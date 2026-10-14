<?php

namespace App\DataTables;

use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WithdrawalsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user_render', function ($w) {
                return $w->user?->name ?? '—';
            })
            ->addColumn('amount_render', fn ($w) => '<strong style="color:var(--admin-accent-2);">R$ ' . number_format($w->amount, 2, ',', '.') . '</strong>')
            ->addColumn('method_render', fn ($w) => $w->method === 'bank_transfer' ? 'Transferência' : 'Multicaixa Express')
            ->addColumn('status_badge', function ($w) {
                $map = [
                    'pending'  => ['admin-badge-warning', 'hourglass-split',   'Pendente'],
                    'approved' => ['admin-badge-info',    'check-circle-fill', 'Aprovado'],
                    'rejected' => ['admin-badge-danger',  'x-circle-fill',     'Rejeitado'],
                    'paid'     => ['admin-badge-success', 'trophy-fill',       'Pago'],
                ];
                [$cls, $icon, $label] = $map[$w->status] ?? ['admin-badge-muted', 'info-circle', $w->status];
                return '<span class="admin-badge ' . $cls . '"><i class="bi bi-' . $icon . '"></i> ' . $label . '</span>';
            })
            ->addColumn('date_render', fn ($w) => $w->created_at->format('d/m/Y H:i'))
            ->addColumn('actions', function ($w) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.withdrawals.show', $w->id) . '" class="admin-row-btn" title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['amount_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    public function query(Withdrawal $model): QueryBuilder
    {
        return $model->newQuery()->with(['user'])->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('withdrawalsTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('user_render')->title('Vendedor'),
            Column::computed('amount_render')->title('Valor'),
            Column::computed('method_render')->title('Método'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('date_render')->title('Data'),
            Column::computed('actions')->title('')
                ->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Withdrawals_' . date('YmdHis');
    }
}