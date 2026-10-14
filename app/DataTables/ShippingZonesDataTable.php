<?php

namespace App\DataTables;

use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ShippingZonesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', fn ($z) => '<input type="checkbox" class="row-check" value="' . $z->id . '">')
            ->addColumn('location_render', function ($z) {
                return '
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <strong style="font-size:13px; color:var(--admin-text);">' . e($z->municipality) . '</strong>
                        <span style="font-size:11px; color:var(--admin-text-mut);">' . e($z->province) . '</span>
                    </div>
                ';
            })
            ->addColumn('standard_render', function ($z) {
                return '<strong style="color:var(--admin-text);">R$ ' . number_format($z->standard_fee, 2, ',', '.') . '</strong>'
                    . ' <small style="color:var(--admin-text-mut);">· ' . $z->standard_hours . 'h</small>';
            })
            ->addColumn('express_render', function ($z) {
                if (! $z->express_fee) return '<span style="color:var(--admin-text-mut);">—</span>';
                return '<strong style="color:var(--admin-accent);">R$ ' . number_format($z->express_fee, 2, ',', '.') . '</strong>'
                    . ' <small style="color:var(--admin-text-mut);">· ' . $z->express_hours . 'h</small>';
            })
            ->addColumn('free_render', function ($z) {
                if (! $z->free_above) return '<span style="color:var(--admin-text-mut);">—</span>';
                return '<span class="admin-badge admin-badge-success">Grátis acima de R$ '
                    . number_format($z->free_above, 0, ',', '.') . '</span>';
            })
            ->addColumn('status_render', function ($z) {
                return $z->active
                    ? '<span class="admin-badge admin-badge-success"><i class="bi bi-circle-fill"></i> Ativa</span>'
                    : '<span class="admin-badge admin-badge-muted"><i class="bi bi-circle-fill"></i> Inativa</span>';
            })
            ->addColumn('actions', function ($z) {
                return '
                    <div class="admin-row-actions">
                        <button type="button"
                                class="admin-row-btn"
                                onclick="toggleZone(' . $z->id . ')"
                                title="' . ($z->active ? 'Desativar' : 'Ativar') . '">
                            <i class="bi bi-' . ($z->active ? 'pause-circle' : 'play-circle') . '"></i>
                        </button>
                        <a href="' . route('admin.shipping-zones.edit', $z->id) . '" class="admin-row-btn" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . route('admin.shipping-zones.destroy', $z->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Excluir esta zona?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="admin-row-btn danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['check', 'location_render', 'standard_render', 'express_render', 'free_render', 'status_render', 'actions'])
            ->setRowId('id');
    }

    public function query(ShippingZone $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('province')->orderBy('municipality');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('shippingZonesTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'asc')
            ->language(['url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json']);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('check')->title('<input type="checkbox" id="checkAll">')
                ->exportable(false)->printable(false)->width(40)->addClass('text-center'),
            Column::computed('location_render')->title('Localização'),
            Column::computed('standard_render')->title('Motoboy Padrão'),
            Column::computed('express_render')->title('Expresso'),
            Column::computed('free_render')->title('Frete Grátis'),
            Column::computed('status_render')->title('Status'),
            Column::computed('actions')->title('Ações')
                ->exportable(false)->printable(false)->width(140)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'ShippingZones_' . date('YmdHis');
    }
}