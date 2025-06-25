<style>
    .nav-link:hover {
        background-color: #f8f9fa;
    }

    .nav-link.active {
        color: #fff !important;
    }
</style>
<div class="d-flex flex-column flex-shrink-0 p-3 bg-light rounded shadow-sm" style="min-height: 100%; min-width: 200px;">
    <ul class="nav nav-pills flex-column mb-auto account-nav">
        <li class="nav-item">
            <a href="{{ route('user.index') }}"
               class="menu-link menu-link_us-s nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
               <i class="bi bi-house me-2"></i> Pengaturan Akun
            </a>
        </li>
        <li>
            <a href="{{ route('user.orders') }}"
               class="menu-link menu-link_us-s nav-link {{ request()->routeIs('user.orders') ? 'active' : '' }}">
               <i class="bi bi-bag me-2"></i> Orders
            </a>
        </li>
        <li>
            <a href="{{route('user.addresses')}}"
               class="menu-link menu-link_us-s nav-link {{ request()->routeIs('user.addresses') ? 'active' : '' }}">
               <i class="bi bi-geo-alt me-2"></i> Addresses
            </a>
        </li>

        <li>
            <a href="{{ route('wishlist.index') }}"
               class="menu-link menu-link_us-s nav-link {{ request()->routeIs('wishlist.index') ? 'active' : '' }}">
               <i class="bi bi-heart me-2"></i> Wishlist
            </a>
        </li>
        <li>
            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <a href="{{ route('logout') }}"
                   class="menu-link menu-link_us-s nav-link"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                   <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </form>
        </li>
    </ul>
</div>