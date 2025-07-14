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
                <input type="text" id="customer_name" name="customer_name" value="NEEEW" required>
            </div>
                
            <div class="form-group">
                <label for="item_select">Товар:</label>
                <select id="item_select" name="item_id" required style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                    <option value="">Оберіть товар...</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Кількість:</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label for="rate">Ціна за одиницю:</label>
                <input type="text" id="rate" name="rate" readonly style="background:#f5f5f5;">
            </div>
            
            <button type="submit">Create Sales Order</button>
        </form>

        <div id="result"></div>

        <script>
            let itemsList = [];
            // Loading products into the dropdown
            function loadItemsForDropdown() {
                fetch('/test/zoho/items/data')
                    .then(response => response.json())
                    .then(data => {
                        if (data.items && data.items.length > 0) {
                            itemsList = data.items;
                            const select = document.getElementById('item_select');
                            data.items.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.item_id;
                                option.textContent = item.name + (item.sku ? ' (' + item.sku + ')' : '');
                                option.setAttribute('data-rate', item.rate);
                                select.appendChild(option);
                            });
                        }
                    });
            }
            // When choosing a product, substitute rate
            document.addEventListener('DOMContentLoaded', function() {
                loadItemsForDropdown();
                document.getElementById('item_select').addEventListener('change', function() {
                    const selected = itemsList.find(i => i.item_id === this.value);
                    document.getElementById('rate').value = selected ? selected.rate : '';
                });
            });
            // Submit form
            document.getElementById('salesOrderForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const resultDiv = document.getElementById('result');
                resultDiv.innerHTML = '<p>Processing...</p>';
                const customerName = document.getElementById('customer_name').value;
                const itemId = document.getElementById('item_select').value;
                const quantity = parseInt(document.getElementById('quantity').value, 10);
                const rate = parseFloat(document.getElementById('rate').value);
                if (!itemId || !quantity || !rate) {
                    resultDiv.innerHTML = '<div class="result error"><strong>Помилка:</strong> Заповніть всі поля!</div>';
                    return;
                }
                const lineItems = [{ item_id: itemId, quantity: quantity, rate: rate }];
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
                        document.getElementById('item_select').selectedIndex = 0;
                        document.getElementById('quantity').value = 1;
                        document.getElementById('rate').value = '';
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