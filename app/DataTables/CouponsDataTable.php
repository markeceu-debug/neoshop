<?php

namespace App\DataTables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CouponsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', fn ($c) => '<input type="checkbox" class="row-check" value="' . $c->id . '">')
            ->addColumn('code_render', function ($c) {
                return '<span class="admin-badge admin-badge-purple" style="font-family:monospace; font-size:13px;">'
                    . e($c->code) . '</span>';
            })
            ->addColumn('value_render', function ($c) {
                return match ($c->type) {
                    'percent'       => '<strong style="color:var(--accent-2)">' . rtrim(rtrim($c->value, '0'), '.') . '%</strong>',
                    'fixed'         => '<strong style="color:var(--success)">R$ ' . number_format($c->value, 2, ',', '.') . '</strong>',
                    'free_shipping' => '<span class="admin-badge admin-badge-info">Frete Grátis</span>',
                    default         => '—',
                };
            })
            ->addColumn('type_label', function ($c) {
                $map = [
                    'percent'       => 'Percentual',
                    'fixed'         => 'Valor Fixo',
                    'free_shipping' => 'Frete Grátis',
                ];
                return $map[$c->type] ?? '—';
            })
            ->addColumn('usage_render', function ($c) {
                $used  = $c->used_count;
                $limit = $c->usage_limit ?: '∞';
                return '<strong>' . $used . '</strong> / ' . $limit;
            })
            ->addColumn('validity_render', function ($c) {
                if (! $c->starts_at && ! $c->expires_at) {
                    return '<span class="admin-badge admin-badge-muted">Sem validade</span>';
                }
                $start = $c->starts_at ? $c->starts_at->format('d/m/Y') : '—';
                $end   = $c->expires_at ? $c->expires_at->format('d/m/Y') : '—';
                return '<span style="font-size:12px;">' . $start . ' → ' . $end . '</span>';
            })
            ->addColumn('status_badge', function ($c) {
                if ($c->isActive()) {
                    return '<span class="admin-badge admin-badge-success"><i class="bi bi-circle-fill"></i> Ativo</span>';
                }
                if (! $c->active) {
                    return '<span class="admin-badge admin-badge-muted"><i class="bi bi-circle-fill"></i> Inativo</span>';
                }
                return '<span class="admin-badge admin-badge-warning"><i class="bi bi-circle-fill"></i> Expirado</span>';
            })
            ->addColumn('actions', function ($c) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.coupons.edit', $c->id) . '" class="admin-row-btn" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . route('admin.coupons.destroy', $c->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Excluir este cupom?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="admin-row-btn danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['check', 'code_render', 'value_render', 'usage_render', 'validity_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    public function query(Coupon $model): QueryBuilder
    {
        return $model->newQuery()->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('couponsTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('check')->title('<input type="checkbox" id="checkAll">')
                ->exportable(false)->printable(false)->width(40)->addClass('text-center'),
            Column::computed('code_render')->title('Código'),
            Column::make('name')->title('Nome'),
            Column::computed('type_label')->title('Tipo'),
            Column::computed('value_render')->title('Desconto'),
            Column::computed('usage_render')->title('Usos')->addClass('text-center'),
            Column::computed('validity_render')->title('Validade'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('actions')->title('Ações')
                ->exportable(false)->printable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Coupons_' . date('YmdHis');
    }
}