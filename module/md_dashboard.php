<?php
include("actions/dashboard/ac_dashboard.php");

$usersPIC = [];
// Fetch from UserTable to allow any employee to be a PIC
$resUsers = sqlsrv_query($koneksi, "SELECT EMPID as id, EmployeeName as name FROM [ATI].[Users].[UserTable] ORDER BY EmployeeName ASC");
if ($resUsers !== false) {
    while ($u = sqlsrv_fetch_array($resUsers, SQLSRV_FETCH_ASSOC)) {
        $usersPIC[] = $u;
    }
}

// apar
$totalApar = get_total_apar();
$totalAparOK = get_total_apar_ok();
$totalAparProses = get_total_apar_proses();
$totalAparAbnormal = get_total_apar_abnormal();

// hydrant
$totalHydrant = get_total_hydrant();
$totalHydrantOK = get_total_hydrant_ok();
$totalHydrantProses = get_total_hydrant_proses();
$totalHydrantAbnormal = get_total_hydrant_abnormal();

// abnormal cases
$aparAbnormalCases = get_apar_abnormal_cases();
$hydrantAbnormalCases = get_hydrant_abnormal_cases();
?>


<style>
    /* Consistent Color Scope */
    :root {
        --c-ok: #28a745;
        --c-proses: #ffc107;
        --c-abnormal: #dc3545;
        --c-total: #007bff;
    }

    body {
        background-color: #f4f6f9;
        /* Subtle contrast */
    }

    /* Layout tweaks */
    .page-inner {
        padding: 1.5rem !important;
    }

    .card {
        margin-bottom: 1rem !important;
    }

    /* Summary Cards compact */
    .summary-card {
        border-radius: 8px;
        background: #fff;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        border-left: 5px solid;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .summary-card.total {
        border-color: var(--c-total);
    }

    .summary-card.ok {
        border-color: var(--c-ok);
    }

    .summary-card.proses {
        border-color: var(--c-proses);
    }

    .summary-card.abn {
        border-color: var(--c-abnormal);
    }

    .summary-card h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1;
    }

    .summary-card span.stat-title {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        display: block;
        color: #555;
        margin-bottom: 1px;
    }

    .summary-card .breakdown {
        font-size: 0.65rem;
        color: #888;
    }

    /* Map Container */
    .map-container-custom {
        position: relative;
        width: 100%;
        overflow: hidden;
        border-radius: 6px;
        height: 380px;
        background: #fff;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #eee;
    }

    .map-container-custom img {
        width: 100%;
        height: 100%;
        object-fit: fill;
        display: block;
    }

    .table-dashboard {
        font-size: 0.85rem;
    }

    .table-dashboard th {
        background: #f8f9fa;
        font-weight: 700;
        white-space: nowrap;
        padding: 10px 8px !important;
    }

    .table-dashboard td {
        vertical-align: middle;
        padding: 8px !important;
    }

    /* Responsive adjustments untuk mobile */
    @media (max-width: 768px) {

        .col-md-5,
        .col-md-7 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        .map-container-custom {
            height: 300px;
        }

        #chartAPAR,
        #chartHydrant {
            height: 100px !important;
        }
    }

    .map-marker {
        position: absolute;
        width: 22px;
        height: 22px;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 6px;
        font-weight: bold;
        transform: translate(-50%, -50%);
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        z-index: 10;
        cursor: crosshair;
    }

    .map-marker:hover {
        transform: translate(-50%, -50%) scale(1.4);
        z-index: 20;
    }

    .marker-proses {
        background-color: var(--c-proses);
        color: #000;
    }

    .marker-ok {
        background-color: var(--c-ok);
        color: #fff;
    }

    .marker-abnormal {
        background-color: var(--c-abnormal);
        color: #fff;
    }

    /* Tooltip Map */
    .custom-map-tooltip {
        position: absolute;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        pointer-events: none;
        display: none;
        min-width: 220px;
        font-size: 11px;
        text-align: left;
    }

    .custom-map-tooltip table td {
        padding: 2px 4px 2px 0;
    }

    /* Compact List */
    .compact-list {
        height: 480px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 5px;
        background: #fff;
    }

    .compact-list::-webkit-scrollbar {
        width: 6px;
    }

    .compact-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }

    .case-item {
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        background: #fafafa;
        border-left: 3px solid var(--c-abnormal);
        position: relative;
    }

    .case-item.open {
        border-left-color: var(--c-abnormal);
    }

    /* Red - Open */
    .case-item.closed {
        border-left-color: #007bff;
    }

    /* Blue - Closed */
    .case-item.verified {
        border-left-color: var(--c-ok);
        opacity: 0.7;
    }

    /* Green - Verified */
    .case-item-title {
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 4px;
        display: flex;
        justify-content: space-between;
    }

    .case-item-desc {
        font-size: 0.75rem;
        color: #555;
        margin-bottom: 6px;
        line-height: 1.3;
    }

    .case-item-meta {
        font-size: 0.7rem;
        color: #888;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .case-item-actions {
        display: flex;
        gap: 4px;
        margin-top: 8px;
    }

    .case-item-actions .btn {
        font-size: 0.65rem;
        padding: 3px 6px;
        line-height: 1;
    }

    .avatar-mini {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: cover;
    }

    .nav-compact .nav-link {
        font-size: 0.8rem;
        padding: 6px 12px;
    }

    .apx-title {
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 0px;
        margin-bottom: 8px;
        text-align: center;
    }

    /* Premium Modal & NG Card Styles */
    .ng-item-card {
        background-color: #fff !important;
        border: 1px solid #e0e6ed !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
        overflow: hidden;
        position: relative;
    }

    .ng-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.06) !important;
        border-color: #d1d9e6 !important;
    }

    .ng-item-card.selected-revisi {
        border-color: #dc3545 !important;
        background-color: #fff5f5 !important;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important;
    }

    .ng-card-checkbox {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .info-asset-pill {
        background: #f8f9fa;
        border-radius: 50px;
        padding: 5px 15px;
        font-size: 0.75rem;
        color: #666;
        border: 1px solid #eee;
        display: inline-flex;
        align-items: center;
        margin-right: 5px;
        margin-bottom: 5px;
    }

    .info-asset-pill i {
        margin-right: 6px;
        color: #007bff;
    }

    .img-premium {
        width: 100%;
        height: 110px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
        cursor: zoom-in;
        transition: opacity 0.2s;
    }

    .img-premium:hover {
        opacity: 0.9;
    }

    .section-premium-label {
        font-size: 0.8rem;
        font-weight: 800;
        color: #444;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-left: 3px solid #007bff;
        padding-left: 10px;
        margin-bottom: 15px;
    }
</style>

<div class="page-inner">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h4 class="fw-bold mb-0 text-dark">Safety & Environment Center</h4>
    </div>

    <!-- DATE FILTER SECTION -->
    <div class="alert alert-light rounded mb-2 py-2 px-3" style="background: #f8f9fa;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="mb-0 fw-bold text-muted small">Filter:</label>
            <input type="date" id="filterStartDate" class="form-control form-control-sm" style="max-width: 130px;">
            <span class="text-muted small">-</span>
            <input type="date" id="filterEndDate" class="form-control form-control-sm" style="max-width: 130px;">
            <button id="btnClearFilter" class="btn btn-sm btn-light border border-secondary ms-2"
                style="font-size: 0.75rem; padding: 4px 10px;">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>

    <!-- ROW 1: COMPACT SUMMARY CARDS -->
    <div class="row g-1 mb-2">
        <!-- Total -->
        <div class="col-6 col-md-4">
            <div class="summary-card total">
                <div class="flex-grow-1">
                    <span class="stat-title">Total Unit</span>
                    <h2><?php echo ($totalApar + $totalHydrant); ?></h2>
                    <div class="breakdown"><i class="fas fa-fire-extinguisher text-danger"></i>
                        <?php echo $totalApar; ?> | <i class="fas fa-water text-info"></i> <?php echo $totalHydrant; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- OK -->
        <div class="col-6 col-md-4">
            <div class="summary-card ok">
                <div class="flex-grow-1">
                    <span class="stat-title text-success">Aman (OK)</span>
                    <h2 class="text-success"><?php echo ($totalAparOK + $totalHydrantOK); ?></h2>
                    <div class="breakdown"><i class="fas fa-fire-extinguisher"></i> <?php echo $totalAparOK; ?> | <i
                            class="fas fa-water"></i> <?php echo $totalHydrantOK; ?></div>
                </div>
            </div>
        </div>
        <!-- ABNORMAL -->
        <div class="col-6 col-md-4">
            <div class="summary-card abn">
                <div class="flex-grow-1">
                    <span class="stat-title text-danger">Abnormal / Temuan</span>
                    <h2 class="text-danger"><?php echo ($totalAparAbnormal + $totalHydrantAbnormal); ?></h2>
                    <div class="breakdown"><i class="fas fa-fire-extinguisher border-end pe-1"></i>
                        <?php echo $totalAparAbnormal; ?> | <i class="fas fa-water"></i>
                        <?php echo $totalHydrantAbnormal; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 2: CORE CONTROL CENTER (Optimized Layout) -->
    <div class="row g-1">

        <!-- LEFT: GRAPHS (col-md-5 untuk proporsi lebih baik) -->
        <div class="col-md-5">
            <div class="card card-round shadow-sm">
                <div class="card-body p-2 d-flex flex-column">
                    <div>
                        <div class="apx-title"><i class="fas fa-fire-extinguisher text-danger"></i> Rasio APAR</div>
                        <div id="chartAPAR" style="height: 120px;"></div>
                    </div>
                    <hr class="my-1">
                    <div>
                        <div class="apx-title"><i class="fas fa-shield-alt text-info"></i> Rasio Hydrant</div>
                        <div id="chartHydrant" style="height: 120px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: MAP (col-md-7 untuk lebih lebar) -->
        <div class="col-md-7">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-white p-2 border-0 d-flex justify-content-between align-items-center"
                    style="min-height: auto;">
                    <div class="fw-bold" style="font-size:0.85rem;"><i class="fas fa-map-marked-alt text-primary"></i>
                        Live Factory Map</div>
                </div>
                <div class="card-body p-2 pt-0">
                    <div class="map-container-custom">
                        <img src="assets/img/ati-layout.jpeg" alt="Layout Map">
                        <!-- Tooltip -->
                        <div id="marker-tooltip" class="custom-map-tooltip">
                            <table class="table-borderless w-100">
                                <tbody>
                                    <tr>
                                        <td style="width: 50px;" class="fw-bold">Kode</td>
                                        <td id="tt-kode"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status</td>
                                        <td><span id="tt-status" class="badge"></span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Jenis</td>
                                        <td id="tt-jenis"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Area</td>
                                        <td id="tt-area"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-danger">Issue</td>
                                        <td id="tt-keterangan" class="text-danger fw-bold"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End Row 2 -->

    <!-- ROW 3: FULL ACTION TABLE -->
    <div class="row g-2 mt-2 pb-5">
        <div class="col-md-12">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-white p-2 border-0" style="padding-bottom: 0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold text-danger" style="font-size:1rem;"><i
                                class="fas fa-exclamation-triangle"></i> Action Required / Temuan Abnormal</div>
                    </div>
                    <ul class="nav nav-tabs border-0" id="actionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-danger px-4" id="apar-tab-btn"
                                data-bs-toggle="tab" data-bs-target="#apar-cases-tab" type="button" role="tab">APAR
                                (<?= count($aparAbnormalCases) ?>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-info px-4" id="hydrant-tab-btn" data-bs-toggle="tab"
                                data-bs-target="#hydrant-cases-tab" type="button" role="tab">Hydrant
                                (<?= count($hydrantAbnormalCases) ?>)</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content">

                        <!-- APAR CASES TAB -->
                        <div class="tab-pane fade show active" id="apar-cases-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table id="apar-action-table"
                                    class="display table table-striped table-hover table-dashboard w-100">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Area</th>
                                            <th>Issue</th>
                                            <th>Countermeasure</th>
                                            <th>PIC</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($aparAbnormalCases as $case):
                                            // Pre-calculate session values if needed here or use data-attributes
                                            ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($case['code']) ?></strong></td>
                                                <td><?= htmlspecialchars($case['area']) ?></td>
                                                <td class="text-danger fw-bold"
                                                    style="max-width:220px; white-space:normal;">
                                                    <?= htmlspecialchars($case['abnormal_case']) ?>
                                                    <?php if (($case['ng_count'] ?? 1) > 1): ?>
                                                        <span class="badge bg-danger d-block mt-1" style="font-size:0.6rem;">
                                                            <?= $case['ng_count'] ?> item NG
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($case['countermeasure'] ?: '-') ?></td>
                                                <td>
                                                    <?php if ($case['pic_name']): ?>
                                                        <span title="<?= htmlspecialchars($case['pic_name']) ?>"><img
                                                                src="storage/users/<?= htmlspecialchars($case['pic_photo'] ?: 'default.png') ?>"
                                                                class="avatar-mini me-1">
                                                            <?= htmlspecialchars($case['pic_name']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-danger"><i class="fas fa-user-times"></i>-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-' ?></td>
                                                <td>
                                                    <?php
                                                    if ($case['status'] === 'Open')
                                                        echo '<span class="badge bg-danger">Open</span>';
                                                    elseif ($case['status'] === 'Revision')
                                                        echo '<span class="badge bg-danger"><i class="fas fa-undo me-1"></i>Revisi</span>';
                                                    elseif ($case['status'] === 'Closed')
                                                        echo '<span class="badge bg-info">Closed</span>';
                                                    elseif ($case['status'] === 'Verified')
                                                        echo '<span class="badge bg-success">Verified</span>';
                                                    else
                                                        echo '<span class="badge bg-warning text-dark">Proses</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php
                                                        $can_edit = (empty($case['pic_id']) || $case['pic_id'] == ($_SESSION['user_id'] ?? null) || strtolower($_SESSION['user_role'] ?? '') === 'admin');
                                                        $isDisabled = ($case['status'] === 'Verified' || !$can_edit) ? 'disabled' : '';
                                                        ?>

                                                        <?php if ($case['status'] === 'Open'): ?>
                                                            <button class="btn btn-info btn-view-detail text-white"
                                                                data-id="<?= $case['id'] ?>" data-type="apar"
                                                                data-status="<?= $case['status'] ?>"
                                                                title="Detail & Rencana Perbaikan"><i
                                                                    class="fas fa-eye"></i></button>
                                                        <?php elseif ($case['status'] === 'On Progress'): ?>
                                                            <button class="btn btn-warning btn-close-case text-dark border"
                                                                data-id="<?= $case['id'] ?>" data-type="apar"
                                                                data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>"
                                                                data-ng-count="<?= $case['ng_count'] ?? 1 ?>" <?= $isDisabled ?>
                                                                title="Selesaikan Perbaikan (<?= $case['ng_count'] ?? 1 ?> item NG)"><i
                                                                    class="fas fa-screwdriver"></i></button>
                                                        <?php elseif ($case['status'] === 'Revision'): ?>
                                                            <button class="btn btn-info btn-view-detail text-white"
                                                                data-id="<?= $case['id'] ?>" data-type="apar"
                                                                data-status="<?= $case['status'] ?>"
                                                                title="Detail - Perlu Perbaikan Ulang"><i
                                                                    class="fas fa-undo"></i></button>
                                                        <?php elseif ($case['status'] === 'Closed'): ?>
                                                            <?php if (strtolower($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                                                <button class="btn btn-success btn-verify-case"
                                                                    data-id="<?= $case['id'] ?>" data-type="apar"
                                                                    data-status="<?= $case['status'] ?>" title="Verifikasi Data"><i
                                                                        class="fas fa-check-double"></i></button>
                                                            <?php else: ?>
                                                                <button class="btn btn-info btn-view-detail text-white"
                                                                    data-id="<?= $case['id'] ?>" data-type="apar"
                                                                    data-status="<?= $case['status'] ?>" title="Detail & Status"><i
                                                                        class="fas fa-eye"></i></button>
                                                            <?php endif; ?>
                                                        <?php elseif ($case['status'] === 'Verified'): ?>
                                                            <span class="text-success small fw-bold"><i
                                                                    class="fas fa-check-circle"></i> Selesai</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- HYDRANT CASES TAB -->
                        <div class="tab-pane fade" id="hydrant-cases-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table id="hydrant-action-table"
                                    class="display table table-striped table-hover table-dashboard w-100">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Area</th>
                                            <th>Issue</th>
                                            <th>Countermeasure</th>
                                            <th>PIC</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hydrantAbnormalCases as $case): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($case['code']) ?></strong></td>
                                                <td><?= htmlspecialchars($case['area']) ?></td>
                                                <td class="text-danger fw-bold"
                                                    style="max-width:220px; white-space:normal;">
                                                    <?= htmlspecialchars($case['abnormal_case']) ?>
                                                    <?php if (($case['ng_count'] ?? 1) > 1): ?>
                                                        <span class="badge bg-danger d-block mt-1" style="font-size:0.6rem;">
                                                            <?= $case['ng_count'] ?> item NG
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($case['countermeasure'] ?: '-') ?></td>
                                                <td>
                                                    <?php if ($case['pic_name']): ?>
                                                        <span title="<?= htmlspecialchars($case['pic_name']) ?>"><img
                                                                src="storage/users/<?= htmlspecialchars($case['pic_photo'] ?: 'default.png') ?>"
                                                                class="avatar-mini me-1">
                                                            <?= htmlspecialchars($case['pic_name']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-danger"><i class="fas fa-user-times"></i>-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-' ?></td>
                                                <td>
                                                    <?php
                                                    if ($case['status'] === 'Open')
                                                        echo '<span class="badge bg-danger">Open</span>';
                                                    elseif ($case['status'] === 'Revision')
                                                        echo '<span class="badge bg-danger"><i class="fas fa-undo me-1"></i>Revisi</span>';
                                                    elseif ($case['status'] === 'Closed')
                                                        echo '<span class="badge bg-info">Closed</span>';
                                                    elseif ($case['status'] === 'Verified')
                                                        echo '<span class="badge bg-success">Verified</span>';
                                                    else
                                                        echo '<span class="badge bg-warning text-dark">Proses</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php
                                                        $can_edit = (empty($case['pic_id']) || $case['pic_id'] == ($_SESSION['user_id'] ?? null) || strtolower($_SESSION['user_role'] ?? '') === 'admin');
                                                        $isDisabled = ($case['status'] === 'Verified' || !$can_edit) ? 'disabled' : '';
                                                        ?>

                                                        <?php if ($case['status'] === 'Open'): ?>
                                                            <button class="btn btn-info btn-view-detail text-white"
                                                                data-id="<?= $case['id'] ?>" data-type="hydrant"
                                                                data-status="<?= $case['status'] ?>"
                                                                title="Detail & Rencana Perbaikan"><i
                                                                    class="fas fa-eye"></i></button>
                                                        <?php elseif ($case['status'] === 'On Progress'): ?>
                                                            <button class="btn btn-warning btn-close-case text-dark border"
                                                                data-id="<?= $case['id'] ?>" data-type="hydrant"
                                                                data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>"
                                                                data-ng-count="<?= $case['ng_count'] ?? 1 ?>" <?= $isDisabled ?>
                                                                title="Selesaikan Perbaikan (<?= $case['ng_count'] ?? 1 ?> item NG)"><i
                                                                    class="fas fa-tools"></i></button>
                                                        <?php elseif ($case['status'] === 'Revision'): ?>
                                                            <button class="btn btn-info btn-view-detail text-white"
                                                                data-id="<?= $case['id'] ?>" data-type="hydrant"
                                                                data-status="<?= $case['status'] ?>"
                                                                title="Detail - Perlu Perbaikan Ulang"><i
                                                                    class="fas fa-undo"></i></button>
                                                        <?php elseif ($case['status'] === 'Closed'): ?>
                                                            <?php if (strtolower($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                                                <button class="btn btn-success btn-verify-case"
                                                                    data-id="<?= $case['id'] ?>" data-type="hydrant"
                                                                    data-status="<?= $case['status'] ?>" title="Verifikasi Data"><i
                                                                        class="fas fa-check-double"></i></button>
                                                            <?php else: ?>
                                                                <button class="btn btn-info btn-view-detail text-white"
                                                                    data-id="<?= $case['id'] ?>" data-type="hydrant"
                                                                    data-status="<?= $case['status'] ?>" title="Detail & Status"><i
                                                                        class="fas fa-eye"></i></button>
                                                            <?php endif; ?>
                                                        <?php elseif ($case['status'] === 'Verified'): ?>
                                                            <span class="text-success small fw-bold"><i
                                                                    class="fas fa-check-circle"></i> Selesai</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
<!-- Modal Edit Case -->
<div class="modal fade" id="editCaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditCase">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Abnormal Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_detail">
                    <input type="hidden" name="id" id="edit_case_id">
                    <input type="hidden" name="type" id="edit_case_type">

                    <div class="mb-3">
                        <label class="form-label">Abnormal Case / Masalah</label>
                        <textarea class="form-control" name="abnormal_case" id="edit_abnormal_case" rows="2"
                            required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Countermeasure / Tindakan</label>
                        <textarea class="form-control" name="countermeasure" id="edit_countermeasure"
                            rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" id="edit_due_date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PIC (Person in Charge)</label>
                        <select class="form-select" name="pic_id" id="edit_pic_id">
                            <option value="">-- No PIC Assigned --</option>
                            <?php foreach ($usersPIC as $u): ?>
                                <option value="<?php echo htmlspecialchars($u['id']); ?>">
                                    <?php echo htmlspecialchars($u['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hanya Admin dan PIC terpilih yang bisa mengupdate kasus ini
                            kedepannya.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEdit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Status (Hanya untuk Selesaikan Perbaikan) -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formUpdateStatus" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Selesaikan Kasus Abnormal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" id="status_case_id">
                    <input type="hidden" name="type" id="status_case_type">
                    <input type="hidden" name="status" value="Closed">
                    <!-- Hardcoded because it's only called when closing -->
                    <input type="hidden" id="status_abnormal_text">

                    <div class="alert alert-info py-2" style="font-size:0.85rem;">
                        Pastikan Anda sudah memperbaiki masalah dan mencantumkan foto bukti perbaikan untuk dicek oleh
                        Admin K3.
                    </div>

                    <div id="repairPhotoDiv">
                        <!-- Diisi dinamis via AJAX: 1 file input per item NG yang On Progress -->
                        <div class="text-center text-muted py-3 small">
                            <i class="fas fa-spinner fa-spin me-1"></i>Memuat daftar item perbaikan...
                        </div>
                    </div>

                    <div id="newExpiredDiv" class="mb-3" style="display:none;">
                        <label class="form-label">New Expired Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="new_expired_date" id="new_expired_date">
                        <small class="text-muted">Kasus terkait expired, wajib input tanggal kedaluwarsa baru (akan
                            mengupdate data master unit).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSaveStatus"><i class="fas fa-check"></i> Simpan
                        & Tutup Kasus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Inspection Detail -->
<div class="modal fade" id="inspectionDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formStartProgress">
                <input type="hidden" name="action" value="start_progress">
                <input type="hidden" name="id" id="start_case_id">
                <input type="hidden" name="type" id="start_case_type">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Detail Inspeksi (Abnormal)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Premium Asset Info Header -->
                    <div class="mb-4 pb-3 border-bottom">
                        <div id="asset_info_pills" class="d-flex flex-wrap">
                            <!-- JS Will Populate Pills Here -->
                        </div>
                        <div class="mt-2 text-muted" style="font-size:0.75rem;">
                            <i class="fas fa-calendar-alt me-1"></i>Inspeksi: <span id="det_insp_date"
                                class="fw-bold">-</span>
                            <span class="mx-2">|</span>
                            <i class="fas fa-comment ms-1 me-1"></i>Catatan: <span id="det_insp_notes"
                                class="fw-bold">-</span>
                        </div>
                    </div>

                    <div class="section-premium-label">Item yang Abnormal (NG)</div>
                    <div id="detail_items_container" class="row g-3">
                        <!-- Ajax Populate -->
                    </div>

                    <!-- Input catatan revisi, muncul jika ada item yang dipilih -->
                    <div id="revisionNotesRow" class="mt-4 d-none p-3 border rounded border-danger bg-white shadow-sm">
                        <label class="form-label fw-bold text-danger small"><i class="fas fa-undo-alt me-1"></i>Catatan
                            Instruksi Revisi</label>
                        <textarea id="revisionNotesInput" class="form-control form-control-sm border-0 bg-light"
                            rows="2"
                            placeholder="Jelaskan apa yang perlu diperbaiki ulang secara spesifik..."></textarea>
                    </div>

                    <!-- NEW INPUT SECTION for Open Status (Clean Look) -->
                    <div id="startProgressInputSection" class="mt-5 pt-4 border-top" style="display: none;">
                        <div class="section-premium-label text-primary" style="border-left-color: #007bff;">Rencana
                            Tindakan Perbaikan</div>
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label fw-bold small text-muted">Tindakan Perbaikan
                                    (Countermeasure)</label>
                                <textarea class="form-control bg-light" name="countermeasure" id="input_countermeasure"
                                    rows="2" placeholder="Jelaskan tindakan yang akan dilakukan..." required></textarea>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-bold small text-muted">Tanggal Target Selesai (Due
                                    Date)</label>
                                <input type="date" class="form-control bg-light" name="due_date" id="input_due_date"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- REPAIR REPORT SECTION for Closed/Verified Status (Clean Look) -->
                    <div id="repairReportSection" class="mt-5 pt-4 border-top" style="display: none;">
                        <div class="section-premium-label text-success" style="border-left-color: #28a745;">Review
                            Laporan Perbaikan</div>
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded shadow-sm border">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <strong class="d-block mb-1 text-muted" style="font-size:0.7rem;">Tindakan
                                                yang Telah Dilakukan:</strong>
                                            <div id="det_countermeasure" class="fw-bold text-dark">—</div>
                                            <div class="mt-2">
                                                <span class="badge bg-white text-success border border-success small"
                                                    style="font-weight:600;">
                                                    <i class="fas fa-calendar-check me-1"></i>Selesai: <span
                                                        id="det_due_date">—</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="det_repair_photo_container" class="col-md-4 text-end"
                                            style="display:none;">
                                            <!-- Thumbnails here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <div>
                        <button type="submit" class="btn btn-primary fw-bold" id="btnMulaiTindakan" style="display: none;">Mulai Tindakan <i class="fas fa-arrow-right ms-1"></i></button>
                        <button type="button" class="btn btn-success fw-bold" id="btnVerifikasiModal" style="display: none;"><i class="fas fa-check-double ms-1"></i> Terima & Verifikasi Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Image Preview -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" id="previewImage" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // ===== DATE FILTER LOGIC (AFFECTS KPI, CHARTS, MAP - NOT ACTION REQUIRED) =====
        function applyDateFilter() {
            const startDate = $('#filterStartDate').val();
            const endDate = $('#filterEndDate').val();

            // Call backend to get filtered data
            $.ajax({
                url: 'actions/dashboard/ac_get_filtered_dashboard.php',
                type: 'GET',
                data: {
                    startDate: startDate,
                    endDate: endDate
                },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        console.error("Filter error:", response.error);
                        return;
                    }

                    // ===== UPDATE KPI CARDS =====
                    const totalApar = parseInt(response.apar.total) || 0;
                    const totalAparOK = parseInt(response.apar.ok) || 0;
                    const totalAparProses = parseInt(response.apar.proses) || 0;
                    const totalAparAbnormal = parseInt(response.apar.abnormal) || 0;

                    const totalHydrant = parseInt(response.hydrant.total) || 0;
                    const totalHydrantOK = parseInt(response.hydrant.ok) || 0;
                    const totalHydrantProses = parseInt(response.hydrant.proses) || 0;
                    const totalHydrantAbnormal = parseInt(response.hydrant.abnormal) || 0;

                    // Debug log
                    console.log('Filter Response:', { totalApar, totalAparOK, totalHydrant, totalHydrantOK });

                    // Update stats breakdown - use eq() for more specific targeting
                    $('.summary-card.total').find('h2').text(totalApar + totalHydrant);
                    $('.summary-card.total').find('.breakdown').html(
                        '<i class="fas fa-fire-extinguisher text-danger"></i> ' + totalApar +
                        ' | <i class="fas fa-water text-info"></i> ' + totalHydrant
                    );

                    $('.summary-card.ok').find('h2').text(totalAparOK + totalHydrantOK);
                    $('.summary-card.ok').find('.breakdown').html(
                        '<i class="fas fa-fire-extinguisher"></i> ' + totalAparOK +
                        ' | <i class="fas fa-water"></i> ' + totalHydrantOK
                    );

                    $('.summary-card.abn').find('h2').text(totalAparAbnormal + totalHydrantAbnormal);
                    $('.summary-card.abn').find('.breakdown').html(
                        '<i class="fas fa-fire-extinguisher border-end pe-1"></i> ' + totalAparAbnormal +
                        ' | <i class="fas fa-water"></i> ' + totalHydrantAbnormal
                    );

                    // ===== UPDATE PIE CHARTS =====
                    if (window.chartAPAR) {
                        window.chartAPAR.updateSeries([
                            totalAparProses,
                            totalAparOK,
                            totalAparAbnormal
                        ]);
                    }

                    if (window.chartHydrant) {
                        window.chartHydrant.updateSeries([
                            totalHydrantProses,
                            totalHydrantOK,
                            totalHydrantAbnormal
                        ]);
                    }

                    // ===== UPDATE MAP MARKERS =====
                    $('.map-container-custom .map-marker').remove();

                    response.markers.forEach(function (item) {
                        var markerClass = '';
                        if (item.status_badge === 'Proses') {
                            markerClass = 'marker-proses';
                        } else if (item.status_badge === 'OK') {
                            markerClass = 'marker-ok';
                        } else {
                            markerClass = 'marker-abnormal';
                        }

                        var markerHtml = '<div class="map-marker ' + markerClass + '" ' +
                            'style="left: ' + item.x_coordinate + '%; top: ' + item.y_coordinate + '%;" ' +
                            'data-kode="' + (item.kode || '-') + '" ' +
                            'data-status="' + item.status_badge + '" ' +
                            'data-jenis="' + (item.jenis || '-') + '" ' +
                            'data-area="' + (item.area || '-') + '" ' +
                            'data-keterangan="' + (item.issue || '') + '" ' +
                            'data-device-type="' + (item.device_type || '-') + '"' +
                            '>' + (item.kode || '') + '</div>';
                        $('.map-container-custom').append(markerHtml);
                    });

                    // Reattach tooltip events to new markers
                    attachMarkerTooltipEvents();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        function attachMarkerTooltipEvents() {
            $('.map-marker').off('mouseenter mouseleave mousemove').on('mouseenter', function (e) {
                var $tooltip = $('#marker-tooltip');
                $('#tt-kode').text($(this).data('kode'));
                $('#tt-jenis').text($(this).data('jenis'));
                $('#tt-area').text($(this).data('area'));
                $('#tt-keterangan').text($(this).data('keterangan') || '-');

                var status = $(this).data('status');
                var $statusBadge = $('#tt-status');
                $statusBadge.removeClass('bg-warning bg-success bg-danger text-dark');
                if (status === 'Proses') {
                    $statusBadge.addClass('bg-warning text-dark').text('Proses');
                } else if (status === 'OK') {
                    $statusBadge.addClass('bg-success').text('OK');
                } else {
                    $statusBadge.addClass('bg-danger').text('Abnormal');
                }
                $tooltip.show();
            }).on('mouseleave', function () {
                $('#marker-tooltip').hide();
            }).on('mousemove', function (e) {
                var $tooltip = $('#marker-tooltip');
                var containerOffset = $('.map-container-custom').offset();
                if (!containerOffset) return;

                var containerScrollTop = $('.map-container-custom').scrollTop() || 0;
                var containerScrollLeft = $('.map-container-custom').scrollLeft() || 0;

                var relX = e.pageX - containerOffset.left + containerScrollLeft;
                var relY = e.pageY - containerOffset.top + containerScrollTop;

                var tooltipWidth = $tooltip.outerWidth();
                var tooltipHeight = $tooltip.outerHeight();
                var containerWidth = $('.map-container-custom')[0].scrollWidth;
                var containerHeight = $('.map-container-custom')[0].scrollHeight;

                var leftPos = relX + 15;
                var topPos = relY + 15;

                if (leftPos + tooltipWidth > containerWidth) {
                    leftPos = relX - tooltipWidth - 15;
                }
                if (topPos + tooltipHeight > containerHeight) {
                    topPos = relY - tooltipHeight - 15;
                }

                $tooltip.css({
                    left: leftPos + 'px',
                    top: topPos + 'px'
                });
            });
        }

        $('#filterStartDate, #filterEndDate').on('change', function () {
            applyDateFilter();
        });

        $('#btnClearFilter').on('click', function () {
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            // Clear filter - reset to show all data
            applyDateFilter();
        });

        // ===== AUTO-LOAD DATA ON PAGE FIRST LOAD =====
        // Panggil applyDateFilter() saat page load untuk memastikan KPI cards ter-update dari backend
        applyDateFilter();

        // Edit Button Click (Using delegation for DataTables)
        $(document).on('click', '.btn-edit-case', function () {
            $('#edit_case_id').val($(this).data('id'));
            $('#edit_case_type').val($(this).data('type'));
            $('#edit_abnormal_case').val($(this).data('abcase'));
            $('#edit_countermeasure').val($(this).data('counter'));
            $('#edit_due_date').val($(this).data('due'));
            $('#edit_pic_id').val($(this).data('pic'));

            var modal = new bootstrap.Modal(document.getElementById('editCaseModal'));
            modal.show();
        });

        // View Detail Click OR Verify Click (Both open the modal, but verify prepares the accept button)
        $(document).on('click', '.btn-view-detail, .btn-verify-case', function (e) {
            e.preventDefault();
            var caseId = $(this).data('id');
            var caseType = $(this).data('type');
            var caseStatus = $(this).data('status');
            var isVerifying = $(this).hasClass('btn-verify-case');

            var $btn = $(this);
            var prevHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_inspection_detail',
                    id: caseId,
                    type: caseType
                },
                success: function (res) {
                    $btn.prop('disabled', false).html(prevHtml);

                    if (res.status === 'success') {
                        $('#det_insp_date').text(res.inspection_date);
                        $('#det_insp_notes').text(res.inspector_notes);

                        // Render Asset Info Pills
                        var pills = $('#asset_info_pills');
                        pills.empty().addClass('gap-2');
                        if (res.asset_info) {
                            pills.append('<div class="info-asset-pill shadow-sm"><i class="fas fa-tag"></i>' + res.asset_info.code + '</div>');
                            pills.append('<div class="info-asset-pill shadow-sm"><i class="fas fa-map-marker-alt"></i>' + res.asset_info.area + '</div>');
                            pills.append('<div class="info-asset-pill shadow-sm"><i class="fas fa-compass"></i>' + res.asset_info.location + '</div>');
                            pills.append('<div class="info-asset-pill shadow-sm"><i class="fas fa-fire-extinguisher"></i>' + res.asset_info.type + '</div>');
                        }

                        var container = $('#detail_items_container');
                        container.empty();

                        if (res.ng_items.length === 0) {
                            container.append('<div class="col-12 text-center text-muted py-5">Tidak ada data item perincian.</div>');
                        } else {
                            var isAdmin = <?= strtolower($_SESSION['user_role'] ?? '') === 'admin' ? 'true' : 'false' ?>;
                            var allClosed = res.ng_items.every(function (i) { return i.status === 'Closed'; });
                            window._selectedRevisi = {};

                            function updateRevisiUI() {
                                var count = Object.keys(window._selectedRevisi).length;
                                $('#revisiCountNum').text(count);
                                if (count > 0) {
                                    $('#btnRevisiCount').removeClass('d-none');
                                    $('#revisionNotesRow').removeClass('d-none');
                                    $('#btnVerifikasiLabel').text('Submit ' + count + ' Revisi').addClass('fw-bold');
                                    $('#btnVerifikasiModal').removeClass('btn-success').addClass('btn-primary shadow');
                                } else {
                                    $('#btnRevisiCount').addClass('d-none');
                                    $('#revisionNotesRow').addClass('d-none');
                                    $('#btnVerifikasiLabel').text('Terima & Verifikasi').removeClass('fw-bold');
                                    $('#btnVerifikasiModal').removeClass('btn-primary shadow').addClass('btn-success');
                                }
                            }

                            var statusMap = {
                                'Open': '<span class="badge bg-danger">Open</span>',
                                'On Progress': '<span class="badge bg-warning text-dark">On Progress</span>',
                                'Closed': '<span class="badge bg-info">Closed</span>',
                                'Verified': '<span class="badge bg-success">Verified</span>',
                                'Revision': '<span class="badge bg-danger"><i class="fas fa-undo me-1"></i>Revisi</span>'
                            };

                            res.ng_items.forEach(function (item) {
                                var canSelect = isAdmin && item.status === 'Closed' && allClosed;
                                var lid = String(item.line_id);

                                // Foto Temuan & Perbaikan Side-by-Side if both exist
                                var photoSection = '<div class="row g-2 mt-2">';

                                // Container finding photo
                                photoSection += '<div class="col-6 mb-1 text-start">' +
                                    '<label class="d-block mb-1 text-muted" style="font-size:0.55rem; font-weight:700; text-transform:uppercase;">Temuan:</label>';
                                if (item.photo) {
                                    photoSection += '<img src="' + item.photo + '" class="img-premium img-preview-btn" data-src="' + item.photo + '">';
                                } else {
                                    photoSection += '<div class="d-flex align-items-center justify-content-center bg-light rounded shadow-inner" style="height:90px; border:1px dashed #ddd;">' +
                                        '<span class="text-muted" style="font-size:0.6rem;">No Photo</span>' +
                                        '</div>';
                                }
                                photoSection += '</div>';

                                // Container repair photo
                                if (item.repair_photo) {
                                    photoSection += '<div class="col-6 mb-1 text-start">' +
                                        '<label class="d-block mb-1 text-success" style="font-size:0.55rem; font-weight:700; text-transform:uppercase;">Perbaikan:</label>' +
                                        '<img src="' + item.repair_photo + '" class="img-premium img-preview-btn" data-src="' + item.repair_photo + '" style="height:90px;">' +
                                        '</div>';
                                } else if (item.photo) {
                                    // if finding photo exists but repair not yet, fill with empty space to keep layout
                                    photoSection += '<div class="col-6 mb-1"></div>';
                                }

                                photoSection += '</div>';

                                var revisionAlert = '';
                                if (item.status === 'Revision' && item.revision_notes) {
                                    revisionAlert = '<div class="alert alert-danger py-1 px-2 mt-2 mb-0" style="font-size:0.7rem;text-align:left;">' +
                                        '<strong>Alasan Reol:</strong> ' + item.revision_notes + '</div>';
                                }

                                var $col = $('<div class="col-md-6 col-lg-4"></div>');
                                var $card = $('<div class="card ng-item-card p-3 h-100" data-line-id="' + item.line_id + '"></div>');

                                var checkboxHtml = canSelect ? '<input type="checkbox" class="ng-card-checkbox" id="chk_' + lid + '">' : '';

                                $card.html(
                                    checkboxHtml +
                                    '<div class="d-flex justify-content-between align-items-start mb-2">' +
                                    '<div class="fw-bold text-dark small text-start" style="line-height:1.2;">' + item.label + '</div>' +
                                    '<div class="badge-status-wrap">' + (statusMap[item.status] || '') + '</div>' +
                                    '</div>' +
                                    (item.keterangan ? '<div class="text-muted small mb-1 text-start" style="font-size:0.7rem;">' + item.keterangan + '</div>' : '') +
                                    revisionAlert +
                                    photoSection +
                                    (canSelect ? '<div class="mt-2 text-primary small text-start" style="font-size:0.6rem;"><i class="fas fa-check-square me-1"></i>Pilih untuk revisi</div>' : '')
                                );

                                if (canSelect) {
                                    $card.css('cursor', 'pointer');

                                    var toggleAction = function (e) {
                                        // Ignore click if it originated from the image preview button
                                        if ($(e.target).closest('.img-preview-btn').length > 0) return;

                                        if (window._selectedRevisi[lid]) {
                                            delete window._selectedRevisi[lid];
                                            $card.removeClass('selected-revisi');
                                            $card.find('.ng-card-checkbox').prop('checked', false);
                                            $card.find('.badge-status-wrap').html(statusMap[item.status]);
                                        } else {
                                            window._selectedRevisi[lid] = true;
                                            $card.addClass('selected-revisi');
                                            $card.find('.ng-card-checkbox').prop('checked', true);
                                            $card.find('.badge-status-wrap').html('<span class="badge bg-danger">REVISI</span>');
                                        }
                                        updateRevisiUI();
                                    };

                                    $card.on('click', toggleAction);
                                    $card.find('.ng-card-checkbox').on('click', function (e) {
                                        e.stopPropagation(); // prevent double toggle from label/card click
                                        toggleAction(e);
                                    });
                                }

                                $col.append($card);
                                container.append($col);
                            });

                            $('#inspectionDetailModal').off('hidden.bs.modal.revisi').on('hidden.bs.modal.revisi', function () {
                                window._selectedRevisi = {};
                                updateRevisiUI();
                                $('#revisionNotesInput').val('');
                            });
                        }

                        // Handle form visibility for Open cases
                        $('#start_case_id').val(caseId);
                        $('#start_case_type').val(caseType);

                        if (caseStatus === 'Open') {
                            $('#startProgressInputSection').show();
                            $('#btnMulaiTindakan').show();
                            $('#input_countermeasure').val('').prop('required', true);
                            $('#input_due_date').val('').prop('required', true);
                            $('#repairReportSection').hide();
                            $('#btnVerifikasiModal').hide();
                        } else {
                            $('#startProgressInputSection').hide();
                            $('#btnMulaiTindakan').hide();
                            $('#input_countermeasure').prop('required', false);
                            $('#input_due_date').prop('required', false);

                            if (res.case_info) {
                                $('#repairReportSection').show();
                                $('#det_countermeasure').text(res.case_info.countermeasure);
                                $('#det_due_date').text(res.case_info.due_date);

                                // Show all repair photos in a gallery in the report section
                                var repairGallery = $('#det_repair_photo_container');
                                repairGallery.empty().show();
                                res.ng_items.forEach(function (item) {
                                    if (item.repair_photo) {
                                        repairGallery.append('<img src="' + item.repair_photo + '" class="img-thumbnail me-1 img-preview-btn" style="width:50px;height:50px;object-fit:cover;cursor:pointer;" data-src="' + item.repair_photo + '">');
                                    }
                                });
                            } else {
                                $('#repairReportSection').hide();
                            }

                            var isAdminCheck = <?= strtolower($_SESSION['user_role'] ?? '') === 'admin' ? 'true' : 'false' ?>;
                            if (isAdminCheck && caseStatus === 'Closed') {
                                $('#btnVerifikasiModal').show().off('click.verify').on('click.verify', function () {
                                    triggerDecision(caseId, caseType);
                                });
                            } else {
                                $('#btnVerifikasiModal').hide();
                            }
                        }

                        var modal = new bootstrap.Modal(document.getElementById('inspectionDetailModal'));
                        modal.show();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).html(prevHtml);
                    Swal.fire('Error', 'Gagal memuat data inspeksi', 'error');
                }
            });
        });

        // Setup Image Preview Click
        $(document).on('click', '.img-preview-btn', function () {
            var src = $(this).data('src');
            $('#previewImage').attr('src', src);
            var prevModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            prevModal.show();
        });

        // Start Progress Submit from Modal
        $('#formStartProgress').on('submit', function (e) {
            e.preventDefault();
            $('#btnMulaiTindakan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {

                    if (res.status == 'success') {
                        Swal.fire('Sukses', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnMulaiTindakan').prop('disabled', false).html('Mulai Tindakan <i class="fas fa-arrow-right ms-1"></i>');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    $('#btnMulaiTindakan').prop('disabled', false).html('Mulai Tindakan <i class="fas fa-arrow-right ms-1"></i>');
                }
            });
        });

        // Close Case Click — load item NG secara dinamis per asset
        $(document).on('click', '.btn-close-case', function () {
            var assetId = $(this).data('id');
            var caseType = $(this).data('type');
            var ngCount = $(this).data('ng-count') || 1;

            $('#status_case_id').val(assetId);
            $('#status_case_type').val(caseType);
            $('#newExpiredDiv').hide();
            $('#new_expired_date').removeAttr('required');

            // Tampilkan loading spinner, lalu buka modal
            $('#repairPhotoDiv').html(
                '<div class="text-center text-muted py-3 small">' +
                '<i class="fas fa-spinner fa-spin me-1"></i>Memuat ' + ngCount + ' item perbaikan...</div>'
            );

            var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();

            // AJAX: ambil semua On Progress items untuk asset ini
            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'get_ng_items_for_close', id: assetId, type: caseType },
                success: function (res) {
                    if (res.status !== 'success' || !res.items || !res.items.length) {
                        $('#repairPhotoDiv').html(
                            '<div class="alert alert-warning py-2 small">Tidak ada item On Progress untuk unit ini.</div>'
                        );
                        return;
                    }

                    var html = '';
                    res.items.forEach(function (item) {
                        html += '<div class="card border mb-2">' +
                            '<div class="card-body p-2">' +
                            '<div class="fw-bold text-danger small mb-1">' +
                            '<i class="fas fa-exclamation-triangle me-1"></i>' + item.label +
                            (item.finding && item.finding !== item.label
                                ? ' <span class="text-dark fw-normal">— ' + item.finding + '</span>'
                                : '') +
                            '</div>' +
                            '<label class="form-label small mb-1">Foto Bukti Perbaikan <span class="text-danger">*</span></label>' +
                            '<input type="file" class="form-control form-control-sm" ' +
                            'name="repair_photo_' + item.line_id + '" accept="image/*" required>' +
                            '</div></div>';
                    });
                    $('#repairPhotoDiv').html(html);

                    // Toggle expired date input jika ada item terkait exp date
                    if (res.has_expired) {
                        $('#newExpiredDiv').show();
                        $('#new_expired_date').attr('required', true);
                    }
                },
                error: function () {
                    $('#repairPhotoDiv').html(
                        '<div class="alert alert-danger py-2 small">Gagal memuat data item perbaikan.</div>'
                    );
                }
            });
        });

        // Submit Edit Form
        $('#formEditCase').on('submit', function (e) {
            e.preventDefault();
            $('#btnSaveEdit').prop('disabled', true).text('Menyimpan...');
            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {

                    if (res.status == 'success') {
                        Swal.fire('Sukses', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnSaveEdit').prop('disabled', false).text('Simpan Perubahan');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    $('#btnSaveEdit').prop('disabled', false).text('Simpan Perubahan');
                }
            });
        });

        // Submit Status Form
        $('#formUpdateStatus').on('submit', function (e) {
            e.preventDefault();
            $('#btnSaveStatus').prop('disabled', true).text('Mengupdate...');

            var formData = new FormData(this);
            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                contentType: false,
                processData: false,
                success: function (res) {
                    if (res.status == 'success') {
                        Swal.fire('Sukses', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnSaveStatus').prop('disabled', false).text('Update Status');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    $('#btnSaveStatus').prop('disabled', false).text('Update Status');
                }
            });
        });

        // Function: Kirim keputusan Admin (Verified / Revision per-item)
        function triggerDecision(caseId, caseType) {
            var revisionIds = Object.keys(window._selectedRevisi || {});
            var revisionNotes = $('#revisionNotesInput').val().trim();
            var hasRevisi = revisionIds.length > 0;

            var confirmText = hasRevisi
                ? revisionIds.length + ' item akan ditandai REVISI, sisanya akan DIVERIFIKASI.'
                : 'Semua item perbaikan akan diverifikasi dan unit kembali ke status OK.';

            swal({
                title: hasRevisi ? 'Simpan Keputusan?' : 'Verifikasi Semua?',
                text: confirmText,
                icon: 'warning',
                buttons: {
                    cancel: {
                        visible: true,
                        text: 'Batal',
                        className: 'btn btn-secondary'
                    },
                    confirm: {
                        text: hasRevisi ? 'Ya, Simpan' : 'Ya, Verifikasi',
                        className: hasRevisi ? 'btn btn-primary' : 'btn btn-success'
                    }
                }
            }).then(function (result) {
                if (!result) return;

                $('#btnVerifikasiModal').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                $.ajax({
                    url: 'actions/dashboard/ac_abnormal.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'submit_decision',
                        id: caseId,
                        type: caseType,
                        revision_line_ids: JSON.stringify(revisionIds),
                        revision_notes: revisionNotes
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            swal('Berhasil', res.message, 'success').then(() => window.location.reload());
                        } else {
                            swal('Gagal', res.message, 'error');
                            $('#btnVerifikasiModal').prop('disabled', false).html('<i class="fas fa-check-double me-1"></i><span id="btnVerifikasiLabel">Terima &amp; Verifikasi</span>');
                        }
                    },
                    error: function () {
                        swal('Error', 'Terjadi kesalahan sistem', 'error');
                        $('#btnVerifikasiModal').prop('disabled', false).html('<i class="fas fa-check-double me-1"></i><span id="btnVerifikasiLabel">Terima &amp; Verifikasi</span>');
                    }
                });
            });
        }
    });
