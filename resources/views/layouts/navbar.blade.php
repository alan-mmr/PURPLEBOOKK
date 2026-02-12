<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
            <strong style="color: #6f42c1; font-size: 24px;">PURPLEBOOK</strong>
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
            <strong style="color: #6f42c1;">PB</strong>
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
        
        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
                <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                    <div class="nav-profile-img">
                        <i class="mdi mdi-account-circle" style="font-size: 32px;"></i>
                    </div>
                    <div class="nav-profile-text">
                        <p class="mb-1 text-black">{{ Auth::user()->name }}</p>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="#">
                        <i class="mdi mdi-account mr-2 text-success"></i> Profile
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;">
                            <i class="mdi mdi-logout mr-2 text-primary"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
