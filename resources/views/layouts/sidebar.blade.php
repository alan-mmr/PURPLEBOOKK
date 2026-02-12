<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <!-- Dashboard -->
        <li class="nav-item {{ request()->is('/') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
            </a>
        </li>
        
        <!-- Kategori Management -->
        <li class="nav-item {{ request()->is('kategori*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('kategori.index') }}">
                <span class="menu-title">Kategori</span>
                <i class="mdi mdi-label-outline menu-icon"></i>
            </a>
        </li>
        
        <!-- Buku Management -->
        <li class="nav-item {{ request()->is('buku*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('buku.index') }}">
                <span class="menu-title">Buku</span>
                <i class="mdi mdi-book-open-page-variant menu-icon"></i>
            </a>
        </li>
    </ul>
</nav>
