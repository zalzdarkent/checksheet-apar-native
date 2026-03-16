<!DOCTYPE html>
<html lang="en">

<?php
include("module/fragments/head.php");
?>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php
        include("module/ui/sidebar.php");
        ?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <?php
                    include("module/ui/logo.php");
                    ?>
                    <!-- End Logo Header -->
                </div>
                <!-- Navbar Header -->
                <?php
                include("module/ui/navbar.php");
                ?>
                <!-- End Navbar -->
            </div>

            <div class="container">
                <!-- content -->
                <?php
                $halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'dashboard';

                $file = "module/md_" . $halaman . ".php";

                if (file_exists($file)) {
                    include($file);
                } else {
                    echo "<h1>Halaman tidak ditemukan</h1>";
                }
                ?>
                <!-- End content -->
            </div>

            <!-- footer -->
            <?php
            include("module/ui/footer.php");
            ?>
            <!-- End footer -->
        </div>
    </div>
    <!-- Script JS -->
    <?php
    include("module/fragments/script.php");
    ?>
    <!-- End script JS -->
</body>

</html>