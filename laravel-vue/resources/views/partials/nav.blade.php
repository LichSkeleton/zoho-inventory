<div class="nav-links">
    <a href="/test/zoho/items" class="{{ request()->is('test/zoho/items') ? 'active' : '' }}">View Items</a>
    <a href="/test/zoho/customers" class="{{ request()->is('test/zoho/customers') ? 'active' : '' }}">View Customers</a>
    <a href="/test/zoho/organizations" class="{{ request()->is('test/zoho/organizations') ? 'active' : '' }}">View Organizations</a>
    <a href="/test/zoho/salesorder" class="{{ request()->is('test/zoho/salesorder') ? 'active' : '' }}">Create Sales Order</a>
    <a href="/test/zoho/purchaseorder" class="{{ request()->is('test/zoho/purchaseorder') ? 'active' : '' }}">Create Purchase Order</a>
</div> 