<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
                $file = "";

                $routes = [
                    'dashboard' => 'module/md_dashboard.php',
                    'apar-ace' => 'module/ace/index.php',
                    'hydrant-ace' => 'module/hydrant/ace/index.php',
                    'user-management' => 'module/user/index.php',
                    'add-user' => 'module/user/create.php'
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
</body>

</html>