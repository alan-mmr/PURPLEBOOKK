<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'PURPLEBOOK - Book Collection System')</title>
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
    
    @stack('styles')
</head>
<body>
    <div class="container-scroller">
        <!-- Navbar -->
        @include('layouts.navbar')
        
        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            @include('layouts.sidebar')
            
            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <!-- Page Content -->
                    @yield('content')
                </div>
                
                <!-- Footer -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                            Copyright © {{ date('Y') }} PURPLEBOOK. All rights reserved.
                        </span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    
    <!-- Template JS -->
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/misc.js') }}"></script>
    
    <!-- Dropdown Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.getElementById('profileDropdown');
        if (dropdownToggle) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu) {
                    dropdownMenu.classList.toggle('show');
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.nav-profile')) {
                    const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                    dropdowns.forEach(dd => dd.classList.remove('show'));
                }
            });
        }
    });
    </script>
    
    @stack('scripts')
</body>
</html>
