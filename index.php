<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . "/config/middleware/auth_middleware.php");
check_auth();
?>
<!DOCTYPE html>
<html lang="en">

<?php
include("components/fragments/head.php");
?>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php
        include("components/ui/sidebar.php");
        ?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <?php
                    include("components/ui/logo.php");
                    ?>
                    <!-- End Logo Header -->
                </div>
                <!-- Navbar Header -->
                <?php
                include("components/ui/navbar.php");
                ?>
                <!-- End Navbar -->
            </div>

            <!-- Script JS -->
            <?php
            include("components/fragments/script.php");
            ?>
            <!-- End script JS -->

            <div class="container">
                <!-- content -->
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                
                // Role Protection
                $protected_pages = ['user-management', 'add-user', 'edit-user'];
                if (in_array($page, $protected_pages)) {
                    check_admin();
                }

                $file = "";

                $routes = [
                    'dashboard' => 'module/md_dashboard.php',
                    'apar-ace' => 'module/apar/ace/index.php',
                    'apar-machining' => 'module/apar/machining/index.php',
                    'apar-office' => 'module/apar/office/index.php',
                    'apar-disa' => 'module/apar/disa/index.php',
                    'apar-all-list' => 'module/apar/all_list.php',
                    'hydrant-ace' => 'module/hydrant/ace/index.php',
                    'user-management' => 'module/user/index.php',
                    'add-user' => 'module/user/create.php',
                    'edit-user' => 'module/user/edit.php',
                    'report' => 'module/report/index.php'
                ];

                if (array_key_exists($page, $routes)) {
                    $file = $routes[$page];
                } else {
                    $file = "module/md_" . $page . ".php";
                }

                if (file_exists($file)) {
                    include($file);
                } else {
                    echo "<h1>Halaman <b>" . htmlspecialchars($page) . "</b> tidak ditemukan ($file)</h1>";
                }
                ?>
                <!-- End content -->
            </div>

            <!-- footer -->
            <?php
            include("components/ui/footer.php");
            ?>
            <!-- End footer -->
        </div>
    </div>

    <?php include("components/modal/map.php"); ?>

    <script>
    $(document).ready(function() {

        // Logout Handler
        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            swal({
                title: 'Apakah anda yakin?',
                text: "Sesi anda akan segera berakhir!",
                type: 'warning',
                buttons: {
                    cancel: {
                        visible: true,
                        text: 'Tidak, tetap disini',
                        className: 'btn btn-danger'
                    },
                    confirm: {
                        text: 'Ya, Logout!',
                        className: 'btn btn-success'
                    }
                }
            }).then((willLogout) => {
                if (willLogout) {
                    window.location.href = 'actions/auth/ac_auth.php?action=logout';
                }
            });
        });
    });
    </script>
</body>

</html>