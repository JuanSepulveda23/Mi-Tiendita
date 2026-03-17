{{-- Formulario reutilizable para crear/editar productos --}}
{{-- Variables: $product (opcional, para edición) --}}

{{-- ═══════════ CAMPOS PRINCIPALES (obligatorios) ═══════════ --}}
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Información básica</h3>

    <div class="form-group">
        <label for="name" class="form-label">Nombre del producto *</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-input"
            value="{{ old('name', $product->name ?? '') }}"
            placeholder="Ej: Coca Cola 600ml"
            required
        >
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="form-group">
            <label for="sale_price" class="form-label">Precio de venta *</label>
            <input
                type="number"
                name="sale_price"
                id="sale_price"
                class="form-input"
                value="{{ old('sale_price', $product->sale_price ?? '') }}"
                step="1"
                min="0"
                placeholder="0"
                required
            >
            @error('sale_price')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="purchase_price" class="form-label">Precio de compra *</label>
            <input
                type="number"
                name="purchase_price"
                id="purchase_price"
                class="form-input"
                value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
                step="0.01"
                min="0"
                placeholder="0"
                required
            >
            @error('purchase_price')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="stock" class="form-label">Stock *</label>
            <input
                type="number"
                name="stock"
                id="stock"
                class="form-input"
                value="{{ old('stock', $product->stock ?? 0) }}"
                min="0"
                required
            >
            @error('stock')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════ CAMPOS OPCIONALES ═══════════ --}}
<div x-data="{ showOptional: {{ isset($product) ? 'true' : 'false' }} }">
    <button
        type="button"
        @click="showOptional = !showOptional"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium mb-4 flex items-center gap-1"
    >
        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': showOptional }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Campos opcionales
    </button>

    <div x-show="showOptional" x-transition class="space-y-4 border-t border-gray-200 pt-4">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label for="barcode" class="form-label">Código de barras</label>
                <input
                    type="text"
                    name="barcode"
                    id="barcode"
                    class="form-input"
                    value="{{ old('barcode', $product->barcode ?? '') }}"
                    placeholder="Opcional"
                >
                @error('barcode')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="min_stock" class="form-label">Stock mínimo (alerta)</label>
                <input
                    type="number"
                    name="min_stock"
                    id="min_stock"
                    class="form-input"
                    value="{{ old('min_stock', $product->min_stock ?? 5) }}"
                    min="0"
                    placeholder="5"
                >
                @error('min_stock')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label for="category" class="form-label">Categoría</label>
                <input
                    type="text"
                    name="category"
                    id="category"
                    class="form-input"
                    value="{{ old('category', $product->category ?? '') }}"
                    placeholder="Ej: Bebidas, Abarrotes..."
                >
                @error('category')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="supplier" class="form-label">Proveedor</label>
                <input
                    type="text"
                    name="supplier"
                    id="supplier"
                    class="form-input"
                    value="{{ old('supplier', $product->supplier ?? '') }}"
                    placeholder="Ej: Coca Cola, Nestlé..."
                >
                @error('supplier')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="hidden"
                    name="is_frequent"
                    value="0"
                >
                <input
                    type="checkbox"
                    name="is_frequent"
                    value="1"
                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                    {{ old('is_frequent', $product->is_frequent ?? false) ? 'checked' : '' }}
                >
                <span class="text-sm font-medium text-gray-700">⭐ Producto frecuente (aparece en Modo Caja)</span>
            </label>
        </div>
    </div>
</div>
