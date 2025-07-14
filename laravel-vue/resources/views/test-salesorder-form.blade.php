<!DOCTYPE html>
<html>
<head>
    <title>Test Sales Order Creation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #005a87; }
        .result { margin-top: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background-color: #f9f9f9; }
        .error { background-color: #ffebee; border-color: #f44336; }
        .success { background-color: #e8f5e8; border-color: #4caf50; }
        .nav-links { margin-bottom: 20px; }
        .nav-links a { margin-right: 15px; color: #007cba; text-decoration: none; }
        .nav-links a:hover { text-decoration: underline; }
        .nav-links a.active {
            background-color: #007cba;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="nav-links">
        <a href="/test/zoho/items">View Items</a>
        <a href="/test/zoho/customers">View Customers</a>
        <a href="/test/zoho/organizations">View Organizations</a>
        <a href="/test/zoho/salesorder" class="active">Create Sales Order</a>
    </div>

    <h1>Test Sales Order Creation</h1>
    
    <form id="salesOrderForm">
        @csrf
        <div class="form-group">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" value="NEEEW" required>
        </div>
        
        <div class="form-group">
            <label for="customer_email">Customer Email:</label>
            <input type="email" id="customer_email" name="customer_email" value="new@gmail.com" required>
        </div>
        
        <div class="form-group">
            <label for="line_items">Line Items (JSON):</label>
            <textarea id="line_items" name="line_items" rows="8" placeholder='[{"item_id": "783803000000073066", "quantity": 2, "rate": 10.00}]' required>[
  {
    "item_id": "783803000000073066",
    "quantity": 2,
    "rate": 10.00
  }
]</textarea>
            <small>Note: Make sure the item_id exists in your Zoho Inventory. You can check available items <a href="/test/zoho/items" target="_blank">here</a>.</small>
        </div>
        
        <button type="submit">Create Sales Order</button>
    </form>

    <div id="result"></div>

    <script>
        document.getElementById('salesOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>Processing...</p>';
            
            const formData = new FormData(this);
            
            let lineItems;
            try {
                lineItems = JSON.parse(formData.get('line_items'));
            } catch (error) {
                resultDiv.innerHTML = '<div class="result error"><strong>Error:</strong> Invalid JSON format in Line Items</div>';
                return;
            }
            
            const data = {
                customer_name: formData.get('customer_name'),
                customer_email: formData.get('customer_email'),
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
                    // Clear form on success
                    document.getElementById('customer_name').value = '';
                    document.getElementById('customer_email').value = '';
                    document.getElementById('line_items').value = '';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="result error"><strong>Network Error:</strong> ' + error.message + '</div>';
            });
        });
    </script>
</body>
</html>