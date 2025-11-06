<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ocean': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        'cyan': {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: all 0.3s ease;
            background: linear-gradient(180deg, #0c4a6e 0%, #115e59 100%);
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        .sidebar.collapsed .logo-text {
            display: none;
        }
        .sidebar.collapsed .user-info {
            display: none;
        }
        .main-content {
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .submenu.open {
            max-height: 500px;
        }
        .active-menu {
            background: linear-gradient(90deg, #0ea5e9 0%, #14b8a6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
        }
        .active-submenu {
            background: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
            border-left: 3px solid #0ea5e9;
        }
        .menu-group button:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .nav-item {
            position: relative;
            overflow: hidden;
        }
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        .nav-item:hover::before {
            left: 100%;
        }
        .gradient-header {
            background: linear-gradient(135deg, #0c4a6e 0%, #115e59 50%, #0ea5e9 100%);
        }
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
            border-left: 4px solid #0ea5e9;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.3);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
    @auth
    <!-- Authenticated Layout with Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar text-white w-64 flex flex-col shadow-xl">
            <!-- Logo and Toggle -->
            <div class="p-4 border-b border-cyan-700 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-lg">
                        <i class="fas fa-cash-register text-ocean-600 text-xl"></i>
                    </div>
                    <span class="logo-text ml-3 text-xl font-bold">POS System</span>
                </div>
                <button id="sidebar-toggle" class="p-2 rounded-lg hover:bg-cyan-700 transition-colors">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- User Info -->
            <div class="user-info p-4 border-b border-cyan-700">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-ocean-500 to-cyan-500 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-cyan-200">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                            <i class="fas fa-tachometer-alt w-5 text-cyan-300"></i>
                            <span class="sidebar-text ml-3 font-medium">Dashboard</span>
                        </a>
                    </li>

                    <!-- POS -->
                    <li class="nav-item">
                        <a href="{{ route('pos.interface') }}" 
                           class="flex items-center px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('pos.*') ? 'active-menu' : '' }}">
                            <i class="fas fa-cash-register w-5 text-cyan-300"></i>
                            <span class="sidebar-text ml-3 font-medium">POS</span>
                        </a>
                    </li>

                    <!-- Inventory -->
                   <!-- Inventory Menu Group -->
<li class="nav-item">
    <div class="menu-group">
        <button class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('inventory.*', 'products.*', 'purchase-orders.*', 'stock-adjustments.*', 'suppliers.*', 'branches.*', 'product-movements.*') ? 'active-menu' : '' }}">
            <div class="flex items-center">
                <i class="fas fa-boxes w-5 text-cyan-300"></i>
                <span class="sidebar-text ml-3 font-medium">Inventory</span>
            </div>
            <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
        </button>
        <div class="submenu pl-4 mt-1 space-y-1">
            <a href="{{ route('inventory.dashboard') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('inventory.dashboard') ? 'active-submenu' : '' }}">
                <i class="fas fa-tachometer-alt w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('products.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-box w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Products</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('suppliers.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-truck w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Suppliers</span>
            </a>
            
            @if(auth()->user()->isAdmin())
            <a href="{{ route('branches.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('branches.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-warehouse w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Branches</span>
            </a>
            <a href="{{ route('product-movements.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('product-movements.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-exchange-alt w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Product Movements</span>
            </a>
            @endif
            
            <a href="{{ route('purchase-orders.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('purchase-orders.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-clipboard-list w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Purchase Orders</span>
            </a>
            <a href="{{ route('stock-adjustments.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('stock-adjustments.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-adjust w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Stock Adjustments</span>
            </a>
        </div>
    </div>
</li>

<!-- Warranties Menu Group -->
<li class="nav-item">
    <div class="menu-group">
        <button class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('product-warranties.*', 'warranty-claims.*') ? 'active-menu' : '' }}">
            <div class="flex items-center">
                <i class="fas fa-file-contract w-5 text-cyan-300"></i>
                <span class="sidebar-text ml-3 font-medium">Warranties</span>
            </div>
            <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
        </button>
        <div class="submenu pl-4 mt-1 space-y-1">
            <!-- Product Warranties -->
            <a href="{{ route('product-warranties.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('product-warranties.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-cog w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Product Warranties</span>
            </a>
            
            <!-- Warranty Claims -->
            <a href="{{ route('warranty-claims.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('warranty-claims.index') ? 'active-submenu' : '' }}">
                <i class="fas fa-clipboard-list w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">Warranty Claims</span>
            </a>
            
            <!-- Quick Actions -->
            @can('create_warranties')
            <a href="{{ route('product-warranties.create') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('product-warranties.create') ? 'active-submenu' : '' }}">
                <i class="fas fa-plus-circle w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">New Product Warranty</span>
            </a>
            @endcan
            
            @can('create_warranty_claims')
            <a href="{{ route('warranty-claims.create') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('warranty-claims.create') ? 'active-submenu' : '' }}">
                <i class="fas fa-tools w-4 mr-2 text-cyan-300"></i>
                <span class="sidebar-text">New Claim</span>
            </a>
            @endcan
        </div>
    </div>