</script>

</div>

<!-- Javascript logic chart -->
<script>
    var totalAparProses = <?php echo $totalAparProses; ?>;
    var totalAparOK = <?php echo $totalAparOK; ?>;
    var totalAparAbnormal = <?php echo $totalAparAbnormal; ?>;

    var totalHydrantProses = <?php echo $totalHydrantProses; ?>;
    var totalHydrantOK = <?php echo $totalHydrantOK; ?>;
    var totalHydrantAbnormal = <?php echo $totalHydrantAbnormal; ?>;

    var chartStatuses = ['Proses', 'OK', 'Abnormal'];

    function fetchChartDetail(deviceType, statusIndex) {
        var statusLabel = (statusIndex === -1) ? 'Semua Status' : chartStatuses[statusIndex];
        var titleType = deviceType.toUpperCase();

        $('#mapSubtitle').text('Filter Aktif: ' + titleType + ' (' + statusLabel + ')');

        // Load data via AJAX
        $.ajax({
            url: 'actions/dashboard/ac_get_chart_detail.php',
            type: 'GET',
            data: { type: deviceType, status: statusLabel },
            dataType: 'json',
            success: function (response) {
                if (response.error) {
                    console.error("Error fetching data:", response.error);
                    return;
                }

                // Bersihkan marker map sebelumnya
                $('.map-container-custom .map-marker').remove();

                response.forEach(function (item) {
                    var statusBadge = '';
                    var markerClass = '';
                    if (item.status_badge === 'Proses') {
                        statusBadge = '<span class="badge bg-warning text-dark">Proses</span>';
                        markerClass = 'marker-proses';
                    }
                    else if (item.status_badge === 'OK') {
                        statusBadge = '<span class="badge bg-success">OK</span>';
                        markerClass = 'marker-ok';
                    }
                    else {
                        statusBadge = '<span class="badge bg-danger">Abnormal</span>';
                        markerClass = 'marker-abnormal';
                    }

                    // Tambahkan marker ke map jika ada koordinat
                    if (item.x_coordinate && item.y_coordinate) {
                        var shortCode = item.kode ? item.kode : '';
                        var markerHtml = '<div class="map-marker ' + markerClass + '" ' +
                            'style="left: ' + item.x_coordinate + '%; top: ' + item.y_coordinate + '%;" ' +
                            'data-kode="' + (item.kode || '-') + '" ' +
                            'data-status="' + item.status_badge + '" ' +
                            'data-jenis="' + (item.jenis || '-') + '" ' +
                            'data-lokasi="' + (item.lokasi || '-') + '" ' +
                            'data-area="' + (item.area || '-') + '" ' +
                            'data-keterangan="' + (item.keterangan || '-') + '" ' +
                            '>' + shortCode + '</div>';
                        $('.map-container-custom').append(markerHtml);
                    }
                });

                // Custom tooltip events
                $('.map-marker').on('mouseenter', function (e) {
                    // Logic is attached when elements are created
                    var $tooltip = $('#marker-tooltip');
                    $('#tt-kode').text($(this).data('kode'));
                    $('#tt-jenis').text($(this).data('jenis'));
                    $('#tt-lokasi').text($(this).data('lokasi'));
                    $('#tt-area').text($(this).data('area'));
                    $('#tt-keterangan').text($(this).data('keterangan'));

                    var status = $(this).data('status');
                    var $statusBadge = $('#tt-status');
                    $statusBadge.removeClass('bg-warning bg-success bg-danger text-dark');
                    if (status === 'Proses') {
                        $statusBadge.addClass('bg-warning text-dark').text('Proses');
                    } else if (status === 'OK') {
                        $statusBadge.addClass('bg-success').text('OK');
                    } else {
                        $statusBadge.addClass('bg-danger').text('Abnormal');
                    }
                    $tooltip.show();
                }).on('mouseleave', function () {
                    $('#marker-tooltip').hide();
                }).on('mousemove', function (e) {
                    var $tooltip = $('#marker-tooltip');
                    var containerOffset = $('.map-container-custom').offset();
                    if (!containerOffset) return;

                    var containerScrollTop = $('.map-container-custom').scrollTop() || 0;
                    var containerScrollLeft = $('.map-container-custom').scrollLeft() || 0;

                    var relX = e.pageX - containerOffset.left + containerScrollLeft;
                    var relY = e.pageY - containerOffset.top + containerScrollTop;

                    var tooltipWidth = $tooltip.outerWidth();
                    var tooltipHeight = $tooltip.outerHeight();
                    var containerWidth = $('.map-container-custom')[0].scrollWidth;
                    var containerHeight = $('.map-container-custom')[0].scrollHeight;

                    var leftPos = relX + 15;
                    var topPos = relY + 15;

                    if (leftPos + tooltipWidth > containerWidth) {
                        leftPos = relX - tooltipWidth - 15;
                    }
                    if (topPos + tooltipHeight > containerHeight) {
                        topPos = relY - tooltipHeight - 15;
                    }

                    $tooltip.css({
                        left: leftPos + 'px',
                        top: topPos + 'px'
                    });
                });

                // Jika filter di trigger dari klik (bukan auto load load), scroll ke map
                if (statusIndex !== -1) {
                    $('html, body').animate({
                        scrollTop: $(".map-container-custom").parent().offset().top - 80
                    }, 500);
                }

            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    var APAR_options = {
        series: [totalAparProses, totalAparOK, totalAparAbnormal],
        chart: {
            type: 'pie',
            height: 200,
            events: {
                dataPointSelection: function (event, chartContext, config) {
                    fetchChartDetail('apar', config.dataPointIndex);
                }
            }
        },
        labels: chartStatuses,
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'bottom', fontSize: '10px', offsetY: 0, marginTop: -10 },
        tooltip: { enabled: true },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '100%' } } },
        cursor: 'pointer'
    };

    window.chartAPAR = new ApexCharts(document.querySelector("#chartAPAR"), APAR_options);
    window.chartAPAR.render();

    var Hydrant_options = {
        series: [totalHydrantProses, totalHydrantOK, totalHydrantAbnormal],
        chart: {
            type: 'pie',
            height: 200,
            events: {
                dataPointSelection: function (event, chartContext, config) {
                    fetchChartDetail('hydrant', config.dataPointIndex);
                }
            }
        },
        labels: chartStatuses,
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'bottom', fontSize: '10px', offsetY: 0, marginTop: -10 },
        tooltip: { enabled: true },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: { size: '100%' } } },
        cursor: 'pointer'
    };

    window.chartHydrant = new ApexCharts(document.querySelector("#chartHydrant"), Hydrant_options);
    window.chartHydrant.render();
</script>

<!-- Javascript logic datatables -->
<script>
    $(document).ready(function () {
        var dtConfig = {
            paging: true,
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            searching: true,
            ordering: true,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Action column
            ]
        };

        // Initialize DataTables
        const aparActionTable = $("#apar-action-table").DataTable(dtConfig);
        const hydrantActionTable = $("#hydrant-action-table").DataTable(dtConfig);

        // Adjust columns on tab switch
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            aparActionTable.columns.adjust().draw();
            hydrantActionTable.columns.adjust().draw();
        });
    });
</script>