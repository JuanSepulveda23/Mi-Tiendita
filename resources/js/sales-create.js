/**
 * Sales Create - Lógica para agregar productos dinámicamente al formulario de ventas
 *
 * Responsabilidad única: manejar la interfaz de creación de ventas.
 * Depende de window.availableProducts (inyectado desde Blade).
 */

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('products-container');
    const addBtn = document.getElementById('add-product-btn');
    const totalDisplay = document.getElementById('sale-total');
    const products = window.availableProducts || [];

    let rowIndex = 0;

    /**
     * Genera el HTML de las opciones del select de productos.
     */
    function buildProductOptions() {
        let options = '<option value="">Seleccionar producto...</option>';
        products.forEach(function (product) {
            options += `<option value="${product.id}" data-price="${product.sale_price}" data-stock="${product.stock}">
                ${product.name} — $${parseFloat(product.sale_price).toFixed(2)} (Stock: ${product.stock})
            </option>`;
        });
        return options;
    }

    /**
     * Crea una nueva fila de producto en el formulario.
     */
    function addProductRow() {
        const row = document.createElement('div');
        row.className = 'product-row grid grid-cols-12 gap-3 items-end mb-3 p-3 bg-gray-50 rounded-lg';
        row.dataset.index = rowIndex;

        row.innerHTML = `
            <div class="col-span-6">
                <label class="form-label">Producto</label>
                <select name="products[${rowIndex}][id]" class="form-input product-select" required>
                    ${buildProductOptions()}
                </select>
            </div>
            <div class="col-span-2">
                <label class="form-label">Cantidad</label>
                <input type="number" name="products[${rowIndex}][quantity]" class="form-input quantity-input" value="1" min="1" required>
            </div>
            <div class="col-span-3">
                <label class="form-label">Subtotal</label>
                <input type="text" class="form-input subtotal-display bg-gray-100" value="$0.00" readonly>
            </div>
            <div class="col-span-1 flex justify-center">
                <button type="button" class="remove-row-btn btn btn-danger btn-sm" title="Eliminar">✕</button>
            </div>
        `;

        container.appendChild(row);
        rowIndex++;

        bindRowEvents(row);
    }

    /**
     * Vincula eventos a una fila de producto.
     */
    function bindRowEvents(row) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        select.addEventListener('change', function () {
            updateRowSubtotal(row);
            updateMaxQuantity(row);
        });

        quantityInput.addEventListener('input', function () {
            updateRowSubtotal(row);
        });

        removeBtn.addEventListener('click', function () {
            row.remove();
            updateTotal();
        });
    }

    /**
     * Actualiza la cantidad máxima según el stock disponible.
     */
    function updateMaxQuantity(row) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.value) {
            const stock = parseInt(selectedOption.dataset.stock) || 0;
            quantityInput.max = stock;
            if (parseInt(quantityInput.value) > stock) {
                quantityInput.value = stock;
            }
        }
    }

    /**
     * Actualiza el subtotal de una fila.
     */
    function updateRowSubtotal(row) {
        const select = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        const selectedOption = select.options[select.selectedIndex];

        let subtotal = 0;
        if (selectedOption && selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price) || 0;
            const quantity = parseInt(quantityInput.value) || 0;
            subtotal = price * quantity;
        }

        subtotalDisplay.value = '$' + subtotal.toFixed(2);
        updateTotal();
    }

    /**
     * Recalcula el total general de la venta.
     */
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.product-row').forEach(function (row) {
            const subtotalText = row.querySelector('.subtotal-display').value;
            total += parseFloat(subtotalText.replace('$', '')) || 0;
        });
        totalDisplay.textContent = total.toFixed(2);
    }

    // Evento: agregar fila
    addBtn.addEventListener('click', addProductRow);

    // Agregar primera fila automáticamente
    addProductRow();
});