</li>

                    <!-- Sales -->
                    <li class="nav-item">
                        <div class="menu-group">
                            <button class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('sales.*') ? 'active-menu' : '' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-chart-line w-5 text-cyan-300"></i>
                                    <span class="sidebar-text ml-3 font-medium">Sales</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
                            </button>
                            <div class="submenu pl-4 mt-1 space-y-1">
                                <a href="{{ route('sales.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('sales.index') ? 'active-submenu' : '' }}">
                                    <i class="fas fa-list w-4 mr-2 text-cyan-300"></i>
                                    <span class="sidebar-text">Sales History</span>
                                </a>
                                <a href="{{ route('sales.daily') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('sales.daily') ? 'active-submenu' : '' }}">
                                    <i class="fas fa-chart-bar w-4 mr-2 text-cyan-300"></i>
                                    <span class="sidebar-text">Sales Reports</span>
                                </a>
                            </div>
                        </div>
                    </li>

                    <!-- Customers -->
                    <li class="nav-item">
                        <a href="{{ route('customers.index') }}" 
                           class="flex items-center px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('customers.*') ? 'active-menu' : '' }}">
                            <i class="fas fa-users w-5 text-cyan-300"></i>
                            <span class="sidebar-text ml-3 font-medium">Customers</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <div class="menu-group">
                            <button class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('reports.*') ? 'active-menu' : '' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-chart-pie w-5 text-cyan-300"></i>
                                    <span class="sidebar-text ml-3 font-medium">Reports</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
                            </button>
                            <div class="submenu pl-4 mt-1 space-y-1">
                                <a href="{{ route('reports.dashboard') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('reports.dashboard') ? 'active-submenu' : '' }}">
                                    <i class="fas fa-chart-line w-4 mr-2 text-cyan-300"></i>
                                    <span class="sidebar-text">Analytics Dashboard</span>
                                </a>
                                
                                <div class="menu-group">
                                    <button class="flex items-center justify-between w-full px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                        <div class="flex items-center">
                                            <i class="fas fa-shopping-cart w-4 mr-2 text-cyan-300"></i>
                                            <span class="sidebar-text">Sales Reports</span>
                                        </div>
                                        <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
                                    </button>
                                    <div class="submenu pl-4 mt-1 space-y-1">
                                        <a href="{{ route('reports.sales.summary') }}" class="flex items-center px-4 py-2 text-xs rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                            Sales Summary
                                        </a>
                                        <a href="{{ route('reports.sales.detailed') }}" class="flex items-center px-4 py-2 text-xs rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                            Detailed Sales
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <!-- Admin -->
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <div class="menu-group">
                            <button class="flex items-center justify-between w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300 {{ request()->routeIs('admin.*', 'users.*', 'categories.*', 'audit.*') ? 'active-menu' : '' }}">
                                <div class="flex items-center">
                                    <i class="fas fa-cog w-5 text-cyan-300"></i>
                                    <span class="sidebar-text ml-3 font-medium">Admin</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs sidebar-text text-cyan-300"></i>
                            </button>
                            <div class="submenu pl-4 mt-1 space-y-1">
                                <!-- <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                    <i class="fas fa-user-cog w-4 mr-2 text-cyan-300"></i>
                                    <span class="sidebar-text">User Management</span>
                                </a> -->
                                <a href="{{ route('categories.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                    <i class="fas fa-tags w-4 mr-2 text-cyan-300"></i>
                                    <span class="sidebar-text">Categories</span>
                                </a>
                                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                    <i class="fas fa-users-cog w-4 mr-2"></i>
                                    <span class="sidebar-text">All Users</span>
                                </a>
                                <a href="{{ route('register') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                    <i class="fas fa-user-plus w-4 mr-2"></i>
                                    <span class="sidebar-text">Register User</span>
                                </a>
                               
                                <a href="{{ route('audit.index') }}" class="flex items-center px-4 py-2 text-sm rounded-lg hover:bg-cyan-700 transition-all duration-300">
                                    <i class="fas fa-clipboard-list w-4 mr-2"></i>
                                    <span class="sidebar-text">Audit Log</span>
                                </a>
                            </div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Logout Button -->
            <div class="p-4 border-t border-cyan-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-3 rounded-xl hover:bg-cyan-700 transition-all duration-300">
                        <i class="fas fa-sign-out-alt w-5 text-cyan-300"></i>
                        <span class="sidebar-text ml-3 font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="gradient-header shadow-lg">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white">@yield('page-title', 'Dashboard')</h1>
                        <!-- Breadcrumb Navigation -->
                        <nav class="flex mt-2" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 text-sm">
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-cyan-200 hover:text-white transition-colors">
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>
                                @yield('breadcrumbs')
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden p-2 rounded-lg hover:bg-cyan-700 transition-colors">
                        <i class="fas fa-bars text-white"></i>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Sample Dashboard Content -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="stat-card rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-ocean-100 text-ocean-600">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                                <p class="text-2xl font-bold text-gray-900">$12,426</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-cyan-100 text-cyan-600">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Customers</p>
                                <p class="text-2xl font-bold text-gray-900">1,248</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-ocean-100 text-ocean-600">
                                <i class="fas fa-boxes text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                                <p class="text-2xl font-bold text-gray-900">24</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stat-card rounded-xl shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-cyan-100 text-cyan-600">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Revenue Growth</p>
                                <p class="text-2xl font-bold text-gray-900">+18.2%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @else
    <!-- Unauthenticated Layout (for login, register, etc.) -->
    <div class="min-h-screen bg-gradient-to-br from-ocean-50 via-cyan-50 to-white">
        @yield('content')
    </div>
    @endauth

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="fixed bottom-4 right-4 bg-gradient-to-r from-cyan-500 to-ocean-500 text-white px-6 py-3 rounded-xl shadow-lg transform transition-transform duration-300 ease-in-out" id="flash-message">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="fixed bottom-4 right-4 bg-gradient-to-r from-red-500 to-orange-500 text-white px-6 py-3 rounded-xl shadow-lg transform transition-transform duration-300 ease-in-out" id="flash-message">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    </div>
    @endif

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide flash messages after 5 seconds
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.transform = 'translateX(100%)';
                    setTimeout(() => flashMessage.remove(), 300);
                }, 5000);
            }

            // Sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                });
            }

            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            
            if (mobileMenuButton && sidebar) {
                mobileMenuButton.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                });
            }

            // Menu group toggle functionality
            const menuGroups = document.querySelectorAll('.menu-group');
            
            menuGroups.forEach(group => {
                const button = group.querySelector('button');
                const submenu = group.querySelector('.submenu');
                
                if (button && submenu) {
                    button.addEventListener('click', () => {
                        // Close all other submenus
                        menuGroups.forEach(otherGroup => {
                            if (otherGroup !== group) {
                                const otherSubmenu = otherGroup.querySelector('.submenu');
                                if (otherSubmenu) {
                                    otherSubmenu.classList.remove('open');
                                }
                            }
                        });
                        
                        // Toggle current submenu
                        submenu.classList.toggle('open');
                    });
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 768 && sidebar && !sidebar.contains(event.target) && 
                    mobileMenuButton && !mobileMenuButton.contains(event.target)) {
                    sidebar.classList.add('collapsed');
                }
            });
        });
    </script>
</body>
</html>