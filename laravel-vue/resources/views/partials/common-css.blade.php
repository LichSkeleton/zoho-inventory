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