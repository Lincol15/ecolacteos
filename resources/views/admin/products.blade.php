@extends('layouts.app')

@section('page-title', 'Productos')
@section('page-subtitle', 'Catálogo de productos que se muestran en la tienda pública')

@section('top-actions')
    <a href="{{ route('admin.products-create') }}" class="btn btn-primary">
        ➕ Nuevo Producto
    </a>
@endsection

@section('content')
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">🧀 Listado de Productos</div>
    </div>
    <div class="panel-body">
        <form method="GET" class="form-grid" style="margin-bottom:16px;">
            <div class="form-group">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Nombre del producto">
            </div>
            <div class="form-group">
                <label class="form-label">Categoría</label>
                <select name="category" class="form-select">
                    <option value="">-- Todas --</option>
                    @foreach(\App\Models\Product::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" {{ request('category') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:end;">
                <button type="submit" class="btn btn-info">🔍 Filtrar</button>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Catálogo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td style="font-size:22px;">
                            @if($p->image_url)
                                <div style="width:36px;height:36px;border-radius:8px;background-size:cover;background-position:center;background-image:url('{{ $p->image_url }}')"></div>
                            @else
                                {{ $p->emoji ?? '🧀' }}
                            @endif
                        </td>
                        <td><strong>{{ $p->name }}</strong><div style="font-size:11px;color:var(--text-light)">{{ $p->sku }}</div></td>
                        <td>{{ \App\Models\Product::CATEGORIES[$p->category] ?? $p->category }}</td>
                        <td>S/ {{ number_format($p->unit_price, 2) }} <span style="font-size:11px;color:var(--text-light)">/ {{ $p->unit }}</span></td>
                        <td>{{ number_format($p->currentStock(), 0) }}</td>
                        <td>
                            <span class="badge {{ $p->show_in_catalog ? 'badge-green' : 'badge-gray' }}">
                                {{ $p->show_in_catalog ? '✅ Visible' : '🚫 Oculto' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $p->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $p->is_active ? '✅ Activo' : '❌ Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.products-edit', $p) }}" class="btn btn-info btn-sm">✏️ Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty">
                                <div class="empty-icon">🧀</div>
                                <h3>No hay productos registrados</h3>
                                <p>Crea uno nuevo presionando el botón "Nuevo Producto".</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="pagination">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
