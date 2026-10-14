<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductsDataTable extends DataTable
{
public function dataTable(QueryBuilder $query): EloquentDataTable
{
return (new EloquentDataTable($query))
->addColumn('check', function ($product) {
return '<input type="checkbox" class="row-check" value="' . $product->id . '">';
})
->addColumn('expand', function ($product) {
return '<button type="button" class="admin-expand-btn" data-id="' . $product->id . '" title="Ver variantes">
<i class="bi bi-chevron-right"></i>
</button>';
})
->addColumn('product_info', function ($product) {
$img = $product->cover_url;
return '
<div class="admin-product-cell">
  
<div class="admin-product-info">
<div class="admin-product-name">' . e($product->name) . '</div>
<div class="admin-product-sku">' . e($product->sku) . '</div>
</div>
</div>
';
})

->addColumn('category_name', function ($product) {
return $product->categoryRelation->name
?? $product->category
?? '—';
})
->addColumn('price_formatted', function ($product) {
return '<span style="color:var(--admin-text);font-weight:700;">R$ '
. number_format($product->price, 2, ',', '.') . '</span>';
})
->addColumn('stock_formatted', function ($product) {
$color = $product->stock === 0
? 'var(--admin-red)'
: ($product->stock < 10 ? 'var(--admin-orange)' : 'var(--admin-text)');
return '<span style="color:' . $color . ';font-weight:700;">' . $product->stock . '</span>';
})

->addColumn('variants_badge', function ($product) {
$count = $product->variants()->count();
return $count > 0
? '<span class="admin-badge admin-badge-purple">' . $count . ' variante(s)</span>'
: '<span class="admin-badge admin-badge-muted">—</span>';
})

->addColumn('status_badge', function ($product) {
$map = [
'active'   => ['admin-badge-success', 'Ativo'],
'inactive' => ['admin-badge-muted',   'Inativo'],
'out_of_stock' => ['admin-badge-danger',  'Esgotado'],
];
[$cls, $label] = $map[$product->status] ?? ['admin-badge-muted', '—'];
return '<span class="admin-badge ' . $cls . '"><i class="bi bi-circle-fill"></i> ' . $label . '</span>';
})


->addColumn('actions', function ($product) {
return '
<div class="admin-row-actions">
<a href="' . route('admin.products.variants.index', $product->id) . '"
   class="admin-row-btn" title="Variantes & Galeria">
<i class="bi bi-diagram-3"></i>
</a>
<a href="' . route('admin.products.edit', $product->id) . '" class="admin-row-btn" title="Editar">
<i class="bi bi-pencil"></i>
</a>
<form action="' . route('admin.products.destroy', $product->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Excluir este produto?\')">
' . csrf_field() . method_field('DELETE') . '
<button type="submit" class="admin-row-btn danger" title="Excluir">
<i class="bi bi-trash"></i>
</button>
</form>
</div>
';
})
->rawColumns(['check', 'expand', 'product_info', 'price_formatted', 'stock_formatted', 'variants_badge', 'status_badge', 'actions'])
->setRowId('id');
}

  public function query(Product $model): QueryBuilder
{
return $model->newQuery()
->with(['category', 'variants.items', 'images'])
->latest();
}
public function html(): HtmlBuilder
{
return $this->builder()
->setTableId('productsTable')
->columns($this->getColumns())
->minifiedAjax()
->orderBy(0, 'desc')
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

Column::computed('expand')
->title('')
->exportable(false)->printable(false)->width(40)->addClass('text-center'),

Column::computed('product_info')->title('Produto'),
// ✅ DEPOIS
Column::make('category.name')->title('Categoria'),Column::computed('price_formatted')->title('Preço'),
Column::computed('stock_formatted')->title('Estoque'),
Column::computed('variants_badge')->title('Variantes'),
Column::computed('status_badge')->title('Status'),

Column::computed('actions')
->title('Ações')
->exportable(false)->printable(false)
->width(160)->addClass('text-center'),
];
}

protected function filename(): string
{
return 'Products_' . date('YmdHis');
}
}