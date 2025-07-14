<!DOCTYPE html>
<html>
<head>
    <title>Zoho Inventory - Customers</title>
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
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .error {
            background-color: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .customers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .customer-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .customer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .customer-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .customer-details {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        .customer-id {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .customer-type {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
            text-transform: capitalize;
        }
        .customer-email {
            color: #1976d2;
            text-decoration: none;
        }
        .customer-email:hover {
            text-decoration: underline;
        }
        .refresh-btn {
            background-color: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .refresh-btn:hover {
            background-color: #005a87;
        }
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .search-box {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .search-box:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0,124,186,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="/test/zoho/items">View Items</a>
            <a href="/test/zoho/customers" class="active">View Customers</a>
            <a href="/test/zoho/organizations">View Organizations</a>
            <a href="/test/zoho/salesorder">Create Sales Order</a>
        </div>

        <h1>👥 Zoho Inventory Customers</h1>
        
        <div class="stats" id="stats" style="display: none;">
            <div class="stat-item">
                <div class="stat-number" id="totalCustomers">0</div>
                <div class="stat-label">Total Customers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="activeCustomers">0</div>
                <div class="stat-label">Active Customers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="inactiveCustomers">0</div>
                <div class="stat-label">Inactive Customers</div>
            </div>
        </div>

        <input type="text" class="search-box" id="searchBox" placeholder="🔍 Search customers by name or email..." onkeyup="filterCustomers()">

        <button class="refresh-btn" onclick="loadCustomers()">🔄 Refresh Customers</button>

        <div id="loading" class="loading">
            <p>Loading customers from Zoho Inventory...</p>
        </div>

        <div id="error" class="error" style="display: none;"></div>

        <div id="customers" class="customers-grid"></div>
    </div>

    <script>
        let allCustomers = [];

        function loadCustomers() {
            const loadingDiv = document.getElementById('loading');
            const errorDiv = document.getElementById('error');
            const customersDiv = document.getElementById('customers');
            const statsDiv = document.getElementById('stats');

            loadingDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            customersDiv.innerHTML = '';
            statsDiv.style.display = 'none';

            fetch('/test/zoho/customers/data')
                .then(response => response.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    
                    if (data.error) {
                        errorDiv.innerHTML = `<strong>Error:</strong> ${data.message || data.error}`;
                        errorDiv.style.display = 'block';
                        return;
                    }

                    if (data.contacts && data.contacts.length > 0) {
                        allCustomers = data.contacts;
                        displayCustomers(allCustomers);
                        displayStats(allCustomers);
                    } else {
                        customersDiv.innerHTML = '<p>No customers found in your Zoho Inventory.</p>';
                    }
                })
                .catch(error => {
                    loadingDiv.style.display = 'none';
                    errorDiv.innerHTML = `<strong>Network Error:</strong> ${error.message}`;
                    errorDiv.style.display = 'block';
                });
        }

        function displayCustomers(customers) {
            const customersDiv = document.getElementById('customers');
            customersDiv.innerHTML = '';
            
            customers.forEach(customer => {
                const customerCard = document.createElement('div');
                customerCard.className = 'customer-card';
                
                const status = customer.status || 'Unknown';
                const statusColor = status === 'active' ? '#4caf50' : '#f44336';
                
                customerCard.innerHTML = `
                    <div class="customer-id">ID: ${customer.contact_id || 'N/A'}</div>
                    <div class="customer-type">${customer.contact_type || 'Unknown'}</div>
                    <div class="customer-name">${customer.contact_name || 'Unnamed Customer'}</div>
                    <div class="customer-details">
                        <p><strong>Phone:</strong> ${customer.phone || 'N/A'}</p>
                        <p><strong>Status:</strong> <span style="color: ${statusColor};">${status}</span></p>
                        <p><strong>Company:</strong> ${customer.company_name || 'N/A'}</p>
                        <p><strong>Address:</strong> ${customer.billing_address?.address || 'N/A'}</p>
                        <p><strong>City:</strong> ${customer.billing_address?.city || 'N/A'}</p>
                        <p><strong>Country:</strong> ${customer.billing_address?.country || 'N/A'}</p>
                    </div>
                `;
                
                customersDiv.appendChild(customerCard);
            });
        }

        function displayStats(customers) {
            const statsDiv = document.getElementById('stats');
            const totalCustomers = customers.length;
            const activeCustomers = customers.filter(customer => customer.status === 'active').length;
            const inactiveCustomers = totalCustomers - activeCustomers;

            document.getElementById('totalCustomers').textContent = totalCustomers;
            document.getElementById('activeCustomers').textContent = activeCustomers;
            document.getElementById('inactiveCustomers').textContent = inactiveCustomers;
            
            statsDiv.style.display = 'flex';
        }

        function filterCustomers() {
            const searchTerm = document.getElementById('searchBox').value.toLowerCase();
            const filteredCustomers = allCustomers.filter(customer => {
                const name = (customer.contact_name || '').toLowerCase();
                const company = (customer.company_name || '').toLowerCase();
                
                return name.includes(searchTerm) || 
                       company.includes(searchTerm);
            });
            
            displayCustomers(filteredCustomers);
        }

        // Load customers when page loads
        document.addEventListener('DOMContentLoaded', loadCustomers);
    </script>
</body>
</html> 