<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserOrdersDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('number_render', function ($o) {
                return '<a href="' . route('user.orders.show', $o->id) . '"
                           style="font-family:monospace; font-weight:800; color:var(--admin-accent-2); text-decoration:none;">'
                    . e($o->number) . '</a>';
            })
            ->addColumn('seller_render', function ($o) {
                return $o->seller?->name ?? '—';
            })
            ->addColumn('total_render', function ($o) {
                return '<strong style="color:var(--admin-text);">R$ '
                    . number_format($o->total, 2, ',', '.') . '</strong>';
            })
            ->addColumn('status_badge', function ($o) {
                $map = [
                    'awaiting_payment'     => ['admin-badge-warning', 'hourglass-split',   'Aguardando Pagto'],
                    'payment_under_review' => ['admin-badge-info',    'hourglass-split',   'Em Análise'],
                    'payment_rejected'     => ['admin-badge-danger',  'x-circle-fill',     'Rejeitado'],
                    'paid'                 => ['admin-badge-success', 'check-circle-fill', 'Pago'],
                    'shipped'              => ['admin-badge-info',    'truck',             'Enviado'],
                    'delivered'            => ['admin-badge-success', 'box-seam',          'Entregue'],
                    'completed'            => ['admin-badge-success', 'trophy-fill',       'Concluído'],
                    'canceled'             => ['admin-badge-muted',   'x-circle',          'Cancelado'],
                    'refunded'             => ['admin-badge-warning', 'arrow-counterclockwise', 'Reembolsado'],
                ];
                [$cls, $icon, $label] = $map[$o->status] ?? ['admin-badge-muted', 'info-circle', $o->status];
                return '<span class="admin-badge ' . $cls . '"><i class="bi bi-' . $icon . '"></i> ' . $label . '</span>';
            })
            ->addColumn('date_render', fn ($o) => $o->created_at->format('d/m/Y H:i'))
            ->addColumn('actions', function ($o) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('user.orders.show', $o->id) . '" class="admin-row-btn" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['number_render', 'total_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('user_id', Auth::id())
            ->with(['seller'])
            ->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('userOrdersTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('number_render')->title('Pedido'),
            Column::computed('seller_render')->title('Vendedor'),
            Column::computed('total_render')->title('Total'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('date_render')->title('Data'),
            Column::computed('actions')->title('')
                ->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'UserOrders_' . date('YmdHis');
    }
}