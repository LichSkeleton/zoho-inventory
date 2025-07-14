<!DOCTYPE html>
<html>
<head>
    <title>Create Purchase Order</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.common-css')
</head>
<body>
    <div class="container">
        @include('partials.nav')
        <h1>📝 Create Purchase Order</h1>
        <form id="purchaseOrderForm">
            @csrf
            <div class="form-group">
                <label for="vendor_id">Постачальник:</label>
                <select id="vendor_id" name="vendor_id" required>
                    <option value="">Оберіть постачальника...</option>
                </select>
            </div>
            <div class="items-titles">
                <div class="item-title-row">Товар</div>
                <div class="item-title-row">Кількість</div>
                <div class="item-title-row">Ціна за одиницю</div>
                <div class="space-eat"></div>
            </div>
            <div id="itemsContainer"></div>
            <button type="submit">Create Purchase Order</button>
        </form>
        <div id="result"></div>
    </div>
    <script>
        let itemsList = [];
        let vendorsList = [];
        // Підвантаження товарів у дропдаун
        function loadItemsForDropdown(callback) {
            fetch('/test/zoho/items/data')
                .then(response => response.json())
                .then(data => {
                    if (data.items && data.items.length > 0) {
                        itemsList = data.items;
                        if (callback) callback();
                    }
                });
        }
        // Підвантаження постачальників
        function loadVendorsForDropdown() {
            fetch('/test/zoho/vendors/data')
                .then(response => response.json())
                .then(data => {
                    if (data.contacts && data.contacts.length > 0) {
                        vendorsList = data.contacts;
                        const select = document.getElementById('vendor_id');
                        data.contacts.forEach(vendor => {
                            const option = document.createElement('option');
                            option.value = vendor.contact_id;
                            option.textContent = vendor.contact_name + (vendor.company_name ? ' (' + vendor.company_name + ')' : '');
                            select.appendChild(option);
                        });
                    }
                });
        }
        function createItemRow(selectedId = '', quantity = 1) {
            const row = document.createElement('div');
            row.className = 'item-row';
            // Товар
            const select = document.createElement('select');
            select.required = true;
            select.style.flex = '2';
            select.innerHTML = '<option value="">Оберіть товар...</option>' +
                itemsList.map(item => `<option value="${item.item_id}" data-rate="${item.rate}">${item.name}${item.sku ? ' ('+item.sku+')' : ''}</option>`).join('');
            select.value = selectedId;
            // Кількість
            const qty = document.createElement('input');
            qty.type = 'number';
            qty.min = 1;
            qty.value = quantity;
            qty.required = true;
            qty.style.flex = '1';
            // Rate
            const rate = document.createElement('input');
            rate.type = 'text';
            rate.readOnly = true;
            rate.style.background = '#f5f5f5';
            rate.style.flex = '1';
            rate.value = '';
            // Встановити rate при виборі товару
            select.addEventListener('change', function() {
                const selected = itemsList.find(i => i.item_id === this.value);
                rate.value = selected ? selected.rate : '';
            });
            // Якщо вже вибрано — підставити rate
            if (selectedId) {
                const selected = itemsList.find(i => i.item_id === selectedId);
                rate.value = selected ? selected.rate : '';
            }
            // Кнопки +/−
            const actions = document.createElement('div');
            actions.className = 'item-actions';
            const plus = document.createElement('button');
            plus.type = 'button';
            plus.textContent = '+';
            plus.title = 'Додати рядок';
            plus.onclick = function() { addItemRow(); };
            const minus = document.createElement('button');
            minus.type = 'button';
            minus.textContent = '−';
            minus.title = 'Видалити рядок';
            minus.onclick = function() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                }
            };
            actions.appendChild(plus);
            actions.appendChild(minus);
            // Додаємо в рядок
            row.appendChild(select);
            row.appendChild(qty);
            row.appendChild(rate);
            row.appendChild(actions);
            return row;
        }
        function addItemRow(selectedId = '', quantity = 1) {
            const container = document.getElementById('itemsContainer');
            const row = createItemRow(selectedId, quantity);
            container.appendChild(row);
        }
        // ініціалізація
        document.addEventListener('DOMContentLoaded', function() {
            loadVendorsForDropdown();
            loadItemsForDropdown(() => {
                addItemRow();
            });
        });
        // Submit форми
        document.getElementById('purchaseOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>Processing...</p>';
            const vendorId = document.getElementById('vendor_id').value;
            const itemRows = document.querySelectorAll('.item-row');
            const lineItems = [];
            let valid = true;
            itemRows.forEach(row => {
                const select = row.querySelector('select');
                const qty = row.querySelector('input[type=number]');
                const rate = row.querySelector('input[readonly]');
                if (!select.value || !qty.value || !rate.value) valid = false;
                lineItems.push({
                    item_id: select.value,
                    quantity: parseInt(qty.value, 10),
                    rate: parseFloat(rate.value)
                });
            });
            if (!valid || lineItems.length === 0 || !vendorId) {
                resultDiv.innerHTML = '<div class="result error"><strong>Помилка:</strong> Заповніть всі поля та додайте хоча б один товар!</div>';
                return;
            }
            const data = {
                vendor_id: vendorId,
                line_items: lineItems
            };
            fetch('/test/zoho/purchaseorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                const isSuccess = !data.error && (!data.code || data.code === 0);
                if (isSuccess) {
                    resultDiv.innerHTML = '<div class="result success">Замовлення постачальнику успішно створено!</div>';
                    document.getElementById('vendor_id').selectedIndex = 0;
                    document.getElementById('itemsContainer').innerHTML = '';
                    addItemRow();
                } else {
                    resultDiv.innerHTML = '<div class="result error">Сталася помилка при створенні замовлення. Зверніться до admin@gmail.com.</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="result error">Сталася помилка при створенні замовлення. Зверніться до admin@gmail.com.</div>';
            });
        });
    </script>
</body>
</html> 