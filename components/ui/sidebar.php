<?php
$halamanSekarang = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

function cekMenuAktif($halamanSaatIni, $targetMenu) {
    return ($halamanSaatIni == $targetMenu) ? 'active' : '';
}

function cekGrupAktif($halamanSaatIni, $kataDepan) {
    return (strpos($halamanSaatIni, $kataDepan) === 0);
}

$grupAparAktif = cekGrupAktif($halamanSekarang, 'apar-');
$grupHydrantAktif = cekGrupAktif($halamanSekarang, 'hydrant-');
?>
<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
            <a href="index.php?page=dashboard" class="logo">
                <img src="assets/img/kaiadmin/logo_light.svg" alt="navbar brand" class="navbar-brand" height="20" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
            <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
            </button>
        </div>
        <!-- End Logo Header -->
    </div>
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Main</h4>
                </li>
                <li class="nav-item <?= cekMenuAktif($halamanSekarang, 'dashboard') ?>">
                    <a href="index.php?page=dashboard">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-section">
                    <span class="sidebar-mini-icon">
                        <i class="fa fa-ellipsis-h"></i>
                    </span>
                    <h4 class="text-section">Navigation</h4>
                </li>
                <li class="nav-item <?= $grupAparAktif ? 'active submenu' : '' ?>">
                    <a data-bs-toggle="collapse" href="#apar" class="<?= $grupAparAktif ? '' : 'collapsed' ?>" aria-expanded="<?= $grupAparAktif ? 'true' : 'false' ?>">
                        <i class="fas fa-fire-extinguisher me-2"></i>
                        <p>Data APAR</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?= $grupAparAktif ? 'show' : '' ?>" id="apar">
                        <ul class="nav nav-collapse">
                            <li class="<?= cekMenuAktif($halamanSekarang, 'apar-ace') ?>">
                                <a href="index.php?page=apar-ace">
                                    <span class="sub-item">ACE</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'apar-machining') ?>">
                                <a href="index.php?page=apar-machining">
                                    <span class="sub-item">Machining</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'apar-office') ?>">
                                <a href="index.php?page=apar-office">
                                    <span class="sub-item">Office</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'apar-disa') ?>">
                                <a href="index.php?page=apar-disa">
                                    <span class="sub-item">Disa</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'apar-all-list') ?>">
                                <a href="index.php?page=apar-all-list">
                                    <span class="sub-item">All List</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?= $grupHydrantAktif ? 'active submenu' : '' ?>">
                    <a data-bs-toggle="collapse" href="#hydrant" class="<?= $grupHydrantAktif ? '' : 'collapsed' ?>" aria-expanded="<?= $grupHydrantAktif ? 'true' : 'false' ?>">
                        <i class="fas fa-shield-alt"></i>
                        <p>Data Hydrant</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse <?= $grupHydrantAktif ? 'show' : '' ?>" id="hydrant">
                        <ul class="nav nav-collapse">
                            <li class="<?= cekMenuAktif($halamanSekarang, 'hydrant-ace') ?>">
                                <a href="index.php?page=hydrant-ace">
                                    <span class="sub-item">ACE</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'hydrant-machining') ?>">
                                <a href="index.php?page=hydrant-machining">
                                    <span class="sub-item">Machining</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'hydrant-office') ?>">
                                <a href="index.php?page=hydrant-office">
                                    <span class="sub-item">Office</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'hydrant-disa') ?>">
                                <a href="index.php?page=hydrant-disa">
                                    <span class="sub-item">Disa</span>
                                </a>
                            </li>
                            <li class="<?= cekMenuAktif($halamanSekarang, 'hydrant-all-list') ?>">
                                <a href="index.php?page=hydrant-all-list">
                                    <span class="sub-item">All List</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item <?= cekMenuAktif($halamanSekarang, 'report') ?>">
                    <a href="index.php?page=report">
                        <i class="fas fa-file-alt"></i>
                        <p>Report</p>
                    </a>
                </li>
                <li class="nav-item <?= cekMenuAktif($halamanSekarang, 'user-management') ?>">
                    <a href="index.php?page=user-management">
                        <i class="fas fa-users"></i>
                        <p>User Management</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>