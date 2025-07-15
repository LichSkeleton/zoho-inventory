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
        <div id="insufficientWarning" style="display:none; margin:8px 0; padding:10px 14px; background:#fffbe6; border:1px solid #ffe58f; border-radius:6px;">
            <label style="display:inline-flex;align-items:center;gap:6px;font-size:14px;line-height:1.2;margin-bottom:4px;">
                <input type="checkbox" id="acknowledgeInsufficient" required style="width:16px;height:16px;margin:0;">
                <span>Ознайомлений(а), що деяких товарів у замовленні не вистачає на складі, тому буде потрібно буде зачекати поки ми їх дозамовимо.</span>
            </label>
            <div style="font-size:13px;margin:4px 0 0 0;">А саме товар(и):</div>
            <ul id="insufficientList" style="margin:4px 0 0 20px;"></ul>
        </div>
        <div id="vendorSelectContainer" style="display:none; margin-bottom:12px;">
            <label for="vendorSelect">Постачальник для дозамовлення:</label>
            <select id="vendorSelect" required></select>
        </div>

        <script>
            let itemsList = [];
            let insufficientItemsCache = null;
            let vendorList = [];
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

            function loadVendors(callback) {
                fetch('/test/zoho/vendors/data')
                    .then(r => r.json())
                    .then(data => {
                        if (data.contacts) {
                            vendorList = data.contacts;
                            const select = document.getElementById('vendorSelect');
                            select.innerHTML = '<option value="">Оберіть постачальника...</option>' +
                                vendorList.map(v => `<option value="${v.contact_id}">${v.contact_name}</option>`).join('');
                            if (callback) callback();
                        }
                    });
            }

            function createItemRow(selectedId = '', quantity = 1) {
                const row = document.createElement('div');
                row.className = 'item-row';
                row.style.marginBottom = '32px'; // more space for stock-info
                row.style.position = 'relative'; // for absolute positioning of stock-info

                // Item select
                const select = document.createElement('select');
                select.required = true;
                select.style.flex = '2';
                select.innerHTML = '<option value="">Оберіть товар...</option>' +
                    itemsList.filter(item => item.status === 'active').map(item => `<option value="${item.item_id}" data-rate="${item.rate}">${item.name}${item.sku ? ' ('+item.sku+')' : ''}</option>`).join('');
                select.value = selectedId;

                // Stock info
                const stockInfo = document.createElement('div');
                stockInfo.className = 'stock-info';
                stockInfo.style.fontSize = '13px';
                stockInfo.style.color = '#888';
                stockInfo.style.position = 'absolute';
                stockInfo.style.left = '0';
                stockInfo.style.top = '100%';
                stockInfo.style.width = '100%';
                stockInfo.style.marginTop = '2px';
                stockInfo.style.minHeight = '18px';
                stockInfo.style.pointerEvents = 'none';

                function updateStockInfo(itemId) {
                    const selected = itemsList.find(i => i.item_id === itemId);
                    if (!selected) {
                        stockInfo.textContent = '';
                        return;
                    }
                    if (typeof selected.actual_available_stock === 'number' && selected.actual_available_stock >= 0) {
                        stockInfo.textContent = `Даного товару: ${selected.actual_available_stock} ${selected.unit || ''}`;
                    } else {
                        stockInfo.textContent = '';
                    }
                }

                select.addEventListener('change', function() {
                    const selected = itemsList.find(i => i.item_id === this.value);
                    rate.value = selected ? selected.rate : '';
                    updateStockInfo(this.value);
                });
                if (selectedId) {
                    const selected = itemsList.find(i => i.item_id === selectedId);
                    rate.value = selected ? selected.rate : '';
                    updateStockInfo(selectedId);
                } else {
                    updateStockInfo('');
                }

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

                // Buttons + and −
                const actions = document.createElement('div');
                actions.className = 'item-actions';
                const plus = document.createElement('button');
                plus.type = 'button';
                plus.textContent = '+';
                plus.title = 'Add row';
                plus.onclick = function() { addItemRow(); };
                const minus = document.createElement('button');
                minus.type = 'button';
                minus.textContent = '−';
                minus.title = 'Remove row';
                minus.onclick = function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                    }
                };
                actions.appendChild(plus);
                actions.appendChild(minus);

                // Add elements to the row
                row.appendChild(select);
                row.appendChild(qty);
                row.appendChild(rate);
                row.appendChild(actions);
                row.appendChild(stockInfo); // stock-info is absolute under select
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
                loadVendors(); // Load vendors on page load
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
                // If the shortage warning is already shown, check the checkbox
                const warningDiv = document.getElementById('insufficientWarning');
                if (warningDiv.style.display !== 'none') {
                    const ack = document.getElementById('acknowledgeInsufficient');
                    if (!ack.checked) {
                        resultDiv.innerHTML = '<div class="result error">Потрібно підтвердити ознайомлення з нестачею товарів!</div>';
                        return;
                    }
                    // Create Purchase Order
                    const vendorId = document.getElementById('vendorSelect').value;
                    if (!vendorId) {
                        resultDiv.innerHTML = '<div class="result error">Оберіть постачальника для дозамовлення!</div>';
                        return;
                    }
                    fetch('/test/zoho/purchaseorder/insufficient', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            insufficient_items: insufficientItemsCache,
                            vendor_id: vendorId
                        })
                    })
                    .then(r => r.json())
                    .then(poData => {
                        if (!poData.error && (!poData.code || poData.code === 0)) {
                            // After a successful PO, we create a Sales Order only for insufficientItemsCache
                            const customerName = document.getElementById('customer_name').value;
                            const lineItems = insufficientItemsCache.map(item => {
                                let rate = item.rate || 0;
                                if (!rate) {
                                    const found = itemsList.find(i => i.item_id === item.item_id);
                                    rate = found && found.rate ? found.rate : 0;
                                }
                                return {
                                    item_id: item.item_id,
                                    quantity: item.ordered,
                                    rate: rate
                                };
                            });
                            fetch('/test/zoho/salesorder/force', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    customer_name: customerName,
                                    line_items: lineItems
                                })
                            })
                            .then(response => response.json())
                            .then(soData => {
                                if (!soData.error && (!soData.code || soData.code === 0)) {
                                    resultDiv.innerHTML = '<div class="result success">Purchase Order та Sales Order успішно створені!</div>';
                                    document.getElementById('customer_name').value = '';
                                    document.getElementById('itemsContainer').innerHTML = '';
                                    addItemRow();
                                    document.getElementById('insufficientWarning').style.display = 'none';
                                    document.getElementById('vendorSelectContainer').style.display = 'none';
                                    insufficientItemsCache = null;
                                } else {
                                    resultDiv.innerHTML = '<div class="result error">Purchase Order створено, але сталася помилка при створенні Sales Order. Зверніться до admin@gmail.com.</div>';
                                }
                            });
                        } else {
                            resultDiv.innerHTML = '<div class="result error">Помилка при створенні Purchase Order. Спробуйте ще раз або зверніться до admin@gmail.com.</div>';
                        }
                    });
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
                    if (data.status === 'insufficient' && data.insufficient_items) {
                        insufficientItemsCache = data.insufficient_items;
                        // Show warning and checkbox
                        const warningDiv = document.getElementById('insufficientWarning');
                        const list = document.getElementById('insufficientList');
                        list.innerHTML = '';
                        data.insufficient_items.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = `${item.name}: потрібно ще ${item.needed} (замовлено: ${item.ordered}, в наявності: ${item.in_stock})`;
                            list.appendChild(li);
                        });
                        warningDiv.style.display = '';
                        document.getElementById('acknowledgeInsufficient').checked = false;
                        // Show supplier selection
                        document.getElementById('vendorSelectContainer').style.display = '';
                        loadVendors();
                        // Prohibit submission without checkbox
                        const submitBtn = document.querySelector('#salesOrderForm button[type=submit]');
                        submitBtn.disabled = true;
                        document.getElementById('acknowledgeInsufficient').onchange = function() {
                            submitBtn.disabled = !this.checked;
                        };
                        resultDiv.innerHTML = '<div class="result error">Деяких товарів не вистачає. Підтвердіть, що ознайомлені з цим, щоб продовжити.</div>';
                        return;
                    }
                    // If everything is ok or the checkbox has already been checked
                    const isSuccess = !data.error && (!data.code || data.code === 0);
                    if (isSuccess) {
                        resultDiv.innerHTML = '<div class="result success">Замовлення успішно створено!</div>';
                        document.getElementById('customer_name').value = '';
                        document.getElementById('itemsContainer').innerHTML = '';
                        addItemRow();
                        document.getElementById('insufficientWarning').style.display = 'none';
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