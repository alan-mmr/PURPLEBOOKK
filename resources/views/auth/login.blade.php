<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PURPLEBOOK</title>
    
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">
                            <div class="brand-logo text-center">
                                <h2 style="color: #6f42c1; font-weight: bold;">PURPLEBOOK</h2>
                                <p class="text-muted">Book Collection System</p>
                            </div>
                            <h4 class="text-center">Hello! Let's get started</h4>
                            <h6 class="font-weight-light text-center">Sign in to continue.</h6>
                            
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                            
                            <form class="pt-3" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-lg" 
                                           name="email" placeholder="Email" value="{{ old('email') }}" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-lg" 
                                           name="password" placeholder="Password" required>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">
                                        SIGN IN
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS -->
    <script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/misc.js') }}"></script>
</body>
</html>
