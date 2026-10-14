<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrdersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', fn ($o) => '<input type="checkbox" class="row-check" value="' . $o->id . '">')
            ->addColumn('number_render', function ($o) {
                return '<a href="' . route('admin.orders.show', $o->id) . '"
                           style="font-family:monospace; font-weight:800; color:var(--accent-2); text-decoration:none;">'
                    . e($o->number) . '</a>';
            })
            ->addColumn('customer_render', function ($o) {
                $seller = $o->seller?->name ?? '—';
                return '
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <strong style="font-size:13px; color:var(--admin-text);">' . e($o->customer_name) . '</strong>
                        <span style="font-size:11px; color:var(--admin-text-mut);">Vendedor: ' . e($seller) . '</span>
                    </div>
                ';
            })
            ->addColumn('total_render', function ($o) {
                return '<strong style="color:var(--admin-text); font-variant-numeric:tabular-nums;">R$ '
                    . number_format($o->total, 2, ',', '.') . '</strong>';
            })
            ->addColumn('commission_render', function ($o) {
                return '<span style="font-size:12px; color:var(--admin-green);">R$ '
                    . number_format($o->commission_amount, 2, ',', '.')
                    . ' <small style="color:var(--admin-text-mut);">(' . (int) $o->commission_rate . '%)</small></span>';
            })
            ->addColumn('status_badge', function ($o) {
                $map = [
                    'awaiting_payment'     => ['admin-badge-warning', 'hourglass-split',  'Aguardando Pagto'],
                    'payment_under_review' => ['admin-badge-info',    'hourglass-split',  'Em Análise'],
                    'payment_rejected'     => ['admin-badge-danger',  'x-circle-fill',    'Rejeitado'],
                    'paid'                 => ['admin-badge-success', 'check-circle-fill','Pago'],
                    'shipped'              => ['admin-badge-info',    'truck',            'Enviado'],
                    'delivered'            => ['admin-badge-success', 'box-seam',         'Entregue'],
                    'completed'            => ['admin-badge-success', 'trophy-fill',      'Concluído'],
                    'canceled'             => ['admin-badge-muted',   'x-circle',         'Cancelado'],
                    'refunded'             => ['admin-badge-warning', 'arrow-counterclockwise', 'Reembolsado'],
                ];
                [$cls, $icon, $label] = $map[$o->status] ?? ['admin-badge-muted', 'info-circle', $o->status];
                return '<span class="admin-badge ' . $cls . '"><i class="bi bi-' . $icon . '"></i> ' . $label . '</span>';
            })
            ->addColumn('date_render', fn ($o) => $o->created_at->format('d/m/Y H:i'))
            ->addColumn('actions', function ($o) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.orders.show', $o->id) . '" class="admin-row-btn" title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['check', 'number_render', 'customer_render', 'total_render', 'commission_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }
public function query(Order $model): QueryBuilder
{
    $query = $model->newQuery()->with(['seller', 'user'])->latest();

    // Aplica filtro de status se vier na URL
    if (request()->filled('status')) {
        $query->where('status', request('status'));
    }

    return $query;
}

public function html(): HtmlBuilder
{
    // Pega o status atual para repassar no AJAX
    $status = request('status');

    return $this->builder()
        ->setTableId('ordersTable')
        ->columns($this->getColumns())
        ->minifiedAjax(
            url: '',
            script: $status
                ? "data.status = " . json_encode($status) . ";"
                : ''
        )
        ->orderBy(0, 'desc')
        ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
}

    public function getColumns(): array
    {
        return [
            Column::computed('check')->title('<input type="checkbox" id="checkAll">')
                ->exportable(false)->printable(false)->width(40)->addClass('text-center'),
            Column::computed('number_render')->title('Pedido'),
            Column::computed('customer_render')->title('Cliente'),
            Column::computed('total_render')->title('Total'),
            Column::computed('commission_render')->title('Comissão'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('date_render')->title('Data'),
            Column::computed('actions')->title('Ações')
                ->exportable(false)->printable(false)->width(80)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Orders_' . date('YmdHis');
    }
}