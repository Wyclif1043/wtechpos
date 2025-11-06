<?php
// config/permissions.php

return [
    'roles' => [
        'admin' => [
            'access_dashboard',
            'access_pos',
            'access_inventory',
            'access_customers',
            'access_reports',
            'access_users',
            'access_settings',
            
            'create_sales',
            'view_sales',
            'edit_sales',
            'delete_sales',
            'void_sales',
            'refund_sales',
            
            'create_products',
            'view_products',
            'edit_products',
            'delete_products',
            
            'create_categories',
            'view_categories',
            'edit_categories',
            'delete_categories',
            
            'create_suppliers',
            'view_suppliers',
            'edit_suppliers',
            'delete_suppliers',
            
            'create_purchase_orders',
            'view_purchase_orders',
            'edit_purchase_orders',
            'delete_purchase_orders',
            'receive_purchase_orders',
            
            'create_stock_adjustments',
            'view_stock_adjustments',
            'edit_stock_adjustments',
            'delete_stock_adjustments',
            
            'create_customers',
            'view_customers',
            'edit_customers',
            'delete_customers',
            
            'manage_credit',
            'process_payments',
            'manage_loyalty',
            
            'view_reports',
            'export_reports',
            
            'create_users',
            'view_users',
            'edit_users',
            'delete_users',
            'manage_permissions',
            
            'change_settings',
            'view_warranties',
            'create_warranties', 
            'edit_warranties',
            'delete_warranties',
            'view_warranty_claims',
            'create_warranty_claims',
            'edit_warranty_claims',
            'delete_warranty_claims',
            'hold_sales',
            'recall_sales',
            'manage_held_sales',
            'view_returns',
            'create_returns',
            'edit_returns', 
            'process_returns',
            'view_shifts',
            'start_shifts',
            'end_shifts',
            'manage_shifts',
            'view_audit_logs',
            'branches.manage',
            'branches.view',
            'branches.create', 
            'branches.edit',
            'branches.delete',
            'inventory.manage',
            'inventory.movements.view',
            'inventory.movements.create',
            'inventory.transfer',
        ],
        
        'manager' => [
            'access_dashboard',
            'access_pos',
            'access_inventory',
            'access_customers',
            'access_reports',
            
            'create_sales',
            'view_sales',
            'edit_sales',
            'void_sales',
            'refund_sales',
            
            'create_products',
            'view_products',
            'edit_products',
            
            'create_categories',
            'view_categories',
            'edit_categories',
            
            'create_suppliers',
            'view_suppliers',
            'edit_suppliers',
            
            'create_purchase_orders',
            'view_purchase_orders',
            'edit_purchase_orders',
            'receive_purchase_orders',
            
            'create_stock_adjustments',
            'view_stock_adjustments',
            'edit_stock_adjustments',
            
            'create_customers',
            'view_customers',
            'edit_customers',
            
            'manage_credit',
            'process_payments',
            'manage_loyalty',
            
            'view_reports',
            'export_reports',

            'branches.manage',
            'branches.view', 
            'branches.create',
            'branches.edit',

            'inventory.manage',
            'inventory.movements.view',
            'inventory.movements.create',
            'inventory.transfer',
        ],
        
        'cashier' => [
            'access_dashboard',
            'access_pos',
            
            'create_sales',
            'view_sales',
            
            'view_products',
            'view_categories',
            
            'create_customers',
            'view_customers',
            'edit_customers',
            
            'process_payments',

            'branches.view',
        ],
        
        'accountant' => [
            'access_dashboard',
            'access_reports',
            'access_customers',
            
            'view_sales',
            'view_products',
            'view_categories',
            'view_suppliers',
            'view_purchase_orders',
            'view_stock_adjustments',
            'view_customers',
            
            'manage_credit',
            'process_payments',
            'manage_loyalty',
            
            'view_reports',
            'export_reports',

            'branches.view',
            'inventory.movements.view',
        ],
    ],
    
    'modules' => [
        'dashboard' => 'Dashboard',
        'pos' => 'Point of Sale',
        'inventory' => 'Inventory',
        'customers' => 'Customers',
        'reports' => 'Reports',
        'users' => 'User Management',
        'settings' => 'Settings',
    ],
    
    'actions' => [
        'access' => 'Access',
        'create' => 'Create',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'manage' => 'Manage',
        'export' => 'Export',
        'void' => 'Void',
        'refund' => 'Refund',
    ],
];