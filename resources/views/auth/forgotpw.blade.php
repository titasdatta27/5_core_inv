<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.shared/title-meta', ['title' => 'Recover Password'])

    @include('layouts.shared/head-css', ['mode' => $mode ?? '', 'demo' => $demo ?? ''])
</head>

<body class="authentication-bg">

    <div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-lg-10">
                    <div class="card overflow-hidden">
                        <div class="row g-0">
                            <div class="col-lg-6 d-none d-lg-block p-2">
                                <img src="/images/auth-img.jpg" alt="" class="img-fluid rounded h-100">
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex flex-column h-100">
                                    <div class="auth-brand p-4">
                                        <a href="{{ route('any', 'index') }}" class="logo-light">
                                            <img src="/images/logo.png" alt="logo" height="22">
                                        </a>
                                        <a href="{{ route('any', 'index') }}" class="logo-dark">
                                            <img src="/images/logo-dark.png" alt="dark logo" height="22">
                                        </a>
                                    </div>
                                    <div class="p-4 my-auto">
                                        <h4 class="fs-20">Forgot Password?</h4>
                                        <p class="text-muted mb-3">Enter your email address and we'll send you an email
                                            with instructions to reset your password.</p>


                                        <!-- form -->
                                        <form action="#">
                                            <div class="mb-3">
                                                <label for="emailaddress" class="form-label">Email address</label>
                                                <input class="form-control" type="email" id="emailaddress" required="" placeholder="Enter your email">
                                            </div>

                                            <div class="mb-0 text-start">
                                                <button class="btn btn-soft-primary w-100" type="submit"><i class="ri-loop-left-line me-1 fw-bold"></i> <span class="fw-bold">Reset Password</span> </button>
                                            </div>
                                        </form>
                                        <!-- end form-->
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-dark-emphasis">Back To <a href="{{ route('second', [ 'auth' , 'login']) }}" class="text-dark fw-bold ms-1 link-offset-3 text-decoration-underline"><b>Log In</b></a></p>
                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->

    <footer class="footer footer-alt fw-medium">
        <span class="text-dark-emphasis">
            <script>
                document.write(new Date().getFullYear())
            </script> © Velonic - Theme by Techzaa
        </span>
    </footer>

    @include('layouts.shared/footer-scripts')

</body>

</html>
