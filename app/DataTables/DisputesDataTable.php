<?php

namespace App\DataTables;

use App\Models\Dispute;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DisputesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('id_render', function ($d) {
                return '<a href="' . route('admin.disputes.show', $d->id) . '"
                           style="font-family:monospace; font-weight:800; color:var(--admin-accent-2); text-decoration:none;">'
                    . '#' . $d->id . '</a>';
            })
            ->addColumn('order_render', function ($d) {
                return '<a href="' . route('admin.orders.show', $d->order_id) . '"
                           style="font-family:monospace; color:var(--admin-accent); text-decoration:none;">'
                    . e($d->order?->number) . '</a>';
            })
            ->addColumn('opener_render', fn ($d) => $d->opener?->name ?? '—')
            ->addColumn('reason_render', function ($d) {
                return '<span style="font-size:12px;">' . e($d->reason_label) . '</span>';
            })
            ->addColumn('amount_render', function ($d) {
                return '<strong style="color:var(--admin-accent-2);">R$ '
                    . number_format($d->order->total ?? 0, 2, ',', '.') . '</strong>';
            })
            ->addColumn('status_badge', function ($d) {
                [$cls, $label] = $d->status_badge;
                return '<span class="admin-badge ' . $cls . '">' . $label . '</span>';
            })
            ->addColumn('date_render', fn ($d) => $d->created_at->format('d/m/Y H:i'))
            ->addColumn('actions', function ($d) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.disputes.show', $d->id) . '" class="admin-row-btn" title="Ver">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['id_render', 'order_render', 'reason_render', 'amount_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    public function query(Dispute $model): QueryBuilder
    {
        return $model->newQuery()->with(['order', 'opener'])->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('disputesTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id_render')->title('ID'),
            Column::computed('order_render')->title('Pedido'),
            Column::computed('opener_render')->title('Cliente'),
            Column::computed('reason_render')->title('Motivo'),
            Column::computed('amount_render')->title('Valor'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('date_render')->title('Data'),
            Column::computed('actions')->title('')
                ->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Disputes_' . date('YmdHis');
    }
}