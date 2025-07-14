<!DOCTYPE html>
<html>
<head>
    <title>Zoho Inventory - Items</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.common-css')
    <style>
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .item-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .item-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .item-details {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        .item-id {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .item-price {
            font-weight: bold;
            color: #2e7d32;
            font-size: 16px;
            margin-top: 10px;
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
            gap: 20px;
        }
        .stat-item {
            flex: 1;
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
    </style>
</head>
<body>
    <div class="container">
        @include('partials.nav')

        <h1>📦 Zoho Inventory Items</h1>
        
        <div class="stats" id="stats" style="display: none;">
            <div class="stat-item">
                <div class="stat-number" id="totalItems">0</div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="activeItems">0</div>
                <div class="stat-label">Active Items</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="inactiveItems">0</div>
                <div class="stat-label">Inactive Items</div>
            </div>
        </div>

        <button class="refresh-btn" onclick="loadItems()">🔄 Refresh Items</button>

        <div id="loading" class="loading">
            <p>Loading items from Zoho Inventory...</p>
        </div>

        <div id="error" class="error" style="display: none;"></div>

        <div id="items" class="items-grid"></div>
    </div>

    <script>
        function loadItems() {
            const loadingDiv = document.getElementById('loading');
            const errorDiv = document.getElementById('error');
            const itemsDiv = document.getElementById('items');
            const statsDiv = document.getElementById('stats');

            loadingDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            itemsDiv.innerHTML = '';
            statsDiv.style.display = 'none';

            fetch('/test/zoho/items/data')
                .then(response => response.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    
                    if (data.error) {
                        errorDiv.innerHTML = `<strong>Error:</strong> ${data.message || data.error}`;
                        errorDiv.style.display = 'block';
                        return;
                    }

                    if (data.items && data.items.length > 0) {
                        displayItems(data.items);
                        displayStats(data.items);
                    } else {
                        itemsDiv.innerHTML = '<p>No items found in your Zoho Inventory.</p>';
                    }
                })
                .catch(error => {
                    loadingDiv.style.display = 'none';
                    errorDiv.innerHTML = `<strong>Network Error:</strong> ${error.message}`;
                    errorDiv.style.display = 'block';
                });
        }

        function displayItems(items) {
            const itemsDiv = document.getElementById('items');
            
            items.forEach(item => {
                const itemCard = document.createElement('div');
                itemCard.className = 'item-card';
                
                const status = item.status || 'Unknown';
                const statusColor = status === 'active' ? '#4caf50' : '#f44336';
                
                itemCard.innerHTML = `
                    <div class="item-id">ID: ${item.item_id || 'N/A'}</div>
                    <div class="item-name">${item.name || 'Unnamed Item'}</div>
                    <div class="item-details">
                        <p><strong>SKU:</strong> ${item.sku || 'N/A'}</p>
                        <p><strong>Status:</strong> <span style="color: ${statusColor};">${status}</span></p>
                        <p><strong>Description:</strong> ${item.description || 'No description'}</p>
                        <p><strong>Category:</strong> ${item.category_name || 'Uncategorized'}</p>
                        <p><strong>Unit:</strong> ${item.unit || 'N/A'}</p>
                    </div>
                    <div class="item-price">
                        Price: $${(item.rate || 0).toFixed(2)}
                    </div>
                `;
                
                itemsDiv.appendChild(itemCard);
            });
        }

        function displayStats(items) {
            const statsDiv = document.getElementById('stats');
            const totalItems = items.length;
            const activeItems = items.filter(item => item.status === 'active').length;
            const inactiveItems = totalItems - activeItems;

            document.getElementById('totalItems').textContent = totalItems;
            document.getElementById('activeItems').textContent = activeItems;
            document.getElementById('inactiveItems').textContent = inactiveItems;
            
            statsDiv.style.display = 'flex';
        }

        // Load items when page loads
        document.addEventListener('DOMContentLoaded', loadItems);
    </script>
</body>
</html> 