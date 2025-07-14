<!DOCTYPE html>
<html>
<head>
    <title>Test Sales Order Creation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-links { 
            margin-bottom: 30px; 
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .nav-links a { 
            margin-right: 20px; 
            color: #007cba; 
            text-decoration: none; 
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .nav-links a:hover { 
            background-color: #e3f2fd;
            text-decoration: none;
        }
        .nav-links a.active {
            background-color: #007cba;
            color: white;
        }
        h1 { 
            color: #333; 
            margin-bottom: 30px;
            border-bottom: 2px solid #007cba;
            padding-bottom: 10px;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background-color: #005a87; }
        .result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9; }
        .error { background-color: #ffebee; border-color: #f44336; }
        .success { background-color: #e8f5e8; border-color: #4caf50; }
        .item-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 10px;
        }
        .item-row select, .item-row input[type=number], .item-row input[readonly] {
            margin-bottom: 0;
        }
        .item-row .item-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .item-row button {
            width: 32px;
            height: 32px;
            padding: 0;
            font-size: 20px;
            font-weight: bold;
            border-radius: 50%;
            border: none;
            background: #007cba;
            color: #fff;
            cursor: pointer;
            transition: background 0.2s;
        }
        .item-row button:hover {
            background: #005a87;
        }
        .items-titles{
            display: flex;
            flex-direction: row;
            gap: 10px;
        }
        .item-title-row:first-child{
            flex: 2 1 0%;
        }
        .item-title-row:not(:first-child){
            flex: 1 1 0%;
        }
        .space-eat{
            width: 32px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/test/zoho/items">View Items</a>
            <a href="/test/zoho/customers">View Customers</a>
            <a href="/test/zoho/organizations">View Organizations</a>
            <a href="/test/zoho/salesorder" class="active">Create Sales Order</a>
        </div>
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
                    const cssClass = isSuccess ? 'success' : 'error';
                    resultDiv.innerHTML = '<div class="result ' + cssClass + '"><pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                    if (isSuccess) {
                        document.getElementById('customer_name').value = '';
                        document.getElementById('itemsContainer').innerHTML = '';
                        addItemRow();
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="result error"><strong>Network Error:</strong> ' + error.message + '</div>';
                });
            });
        </script>
    </div>
</body>
</html>