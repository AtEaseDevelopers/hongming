



{{-- @canany(['deliveryorder'])
<li class="nav-item {{ Request::is('deliveryOrders*') ? 'active' : '' }}">
    <a class="nav-link {{ Request::is('deliveryOrders*') ? 'active' : '' }}" href="{{ route('deliveryOrders.index') }}">
        <i class="nav-icon icon-notebook"></i>
        <span>Delivery Orders</span>
    </a>
</li>
@endcanany

@canany(['loan','loanpayment'])
<li class="nav-item nav-dropdown {{ Request::is('loans*','loanpayments*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-diamond"></i>
        <span>Loan Management</span>
    </a>

    @can('loan')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('loans*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('loans*') ? 'active' : '' }}" href="{{ route('loans.index') }}">
                    <span>Loans</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('loanpayment')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('loanpayments*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('loanpayments*') ? 'active' : '' }}" href="{{ route('loanpayments.index') }}">
                    <span>Loan Payments</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany

@canany(['bonus','claim','advance','compound','paymentdetail'])
<li class="nav-item nav-dropdown {{ Request::is('bonuses*','claims*','advances*','compounds*','paymentdetails*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-wallet"></i>
        <span>Finance</span>
    </a>

    @can('paymentdetail')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('paymentdetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('paymentdetails*') ? 'active' : '' }}" href="{{ route('paymentdetails.index') }}">
                    <span>Payment Details</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('compound')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('compounds*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('compounds*') ? 'active' : '' }}" href="{{ route('compounds.index') }}">
                    <span>Compounds</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('advance')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('advances*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('advances*') ? 'active' : '' }}" href="{{ route('advances.index') }}">
                    <span>Advances</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('claim')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('claims*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('claims*') ? 'active' : '' }}" href="{{ route('claims.index') }}">
                    <span>Claims</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('bonus')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('bonuses*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('bonuses*') ? 'active' : '' }}" href="{{ route('bonuses.index') }}">
                    <span>Bonuses</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany --}}

@canany(['invoice'])
<li class="nav-item nav-dropdown {{ Request::is('invoices*','invoiceDetails*','invoicePayments') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-notebook"></i>
        <span>Invoices</span>
    </a>

    @can('invoice')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoices*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <span>Invoices</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoiceDetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoiceDetails*') ? 'active' : '' }}" href="{{ route('invoiceDetails.index') }}">
                    <span>Invoice Details</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('invoicePayments*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('invoicePayments*') ? 'active' : '' }}" href="{{ route('invoicePayments.index') }}">
                    <span>Payments</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany

@canany(['task'])
<li class="nav-item {{ Request::is('tasks*','taskTransfers*') ? 'open' : '' }}">
    <a class="nav-link {{ Request::is('tasks*','taskTransfers*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
        <i class="nav-icon icon-menu"></i>
        <span>Tasks</span>
    </a>
</li>
@endcanany

@canany(['task'])
<li class="nav-item {{ Request::is('trips*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('trips.index') }}">
        <i class="nav-icon icon-login"></i>
        <span>Trips</span>
    </a>
</li>
@endcanany

@canany(['inventorybalance','inventorytransaction'])
<li class="nav-item nav-dropdown {{ Request::is('inventoryBalances*','inventoryTransactions*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-drawer"></i>
        <span>Inventory</span>
    </a>

    @can('inventorybalance')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryBalances*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryBalances*') ? 'active' : '' }}" href="{{ route('inventoryBalances.index') }}">
                    <span>Balances</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('inventorytransaction')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryTransactions*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryTransactions*') ? 'active' : '' }}" href="{{ route('inventoryTransactions.index') }}">
                    <span>Transactions</span>
                </a>
            </li>
        </ul>
    @endcan
    @can('inventorytransfer')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('inventoryTransfers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('inventoryTransfers*') ? 'active' : '' }}" href="{{ route('inventoryTransfers.index') }}">
                    <span>Transfers</span>
                </a>
            </li>
        </ul>
    @endcan
</li>
@endcanany

@canany(['lorry','driver','kelindan','agent','supervisor','product','customer','specialprice','foc','assigns'])
<li class="nav-item nav-dropdown {{ Request::is('lorries*','servicedetails*','drivers*','driverLocations*','kelindans*','agents*','supervisors*','products*','customers*','specialprice*','focs*','assigns*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-list"></i>
        <span>Master Data</span>
    </a>

    @can('lorry')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('lorries*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('lorries*') ? 'active' : '' }}" href="{{ route('lorries.index') }}">
                    <span>Lorries</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('servicedetails*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('servicedetails*') ? 'active' : '' }}" href="{{ route('servicedetails.index') }}">
                    <span>Lorry Service</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('driver')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('drivers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('drivers*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                    <span>Drivers</span>
                </a>
            </li>
        </ul>
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('driverLocations*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('driverLocations*') ? 'active' : '' }}" href="{{ route('driverLocations.index') }}">
                    <span>Driver Locations</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('kelindan')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('kelindans*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('kelindans*') ? 'active' : '' }}" href="{{ route('kelindans.index') }}">
                    <span>Kelindans</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('agent')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('agents*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('agents*') ? 'active' : '' }}" href="{{ route('agents.index') }}">
                    <span>Agents</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('supervisor')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('supervisors*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('supervisors*') ? 'active' : '' }}" href="{{ route('supervisors.index') }}">
                    <span>Operations</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('product')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('products*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('products*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <span>Products</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('customer')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('customers*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('customers*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <span>Customers</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('company')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('companies*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('companies*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                    <span>Companies</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('specialprice')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('specialprice*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('specialprice*') ? 'active' : '' }}" href="{{ route('specialPrices.index') }}">
                    <span>Special Price</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('foc')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('focs*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('focs*') ? 'active' : '' }}" href="{{ route('focs.index') }}">
                    <span>Focs</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('assign')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('assigns*') ? 'active' : '' }}">
                <a class="nav-link {{ Request::is('assigns*') ? 'active' : '' }}" href="{{ route('assigns.index') }}">
                    <span>Assigns</span>
                </a>
            </li>
        </ul>
    @endcan

</li>

</li>
@endcanany

@canany(['code'])
<li class="nav-item nav-dropdown {{ Request::is('codes*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-settings"></i>
        <span>Setup</span>
    </a>

    @can('code')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('codes*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('codes*') ? 'active' : '' }}" href="{{ route('codes.index') }}">
                    <span>Codes</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('codes*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('codes*') ? 'active' : '' }}" href="{{ route('customer_group.index') }}">
                    <span>Customer Group</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('codes*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('codes*') ? 'active' : '' }}" href="{{ route('commission_group.index') }}">
                    <span>Commission</span>
                </a>
            </li>
        </ul>
    @endcan

</li>
@endcanany


@canany(['user','userrole','role','rolepermission'])
<li class="nav-item nav-dropdown {{ Request::is('users*','userHasRoles*','roles*','roleHasPermissions*','permissions*') ? 'open' : '' }}">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon icon-people"></i>
        <span>User Management</span>
    </a>

    @can('user')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('users*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('users*') ? 'active' : '' }}" href="{!! route('users.index') !!}">
                    <span>Users</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('userrole')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('userHasRoles*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('userHasRoles*') ? 'active' : '' }}" href="{{ route('userHasRoles.index') }}">
                    <span>User Roles</span>
                </a>
            </li>
        </ul>-->
    @endcan

    @can('role')
        <ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('roles*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('roles*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <span>Roles</span>
                </a>
            </li>
        </ul>
    @endcan

    @can('rolepermission')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('roleHasPermissions*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('roleHasPermissions*') ? 'active' : '' }}" href="{{ route('roleHasPermissions.index') }}">
                    <span>Role Permissions</span>
                </a>
            </li>
        </ul>-->
    @endcan

    @if(env('APP_ENV') == 'local')
        <!--<ul class="nav-dropdown-items">
            <li class="nav-item {{ Request::is('permissions*') ? 'active open' : '' }}">
                <a class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                    <span>Permissions</span>
                </a>
            </li>
        </ul>-->
    @endif

</li>
@endcanany

@can('report')
    <li class="nav-item {{ Request::is('reports*') ? 'active' : '' }}">
        <a class="nav-link {{ Request::is('reports*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
            <i class="nav-icon icon-cursor"></i>
            <span>Reports</span>
        </a>
    </li>

    @if(env('APP_ENV') == 'local')
    @endif

@endcan
