<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Login - Monitoring APAR & Hydrant</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/kaiadmin/favicon.ico" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: ["Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"],
                urls: ["assets/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <style>
        body {
            background: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            overflow: hidden;
            font-family: 'Public Sans', sans-serif;
        }

        .login-wrapper {
            width: 100%;
            height: 100vh;
            display: flex;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .login-side-image {
            flex: 1.2;
            background: url('assets/img/examples/example6.jpeg') no-repeat center center;
            background-size: cover;
            position: relative;
        }

        .login-side-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(29, 122, 243, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%);
        }

        .login-side-form {
            flex: 0.8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #fff;
            z-index: 10;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo img {
            /* height: 60px; */
            width: 140px;
            margin-bottom: 15px;
        }

        .login-logo h2 {
            font-weight: 700;
            color: #1a2035;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }

        .login-logo p {
            color: #8d94ad;
            font-size: 14px;
        }

        .form-group-default {
            border: 2px solid #ebedf2 !important;
            border-radius: 12px !important;
            padding: 8px 15px !important;
            transition: all 0.3s;
            background: #fbfbfb !important;
        }

        .form-group-default:focus-within {
            border-color: #1d7af3 !important;
            background: #fff !important;
            box-shadow: 0 5px 15px rgba(29, 122, 243, 0.1);
        }

        .form-group-default label {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: #4d5467 !important;
            text-transform: uppercase;
            margin-bottom: 2px !important;
        }

        .form-control {
            border: none !important;
            padding: 0 !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            height: auto !important;
            background: transparent !important;
        }

        .btn-login {
            background: #1d7af3 !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 14px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
            box-shadow: 0 10px 20px rgba(29, 122, 243, 0.3);
            transition: all 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(29, 122, 243, 0.4);
        }

        .input-group-text {
            background: transparent !important;
            border: none !important;
            cursor: pointer;
            padding-right: 0;
            color: #8d94ad;
        }

        .footer-text {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #8d94ad;
        }

        @media (max-width: 991px) {
            .login-side-image {
                display: none;
            }
            .login-side-form {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-side-image"></div>
        <div class="login-side-form">
            <div class="login-box">
                <div class="login-logo">
                    <img src="assets/img/ati-logo.png" alt="Logo">
                    <h2>PT AT Indonesia</h2>
                    <p>Monitoring System APAR & Hydrant</p>
                </div>
                
                <form action="actions/auth/ac_auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <?php if (isset($_GET['redirect_to'])): ?>
                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_GET['redirect_to']) ?>">
                    <?php endif; ?>
                    
                    <div class="form-group form-group-default">
                        <label>User ID / NPK</label>
                        <input type="text" name="npk" class="form-control" placeholder="Masukkan User ID / NPK" required autofocus autocomplete="off">
                    </div>

                    <div class="form-group form-group-default mt-3">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required>
                            <span class="input-group-text" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-login">
                        LOGIN
                    </button>
                    
                    <div class="footer-text">
                        Jl. Maligi III H1-5, Kawasan Industri KIIC Karawang<br>
                        Telp : (021) 890 4376 / Fax : (021) 890 4375
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <!-- Notify -->
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <script>
        $(document).ready(function() {
            // Password toggle
            $('#togglePassword').on('click', function() {
                const password = $('#password');
                const icon = $(this).find('i');
                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    password.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Alerts
            <?php if (isset($_SESSION['error'])): ?>
                $.notify({
                    icon: 'fas fa-exclamation-triangle',
                    title: 'Login Gagal',
                    message: '<?= $_SESSION['error'] ?>',
                }, {
                    type: 'danger',
                    placement: { from: "top", align: "right" },
                    time: 1000,
                });
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                $.notify({
                    icon: 'fas fa-check-circle',
                    title: 'Berhasil',
                    message: '<?= $_SESSION['success'] ?>',
                }, {
                    type: 'success',
                    placement: { from: "top", align: "right" },
                    time: 1000,
                });
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
