<?php

namespace App\DataTables;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PromotionsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', fn ($p) => '<input type="checkbox" class="row-check" value="' . $p->id . '">')
            ->addColumn('type_label', function ($p) {
                $map = [
                    'percent'      => 'Percentual',
                    'fixed'        => 'Valor Fixo',
                    'buy_x_get_y'  => 'Compre X Leve Y',
                ];
                return $map[$p->type] ?? '—';
            })
            ->addColumn('value_render', function ($p) {
                return match ($p->type) {
                    'percent'     => '<strong style="color:var(--accent-2)">' . rtrim(rtrim($p->value, '0'), '.') . '%</strong>',
                    'fixed'       => '<strong style="color:var(--success)">R$ ' . number_format($p->value, 2, ',', '.') . '</strong>',
                    'buy_x_get_y' => '<span class="admin-badge admin-badge-warning">' . ($p->buy_qty ?? '?') . ' + ' . ($p->get_qty ?? '?') . ' grátis</span>',
                    default       => '—',
                };
            })
            ->addColumn('applies_label', function ($p) {
                return match ($p->applies_to) {
                    'all'        => 'Todos os produtos',
                    'products'   => count($p->product_ids ?? []) . ' produto(s)',
                    'categories' => count($p->category_ids ?? []) . ' categoria(s)',
                    default      => '—',
                };
            })
            ->addColumn('validity_render', function ($p) {
                if (! $p->starts_at && ! $p->expires_at) {
                    return '<span class="admin-badge admin-badge-muted">Sem validade</span>';
                }
                $start = $p->starts_at ? $p->starts_at->format('d/m/Y') : '—';
                $end   = $p->expires_at ? $p->expires_at->format('d/m/Y') : '—';
                return '<span style="font-size:12px;">' . $start . ' → ' . $end . '</span>';
            })
            ->addColumn('status_badge', function ($p) {
                if ($p->isActive()) {
                    return '<span class="admin-badge admin-badge-success"><i class="bi bi-circle-fill"></i> Ativa</span>';
                }
                if (! $p->active) {
                    return '<span class="admin-badge admin-badge-muted"><i class="bi bi-circle-fill"></i> Inativa</span>';
                }
                return '<span class="admin-badge admin-badge-warning"><i class="bi bi-circle-fill"></i> Expirada</span>';
            })
            ->addColumn('actions', function ($p) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.promotions.edit', $p->id) . '" class="admin-row-btn" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . route('admin.promotions.destroy', $p->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Excluir esta promoção?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="admin-row-btn danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['check', 'type_label', 'value_render', 'applies_label', 'validity_render', 'status_badge', 'actions'])
            ->setRowId('id');
    }

    public function query(Promotion $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('priority')->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('promotionsTable')
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
            Column::make('name')->title('Nome'),
            Column::computed('type_label')->title('Tipo'),
            Column::computed('value_render')->title('Desconto'),
            Column::computed('applies_label')->title('Aplica-se a'),
            Column::computed('validity_render')->title('Validade'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('actions')->title('Ações')
                ->exportable(false)->printable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Promotions_' . date('YmdHis');
    }
}