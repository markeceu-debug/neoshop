<?php

namespace App\DataTables;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoriesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('check', function ($category) {
                return '<input type="checkbox" class="row-check" value="' . $category->id . '">';
            })
            ->addColumn('icon_render', function ($category) {
                $icon = $category->icon ?: 'bi-tag';
                return '<i class="bi ' . e($icon) . '" style="font-size:20px; color:var(--admin-accent);"></i>';
            })
            ->addColumn('image_render', function ($category) {
                return '<img src="' . $category->image_url . '" alt="' . e($category->name) . '"
                            style="width:44px;height:44px;object-fit:cover;border:1px solid var(--admin-border-strong);">';
            })
            ->addColumn('products_count_badge', function ($category) {
                $count = $category->products()->count();
                return '<span class="admin-badge admin-badge-purple">' . $count . ' produto(s)</span>';
            })
            ->addColumn('active_badge', function ($category) {
                return $category->active
                    ? '<span class="admin-badge admin-badge-success"><i class="bi bi-circle-fill"></i> Ativa</span>'
                    : '<span class="admin-badge admin-badge-muted"><i class="bi bi-circle-fill"></i> Inativa</span>';
            })
            ->addColumn('position_render', function ($category) {
                return '<span style="font-weight:700;color:var(--admin-text);">' . $category->position . '</span>';
            })
            ->addColumn('actions', function ($category) {
                return '
                    <div class="admin-row-actions">
                        <a href="' . route('admin.categories.edit', $category->id) . '" class="admin-row-btn" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="' . route('admin.categories.destroy', $category->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Excluir esta categoria?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="admin-row-btn danger" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['check', 'icon_render', 'image_render', 'products_count_badge', 'active_badge', 'position_render', 'actions'])
            ->setRowId('id');
    }

    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()->orderBy('position')->orderBy('name');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('categoriesTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->language([
                'url' => 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('check')
                ->title('<input type="checkbox" id="checkAll">')
                ->exportable(false)->printable(false)->width(40)->addClass('text-center'),

            Column::computed('image_render')->title('Imagem')->width(70),
            Column::computed('icon_render')->title('Ícone')->width(60)->addClass('text-center'),
            Column::make('name')->title('Nome'),
            Column::make('slug')->title('Slug'),
            Column::computed('products_count_badge')->title('Produtos'),
            Column::computed('position_render')->title('Ordem')->width(80)->addClass('text-center'),
            Column::computed('active_badge')->title('Status'),
            Column::computed('actions')->title('Ações')
                ->exportable(false)->printable(false)->width(120)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Categories_' . date('YmdHis');
    }
}