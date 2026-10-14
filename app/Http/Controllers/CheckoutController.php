<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use App\Models\PaymentProof;
use App\Services\OrderTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
        private CouponService $coupons,
    ) {}

    /**
     * Mostra a página de checkout.
     */
    public function index()
    {
        $cart = $this->cart->resolveCart();

        if ($cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Revalida preços/estoque
        $issues = $this->cart->revalidate($cart);

        // Opções de entrega (só se o cliente já escolheu município)
        $shippingOptions = [];

        if ($cart->customer_province && $cart->customer_municipality) {
            $result = $this->cart->calculateShipping($cart, $cart->shipping_method ?? 'standard');
            $shippingOptions = $result['options'] ?? [];
        }

        return view('shop.checkout', [
            'cart'            => $cart,
            'items'           => $cart->items()->with(['product', 'variantItem'])->get(),
            'issues'          => $issues,
            'shippingOptions' => $shippingOptions,
            'provinces'       => $this->provinces(),
        ]);
    }

    /**
     * Processa o pedido.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],

            'shipping_address'              => ['required', 'array'],
            'shipping_address.province'     => ['required', 'string', 'max:100'],
            'shipping_address.municipality' => ['required', 'string', 'max:100'],
            'shipping_address.neighborhood' => ['required', 'string', 'max:100'],
            'shipping_address.street'       => ['required', 'string', 'max:200'],
            'shipping_address.number'       => ['required', 'string', 'max:20'],
            'shipping_address.complement'   => ['nullable', 'string', 'max:100'],
            'shipping_address.reference'    => ['nullable', 'string', 'max:200'],

            'shipping_method' => ['required', 'in:standard,express,pickup'],
            'payment_method'  => ['required', 'in:transfer,multicaixa'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $cart = $this->cart->resolveCart();

        try {
            $order = $this->checkout->process($cart, $data);

            return response()->json([
                'status'       => 'success',
                'message'      => 'Pedido criado! Finalize o pagamento.',
                'order_id'     => $order->id,
                'order_number' => $order->number,
                'redirect'     => route('checkout.payment', $order),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->validator->errors()->first(),
            ], 422);
        }
    }
    /**
     * Página de instruções de pagamento + upload de comprovativo.
     */
    public function payment(Order $order): View
    {
        // Autorização básica
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('shop.payment-instructions', [
            'order'    => $order->load(['items', 'seller']),
            'bankInfo' => [
                'holder'             => \App\Models\Setting::get('bank_holder'),
                'bank'               => \App\Models\Setting::get('bank_name'),
                'iban'               => \App\Models\Setting::get('bank_iban'),
                'account'            => \App\Models\Setting::get('bank_account'),
                'multicaixa_express' => \App\Models\Setting::get('multicaixa_express'),
            ],
        ]);
    }


    /**
     * Recebe o comprovativo de pagamento enviado pelo cliente.
     */
    public function uploadProof(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($order->status, ['awaiting_payment', 'payment_rejected'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Este pedido não aceita mais comprovativos.',
            ], 422);
        }

        $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:3'],
            'files.*' => ['file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],  // 5MB
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $order) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('payment-proofs/' . $order->number, 'public');

                PaymentProof::create([
                    'order_id'    => $order->id,
                    'uploaded_by' => auth()->id(),
                    'file_path'   => $path,
                    'file_type'   => $file->getMimeType(),
                    'file_size'   => $file->getSize(),
                    'notes'       => $request->input('notes'),
                    'status'      => 'pending',
                ]);
            }

            $order->update(['status' => 'payment_under_review']);
            app(OrderTrackingService::class)->log(
                $order,
                'payment_under_review',
                location: $order->customer_municipality
            );
        });

        return response()->json([
            'status'   => 'success',
            'message'  => 'Comprovativo enviado! Aguarde a análise.',
            'redirect' => route('checkout.success', $order),
        ]);
    }
    /**
     * Página de sucesso do pedido.
     */
    public function success(Order $order): View
    {
        if ($order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('shop.order-success', [
            'order' => $order->load('items'),
        ]);
    }


    /**
     * Calcula o frete via AJAX (chamado quando o cliente escolhe o município).
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $data = $request->validate([
            'province'     => ['required', 'string'],
            'municipality' => ['required', 'string'],
            'method'       => ['nullable', 'in:standard,express'],
        ]);

        $cart = $this->cart->resolveCart();
        $method = $data['method'] ?? 'standard';

        try {
            $this->cart->setCustomerLocation(
                $cart,
                $data['province'],
                $data['municipality'],
                $method
            );

            $result = $this->cart->calculateShipping($cart, $method);

            return response()->json([
                'status'  => 'success',
                'options' => $result['options'],
                'fee'     => number_format($result['fee'], 2, ',', '.'),
                'total'   => number_format($cart->fresh()->total, 2, ',', '.'),
                'hours'   => $result['hours'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    private function provinces(): array
    {
        return [
            'Bengo' => ['Dande', 'Ambriz', 'Bula Atumba', 'Dembos', 'Nambuangongo', 'Pango Aluquém'],
            'Benguela' => ['Benguela', 'Lobito', 'Catumbela', 'Baía Farta', 'Balombo', 'Bocoio', 'Caimbambo', 'Chongoroi', 'Cubal', 'Ganda'],
            'Bié' => ['Kuito', 'Andulo', 'Camacupa', 'Catabola', 'Chinguar', 'Chitembo', 'Cuemba', 'Cunhinga', 'Nharea'],
            'Cabinda' => ['Cabinda', 'Belize', 'Buco-Zau', 'Cacongo'],
            'Cuando Cubango' => ['Menongue', 'Calai', 'Cuangar', 'Cuchi', 'Cuito Cuanavale', 'Dirico', 'Longa', 'Mavinga', 'Nancova', 'Rivungo'],
            'Cuanza Norte' => ['N\'dalatando', 'Ambaca', 'Banga', 'Bolongongo', 'Cambambe', 'Cazengo', 'Golungo Alto', 'Gonguembo', 'Lucala', 'Quiculungo', 'Samba Caju'],
            'Cuanza Sul' => ['Sumbe', 'Cassongue', 'Cela', 'Conda', 'Ebo', 'Gabela (Amboim)', 'Mussende', 'Porto Amboim', 'Quibala', 'Quilenda', 'Seles'],
            'Cunene' => ['Ondjiva', 'Cahama', 'Cuanhama', 'Curoca', 'Cuvelai', 'Namacunde', 'Ombadja'],
            'Huambo' => ['Huambo', 'Caála', 'Bailundo', 'Ecunha', 'Londuimbali', 'Longonjo', 'Mungo', 'Tchicala-Tcholoanga', 'Tchindjenje', 'Ucuma'],
            'Huíla' => ['Lubango', 'Matala', 'Caconda', 'Cacula', 'Caluquembe', 'Chibia', 'Chicomba', 'Chipindo', 'Cuvango', 'Gambos', 'Humpata', 'Jamba', 'Quilengues', 'Quipungo'],
            'Luanda' => ['Luanda', 'Belas', 'Cacuaco', 'Cazenga', 'Icolo e Bengo', 'Quiçama', 'Talatona', 'Viana'],
            'Lunda Norte' => ['Dundo', 'Cambulo', 'Capenda-Camulemba', 'Caungula', 'Chitato', 'Cuango', 'Cuílo', 'Lóvua', 'Lubalo', 'Xá-Muteba'],
            'Lunda Sul' => ['Saurimo', 'Cacolo', 'Dala', 'Muconda'],
            'Malanje' => ['Malanje', 'Cacuso', 'Calandula', 'Cambundi-Catembo', 'Cangandala', 'Caombo', 'Cuaba Nzogo', 'Cunda-Dia-Baze', 'Luquembo', 'Marimba', 'Massango', 'Mucari', 'Quela', 'Quirima'],
            'Moxico' => ['Luena', 'Camanongue', 'Léua', 'Luau', 'Luchazes', 'Cameia'],
            'Namibe' => ['Moçâmedes', 'Bibala', 'Camucuio', 'Tômbwa', 'Virei'],
            'Uíge' => ['Uíge', 'Alto Cauale', 'Ambuíla', 'Bembe', 'Buengas', 'Bungo', 'Damba', 'Milunga', 'Mucaba', 'Negage', 'Puri', 'Quimbele', 'Quitexe', 'Sanza Pombo', 'Songo', 'Zombo'],
            'Zaire' => ['Mabanza Congo', 'Cuimba', 'Noqui', 'Nzeto', 'Soyo', 'Tomboco'],
        ];
    }
}
