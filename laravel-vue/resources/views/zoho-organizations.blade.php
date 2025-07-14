<!DOCTYPE html>
<html>
<head>
    <title>Zoho Inventory - Organizations</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.common-css')
    <style>
        .organizations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .organization-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .organization-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .organization-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        .organization-details {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        .organization-id {
            background: #e3f2fd;
            color: #1976d2;
            padding: 6px 12px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 15px;
            display: inline-block;
        }
        .organization-status {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 15px;
            display: inline-block;
            text-transform: capitalize;
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
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1976d2;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #1565c0;
        }
        .info-box p {
            margin-bottom: 8px;
        }
        .current-org {
            background: #fff3e0;
            border: 1px solid #ff9800;
            color: #e65100;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
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

        <h1>🏢 Zoho Inventory Organizations</h1>
        
        <div class="info-box">
            <h3>ℹ️ About Organizations</h3>
            <p>This page shows all organizations available in your Zoho account. The organization ID is used to identify which organization's data you're accessing.</p>
            <p><strong>Current Organization ID:</strong> <span id="currentOrgId">Loading...</span></p>
        </div>

        <div class="stats" id="stats" style="display: none;">
            <div class="stat-item">
                <div class="stat-number" id="totalOrganizations">0</div>
                <div class="stat-label">Total Organizations</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="activeOrganizations">0</div>
                <div class="stat-label">Active Organizations</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="inactiveOrganizations">0</div>
                <div class="stat-label">Inactive Organizations</div>
            </div>
        </div>

        <button class="refresh-btn" onclick="loadOrganizations()">🔄 Refresh Organizations</button>

        <div id="loading" class="loading">
            <p>Loading organizations from Zoho Inventory...</p>
        </div>

        <div id="error" class="error" style="display: none;"></div>

        <div id="organizations" class="organizations-grid"></div>
    </div>

    <script>
        function loadOrganizations() {
            const loadingDiv = document.getElementById('loading');
            const errorDiv = document.getElementById('error');
            const organizationsDiv = document.getElementById('organizations');
            const statsDiv = document.getElementById('stats');

            loadingDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            organizationsDiv.innerHTML = '';
            statsDiv.style.display = 'none';

            fetch('/test/zoho/organizations/data')
                .then(response => response.json())
                .then(data => {
                    loadingDiv.style.display = 'none';
                    
                    if (data.error) {
                        errorDiv.innerHTML = `<strong>Error:</strong> ${data.message || data.error}`;
                        errorDiv.style.display = 'block';
                        return;
                    }

                    if (data.organizations && data.organizations.length > 0) {
                        displayOrganizations(data.organizations);
                        displayStats(data.organizations);
                    } else {
                        organizationsDiv.innerHTML = '<p>No organizations found in your Zoho account.</p>';
                    }
                })
                .catch(error => {
                    loadingDiv.style.display = 'none';
                    errorDiv.innerHTML = `<strong>Network Error:</strong> ${error.message}`;
                    errorDiv.style.display = 'block';
                });
        }

        function displayOrganizations(organizations) {
            const organizationsDiv = document.getElementById('organizations');
            
            // Get current organization ID from config or environment
            const currentOrgId = '{{ config("services.zoho.organization_id") }}';
            document.getElementById('currentOrgId').textContent = currentOrgId || 'Not configured';
            
            organizations.forEach(organization => {
                const organizationCard = document.createElement('div');
                organizationCard.className = 'organization-card';
                
                const isCurrentOrg = organization.organization_id === currentOrgId;
                const status = organization.status || 'Unknown';
                const statusColor = status === 'active' ? '#4caf50' : '#f44336';
                
                let currentOrgBadge = '';
                if (isCurrentOrg) {
                    currentOrgBadge = '<div class="current-org">🎯 Current Organization</div>';
                }
                
                organizationCard.innerHTML = `
                    ${currentOrgBadge}
                    <div class="organization-id">ID: ${organization.organization_id || 'N/A'}</div>
                    <div class="organization-status">${status}</div>
                    <div class="organization-name">${organization.name || 'Unnamed Organization'}</div>
                    <div class="organization-details">
                        <p><strong>Email:</strong> <a href="mailto:${organization.email || ''}" class="organization-email">${organization.email || 'N/A'}</a></p>
                        <p><strong>Phone:</strong> ${organization.phone || 'N/A'}</p>
                        <p><strong>Website:</strong> ${organization.website ? `<a href="${organization.website}" target="_blank">${organization.website}</a>` : 'N/A'}</p>
                        <p><strong>Industry:</strong> ${organization.industry || 'N/A'}</p>
                        <p><strong>Industry Type:</strong> ${organization.industry_type || 'N/A'}</p>
                        <p><strong>Address:</strong> ${organization.address || 'N/A'}</p>
                        <p><strong>City:</strong> ${organization.city || 'N/A'}</p>
                        <p><strong>State:</strong> ${organization.state || 'N/A'}</p>
                        <p><strong>Country:</strong> ${organization.country || 'N/A'}</p>
                        <p><strong>Zip Code:</strong> ${organization.zip_code || 'N/A'}</p>
                        <p><strong>Currency Code:</strong> ${organization.currency_code || 'N/A'}</p>
                        <p><strong>Time Zone:</strong> ${organization.time_zone || 'N/A'}</p>
                        <p><strong>Date Format:</strong> ${organization.date_format || 'N/A'}</p>
                        <p><strong>Language Code:</strong> ${organization.language_code || 'N/A'}</p>
                    </div>
                `;
                
                organizationsDiv.appendChild(organizationCard);
            });
        }

        function displayStats(organizations) {
            const statsDiv = document.getElementById('stats');
            const totalOrganizations = organizations.length;
            const activeOrganizations = organizations.filter(org => org.status === 'active').length;
            const inactiveOrganizations = totalOrganizations - activeOrganizations;

            document.getElementById('totalOrganizations').textContent = totalOrganizations;
            document.getElementById('activeOrganizations').textContent = activeOrganizations;
            document.getElementById('inactiveOrganizations').textContent = inactiveOrganizations;
            
            statsDiv.style.display = 'flex';
        }

        // Load organizations when page loads
        document.addEventListener('DOMContentLoaded', loadOrganizations);
    </script>
</body>
</html> 