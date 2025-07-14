<!DOCTYPE html>
<html>
<head>
    <title>Test Sales Order Creation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.common-css')
</head>
<body>
    <div class="container">
        @include('partials.nav')
        <h1>📝 Create Sales Order</h1>
        <form id="salesOrderForm">
            @csrf
            <div class="form-group">
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div class="items-titles">
                <div class="item-title-row">Товар</div>
                <div class="item-title-row">Кількість</div>
                <div class="item-title-row">Ціна за одиницю</div>
                <div class="space-eat"></div>
            </div>

            <div id="itemsContainer"></div>
            <button type="submit">Create Sales Order</button>
        </form>

        <div id="result"></div>

        <script>
            let itemsList = [];
            // Loading products into the dropdown
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

            function createItemRow(selectedId = '', quantity = 1) {
                const row = document.createElement('div');
                row.className = 'item-row';

                // Item
                const select = document.createElement('select');
                select.required = true;
                select.style.flex = '2';
                select.innerHTML = '<option value="">Оберіть товар...</option>' +
                    itemsList.map(item => `<option value="${item.item_id}" data-rate="${item.rate}">${item.name}${item.sku ? ' ('+item.sku+')' : ''}</option>`).join('');
                select.value = selectedId;

                // QTY
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

                // Set rate when selecting a product
                select.addEventListener('change', function() {
                    const selected = itemsList.find(i => i.item_id === this.value);
                    rate.value = selected ? selected.rate : '';
                });
                // If already selected, substitute rate
                if (selectedId) {
                    const selected = itemsList.find(i => i.item_id === selectedId);
                    rate.value = selected ? selected.rate : '';
                }

                // Buttons + and −
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

                // Add to the line
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

            // Initialization
            document.addEventListener('DOMContentLoaded', function() {
                loadItemsForDropdown(() => {
                    addItemRow();
                });
            });

            // Submit form
            document.getElementById('salesOrderForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = '<p>Processing...</p>';
                const customerName = document.getElementById('customer_name').value;
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
                if (!valid || lineItems.length === 0) {
                    resultDiv.innerHTML = '<div class="result error"><strong>Помилка:</strong> Заповніть всі поля та додайте хоча б один товар!</div>';
                    return;
                }
                const data = {
                    customer_name: customerName,
                    line_items: lineItems
                };
                fetch('/test/zoho/salesorder', {
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
                        resultDiv.innerHTML = '<div class="result success">Замовлення успішно створено!</div>';
                        document.getElementById('customer_name').value = '';
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
    </div>
</body>
</html>