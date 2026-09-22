<x-base-layout>
    <div class="section-title-01 honmob">
        <div class="bg_parallax image_01_parallax"></div>
        <div class="opacy_bg_02">
            <div class="container">
                <h1>Login</h1>
                <div class="crumbs">
                    <ul>
                        <li><a href="/"><button class="glass-button">Home</button></a></li>
                        <li>/</li>
                        <li>Login</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <section class="glass-card content-central">
        <div class="neomorph-card">
            <div class="paddings-mini">
                <div class="neomorph-card">
                    <div class="row portfolioContainer">
                        <div class="col-xs-12 col-sm-3 col-md-3 profile1"></div>
                        <div class="col-xs-12 col-sm-6 col-md-6 profile1" style="min-height: 300px;">
                            <div class="thinborder-ontop">
                                <h3>Login Info</h3>
                                <form id="userloginform" method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <label for="email" class="col-sm-4 col-form-label text-md-right">E-Mail Address</label>
                                        <div class="col-md-6">
                                            <input id="email" type="email" class="form-control" name="email" :value="old('email')" required autofocus>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                                        <div class="col-md-6" style="position: relative;">
                                            <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" style="padding-right: 45px;">
                                            <button type="button" id="togglePassword" class="btn btn-sm btn-link" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); border: none; background: transparent; text-decoration: none; z-index: 10;">👁️</button>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="remember_me" name="remember"> Remember Me
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="glass-button pull-right" id="loginButton">Login</button>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <div class="col-md-10">
                                            <a class="" href="{{ route('password.request') }}">Forgot Your Password?</a>
                                            <p style="margin-top: 15px; font-size: 14px;">Don't have an account? <a href="{{ route('register') }}" id="switchToRegister">Register here</a></p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-3 col-md-3 profile1"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SweetAlert2 Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
        });
    </script>
    @endif

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
        });
    </script>
    @endif

    <script>
        // Manejar el envío del formulario
        document.getElementById('userloginform').addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Logging in...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            this.submit();
        });

        // Alternar visibilidad de la contraseña
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', () => {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
    </script>
</x-base-layout>