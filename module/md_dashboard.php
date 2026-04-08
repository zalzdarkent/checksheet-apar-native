<?php
include("actions/dashboard/ac_dashboard.php");

$usersPIC = [];
// Fetch from UserTable to allow any employee to be a PIC
$resUsers = sqlsrv_query($koneksi, "SELECT EMPID as id, EmployeeName as name FROM [apar].[Users].[UserTable] ORDER BY EmployeeName ASC");
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
        background-color: #f4f6f9; /* Subtle contrast */
    }

    /* Layout tweaks */
    .page-inner { padding: 1.5rem !important; }
    .card { margin-bottom: 1rem !important; } 
    
    /* Summary Cards compact */
    .summary-card {
        border-radius: 8px;
        background: #fff;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        border-left: 5px solid;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .summary-card.total { border-color: var(--c-total); }
    .summary-card.ok { border-color: var(--c-ok); }
    .summary-card.proses { border-color: var(--c-proses); }
    .summary-card.abn { border-color: var(--c-abnormal); }
    
    .summary-card h2 { margin: 0; font-size: 1.4rem; font-weight: 800; line-height: 1; }
    .summary-card span.stat-title { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; display: block; color: #555; margin-bottom: 1px;}
    .summary-card .breakdown { font-size: 0.65rem; color: #888; }
    
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
    
    .table-dashboard { font-size: 0.85rem; }
    .table-dashboard th { background: #f8f9fa; font-weight: 700; white-space: nowrap; padding: 10px 8px !important; }
    .table-dashboard td { vertical-align: middle; padding: 8px !important; }
    
    /* Responsive adjustments untuk mobile */
    @media (max-width: 768px) {
        .col-md-5, .col-md-7 { flex: 0 0 100% !important; max-width: 100% !important; }
        .map-container-custom { height: 300px; }
        #chartAPAR, #chartHydrant { height: 100px !important; }
    }
    
    .map-marker {
        position: absolute; width: 22px; height: 22px;
        border: 2px solid #fff; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 6px; font-weight: bold;
        transform: translate(-50%, -50%); cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3); z-index: 10; cursor: crosshair;
    }
    .map-marker:hover { transform: translate(-50%, -50%) scale(1.4); z-index: 20; }
    .marker-proses { background-color: var(--c-proses); color: #000; }
    .marker-ok { background-color: var(--c-ok); color: #fff; }
    .marker-abnormal { background-color: var(--c-abnormal); color: #fff; }

    /* Tooltip Map */
    .custom-map-tooltip {
        position: absolute; background: rgba(255, 255, 255, 0.98);
        border: 1px solid #ddd; border-radius: 6px; padding: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000;
        pointer-events: none; display: none; min-width: 220px;
        font-size: 11px; text-align: left;
    }
    .custom-map-tooltip table td { padding: 2px 4px 2px 0; }

    /* Compact List */
    .compact-list {
        height: 480px; overflow-y: auto; overflow-x: hidden;
        padding-right: 5px; background: #fff;
    }
    .compact-list::-webkit-scrollbar { width: 6px; }
    .compact-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

    .case-item {
        border: 1px solid #eee; border-radius: 6px; padding: 10px; margin-bottom: 8px;
        background: #fafafa; border-left: 3px solid var(--c-abnormal); position: relative;
    }
    .case-item.open { border-left-color: var(--c-abnormal); } /* Red - Open */
    .case-item.closed { border-left-color: #007bff; } /* Blue - Closed */
    .case-item.verified { border-left-color: var(--c-ok); opacity: 0.7; } /* Green - Verified */
    .case-item-title { font-size: 0.85rem; font-weight: 700; margin-bottom: 4px; display:flex; justify-content: space-between;}
    .case-item-desc { font-size: 0.75rem; color: #555; margin-bottom: 6px; line-height: 1.3;}
    .case-item-meta { font-size: 0.7rem; color: #888; display: flex; align-items: center; gap: 8px;}
    .case-item-actions { display: flex; gap: 4px; margin-top: 8px; }
    .case-item-actions .btn { font-size: 0.65rem; padding: 3px 6px; line-height: 1; }
    
    .avatar-mini { width: 18px; height: 18px; border-radius: 50%; object-fit: cover; }
    
    .nav-compact .nav-link { font-size:0.8rem; padding: 6px 12px; }
    
    .apx-title { font-size:0.8rem; font-weight:700; margin-top:0px; margin-bottom:8px; text-align:center;}
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
            <button id="btnClearFilter" class="btn btn-sm btn-light border border-secondary ms-2" style="font-size: 0.75rem; padding: 4px 10px;">
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
                    <div class="breakdown"><i class="fas fa-fire-extinguisher text-danger"></i> <?php echo $totalApar; ?> | <i class="fas fa-water text-info"></i> <?php echo $totalHydrant; ?></div>
                </div>
            </div>
        </div>
        <!-- OK -->
        <div class="col-6 col-md-4">
            <div class="summary-card ok">
                <div class="flex-grow-1">
                    <span class="stat-title text-success">Aman (OK)</span>
                    <h2 class="text-success"><?php echo ($totalAparOK + $totalHydrantOK); ?></h2>
                    <div class="breakdown"><i class="fas fa-fire-extinguisher"></i> <?php echo $totalAparOK; ?> | <i class="fas fa-water"></i> <?php echo $totalHydrantOK; ?></div>
                </div>
            </div>
        </div>
        <!-- ABNORMAL -->
        <div class="col-6 col-md-4">
            <div class="summary-card abn">
                <div class="flex-grow-1">
                    <span class="stat-title text-danger">Abnormal / Temuan</span>
                    <h2 class="text-danger"><?php echo ($totalAparAbnormal + $totalHydrantAbnormal); ?></h2>
                    <div class="breakdown"><i class="fas fa-fire-extinguisher border-end pe-1"></i> <?php echo $totalAparAbnormal; ?> | <i class="fas fa-water"></i> <?php echo $totalHydrantAbnormal; ?></div>
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
                <div class="card-header bg-white p-2 border-0 d-flex justify-content-between align-items-center" style="min-height: auto;">
                    <div class="fw-bold" style="font-size:0.85rem;"><i class="fas fa-map-marked-alt text-primary"></i> Live Factory Map</div>
                </div>
                <div class="card-body p-2 pt-0">
                    <div class="map-container-custom">
                        <img src="assets/img/ati-layout.jpeg" alt="Layout Map">
                        <!-- Tooltip -->
                        <div id="marker-tooltip" class="custom-map-tooltip">
                            <table class="table-borderless w-100">
                                <tbody>
                                    <tr><td style="width: 50px;" class="fw-bold">Kode</td><td id="tt-kode"></td></tr>
                                    <tr><td class="fw-bold">Status</td><td><span id="tt-status" class="badge"></span></td></tr>
                                    <tr><td class="fw-bold">Jenis</td><td id="tt-jenis"></td></tr>
                                    <tr><td class="fw-bold">Area</td><td id="tt-area"></td></tr>
                                    <tr><td class="fw-bold text-danger">Issue</td><td id="tt-keterangan" class="text-danger fw-bold"></td></tr>
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
                        <div class="fw-bold text-danger" style="font-size:1rem;"><i class="fas fa-exclamation-triangle"></i> Action Required / Temuan Abnormal</div>
                    </div>
                    <ul class="nav nav-tabs border-0" id="actionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-danger px-4" id="apar-tab-btn" data-bs-toggle="tab" data-bs-target="#apar-cases-tab" type="button" role="tab">APAR (<?= count($aparAbnormalCases) ?>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-info px-4" id="hydrant-tab-btn" data-bs-toggle="tab" data-bs-target="#hydrant-cases-tab" type="button" role="tab">Hydrant (<?= count($hydrantAbnormalCases) ?>)</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content">
                        
                        <!-- APAR CASES TAB -->
                        <div class="tab-pane fade show active" id="apar-cases-tab" role="tabpanel">
                            <div class="table-responsive">
                                <table id="apar-action-table" class="display table table-striped table-hover table-dashboard w-100">
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
                                            <td class="text-danger fw-bold"><?= htmlspecialchars($case['abnormal_case']) ?></td>
                                            <td><?= htmlspecialchars($case['countermeasure'] ?: '-') ?></td>
                                            <td>
                                                <?php if($case['pic_name']): ?>
                                                    <span title="<?= htmlspecialchars($case['pic_name']) ?>"><img src="storage/users/<?= htmlspecialchars($case['pic_photo'] ?: 'default.png') ?>" class="avatar-mini me-1"> <?= htmlspecialchars($case['pic_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-user-times"></i>-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-' ?></td>
                                            <td>
                                                <?php
                                                if ($case['status'] === 'Open') echo '<span class="badge bg-danger">Open</span>';
                                                elseif ($case['status'] === 'Closed') echo '<span class="badge bg-info">Closed</span>';
                                                elseif ($case['status'] === 'Verified') echo '<span class="badge bg-success">Verified</span>';
                                                else echo '<span class="badge bg-warning text-dark">Proses</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php 
                                                        $can_edit = (empty($case['pic_id']) || $case['pic_id'] == ($_SESSION['user_id'] ?? null) || strtolower($_SESSION['user_role'] ?? '') === 'admin');
                                                        $isDisabled = ($case['status'] === 'Verified' || !$can_edit) ? 'disabled' : '';
                                                    ?>
                                                    <button class="btn btn-warning btn-edit-case" data-id="<?= $case['id'] ?>" data-type="apar" data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>" data-counter="<?= htmlspecialchars($case['countermeasure'] ?? '') ?>" data-due="<?= $case['due_date'] ? $case['due_date']->format('Y-m-d') : '' ?>" data-pic="<?= $case['pic_id'] ?>" <?= $isDisabled ?>><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-primary btn-update-status" data-id="<?= $case['id'] ?>" data-type="apar" data-status="<?= $case['status'] ?>" data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>" <?= $isDisabled ?>><i class="fas fa-sync-alt"></i></button>
                                                    <?php if ($case['status'] === 'Closed' && strtolower($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                                        <button class="btn btn-success btn-verify-case" data-id="<?= $case['id'] ?>" data-type="apar"><i class="fas fa-check-double"></i></button>
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
                                <table id="hydrant-action-table" class="display table table-striped table-hover table-dashboard w-100">
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
                                            <td class="text-danger fw-bold"><?= htmlspecialchars($case['abnormal_case']) ?></td>
                                            <td><?= htmlspecialchars($case['countermeasure'] ?: '-') ?></td>
                                            <td>
                                                <?php if($case['pic_name']): ?>
                                                    <span title="<?= htmlspecialchars($case['pic_name']) ?>"><img src="storage/users/<?= htmlspecialchars($case['pic_photo'] ?: 'default.png') ?>" class="avatar-mini me-1"> <?= htmlspecialchars($case['pic_name']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-user-times"></i>-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-' ?></td>
                                            <td>
                                                <?php
                                                if ($case['status'] === 'Open') echo '<span class="badge bg-danger">Open</span>';
                                                elseif ($case['status'] === 'Closed') echo '<span class="badge bg-info">Closed</span>';
                                                elseif ($case['status'] === 'Verified') echo '<span class="badge bg-success">Verified</span>';
                                                else echo '<span class="badge bg-warning text-dark">Proses</span>';
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php 
                                                        $can_edit = (empty($case['pic_id']) || $case['pic_id'] == ($_SESSION['user_id'] ?? null) || strtolower($_SESSION['user_role'] ?? '') === 'admin');
                                                        $isDisabled = ($case['status'] === 'Verified' || !$can_edit) ? 'disabled' : '';
                                                    ?>
                                                    <button class="btn btn-warning btn-edit-case" data-id="<?= $case['id'] ?>" data-type="hydrant" data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>" data-counter="<?= htmlspecialchars($case['countermeasure'] ?? '') ?>" data-due="<?= $case['due_date'] ? $case['due_date']->format('Y-m-d') : '' ?>" data-pic="<?= $case['pic_id'] ?>" <?= $isDisabled ?>><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-primary btn-update-status" data-id="<?= $case['id'] ?>" data-type="hydrant" data-status="<?= $case['status'] ?>" data-abcase="<?= htmlspecialchars($case['abnormal_case']) ?>" <?= $isDisabled ?>><i class="fas fa-sync-alt"></i></button>
                                                    <?php if ($case['status'] === 'Closed' && strtolower($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                                        <button class="btn btn-success btn-verify-case" data-id="<?= $case['id'] ?>" data-type="hydrant"><i class="fas fa-check-double"></i></button>
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
                            <textarea class="form-control" name="abnormal_case" id="edit_abnormal_case" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Countermeasure / Tindakan</label>
                            <textarea class="form-control" name="countermeasure" id="edit_countermeasure" rows="2"></textarea>
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
                                    <option value="<?php echo htmlspecialchars($u['id']); ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hanya Admin dan PIC terpilih yang bisa mengupdate kasus ini kedepannya.</small>
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

    <!-- Modal Update Status -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUpdateStatus" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Update Status Kasus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" id="status_case_id">
                        <input type="hidden" name="type" id="status_case_type">
                        <input type="hidden" id="status_abnormal_text">
                        
                        <div class="mb-3">
                            <label class="form-label">Update Status</label>
                            <select class="form-select" name="status" id="status_select" required>
                                <option value="Open">Open</option>
                                <option value="On Progress">On Progress</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        
                        <div id="repairPhotoDiv" class="mb-3" style="display:none;">
                            <label class="form-label">Foto Bukti Perbaikan <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="repair_photo" id="repair_photo" accept="image/*">
                            <small class="text-muted">Wajib diupload saat menutup kasus.</small>
                        </div>

                        <div id="newExpiredDiv" class="mb-3" style="display:none;">
                            <label class="form-label">New Expired Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="new_expired_date" id="new_expired_date">
                            <small class="text-muted">Kasus terkait expired, wajib input tanggal kedaluwarsa baru (akan mengupdate data master unit).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveStatus">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
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
                success: function(response) {
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

                    response.markers.forEach(function(item) {
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
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        function attachMarkerTooltipEvents() {
            $('.map-marker').off('mouseenter mouseleave mousemove').on('mouseenter', function(e) {
                var $tooltip = $('#marker-tooltip');
                $('#tt-kode').text($(this).data('kode'));
                $('#tt-jenis').text($(this).data('jenis'));
                $('#tt-area').text($(this).data('area'));
                $('#tt-keterangan').text($(this).data('keterangan') || '-');

                var status = $(this).data('status');
                var $statusBadge = $('#tt-status');
                $statusBadge.removeClass('bg-warning bg-success bg-danger text-dark');
                if(status === 'Proses') {
                    $statusBadge.addClass('bg-warning text-dark').text('Proses');
                } else if(status === 'OK') {
                    $statusBadge.addClass('bg-success').text('OK');
                } else {
                    $statusBadge.addClass('bg-danger').text('Abnormal');
                }
                $tooltip.show();
            }).on('mouseleave', function() {
                $('#marker-tooltip').hide();
            }).on('mousemove', function(e) {
                var $tooltip = $('#marker-tooltip');
                var containerOffset = $('.map-container-custom').offset();
                if(!containerOffset) return;

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

                if(leftPos + tooltipWidth > containerWidth) {
                    leftPos = relX - tooltipWidth - 15;
                }
                if(topPos + tooltipHeight > containerHeight) {
                    topPos = relY - tooltipHeight - 15;
                }

                $tooltip.css({
                    left: leftPos + 'px',
                    top: topPos + 'px'
                });
            });
        }

        $('#filterStartDate, #filterEndDate').on('change', function() {
            applyDateFilter();
        });

        $('#btnClearFilter').on('click', function() {
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
            // Clear filter - reset to show all data
            applyDateFilter();
        });

        // ===== AUTO-LOAD DATA ON PAGE FIRST LOAD =====
        // Panggil applyDateFilter() saat page load untuk memastikan KPI cards ter-update dari backend
        applyDateFilter();

        // Edit Button Click (Using delegation for DataTables)
        $(document).on('click', '.btn-edit-case', function() {
            $('#edit_case_id').val($(this).data('id'));
            $('#edit_case_type').val($(this).data('type'));
            $('#edit_abnormal_case').val($(this).data('abcase'));
            $('#edit_countermeasure').val($(this).data('counter'));
            $('#edit_due_date').val($(this).data('due'));
            $('#edit_pic_id').val($(this).data('pic'));
            
            var modal = new bootstrap.Modal(document.getElementById('editCaseModal'));
            modal.show();
        });

        // Status Button Click (Using delegation for DataTables)
        $(document).on('click', '.btn-update-status', function() {
            var currStatus = $(this).data('status');
            var abcase = $(this).data('abcase');
            
            if(currStatus === 'Verified') {
                Swal.fire('Locked', 'Kasus yang sudah Verified tidak bisa diubah statusnya.', 'warning');
                return;
            }

            $('#status_case_id').val($(this).data('id'));
            $('#status_case_type').val($(this).data('type'));
            $('#status_select').val(currStatus);
            $('#status_abnormal_text').val(abcase);
            
            $('#status_select').trigger('change');
            
            var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        });

        // Logic dynamic req field depending on status
        $('#status_select').on('change', function() {
            var st = $(this).val();
            var abcase = $('#status_abnormal_text').val() || '';
            
            if (st === 'Closed') {
                $('#repairPhotoDiv').show();
                $('#repair_photo').attr('required', true);
                
                if (abcase.toUpperCase().indexOf('EXPIRED') !== -1) {
                    $('#newExpiredDiv').show();
                    $('#new_expired_date').attr('required', true);
                } else {
                    $('#newExpiredDiv').hide();
                    $('#new_expired_date').removeAttr('required');
                }
            } else {
                $('#repairPhotoDiv').hide();
                $('#repair_photo').removeAttr('required');
                
                $('#newExpiredDiv').hide();
                $('#new_expired_date').removeAttr('required');
            }
        });

        // Submit Edit Form
        $('#formEditCase').on('submit', function(e) {
            e.preventDefault();
            $('#btnSaveEdit').prop('disabled', true).text('Menyimpan...');
            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(resp) {
                    var res = JSON.parse(resp);
                    if(res.status == 'success') {
                        Swal.fire('Sukses', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnSaveEdit').prop('disabled', false).text('Simpan Perubahan');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    $('#btnSaveEdit').prop('disabled', false).text('Simpan Perubahan');
                }
            });
        });

        // Submit Status Form
        $('#formUpdateStatus').on('submit', function(e) {
            e.preventDefault();
            $('#btnSaveStatus').prop('disabled', true).text('Mengupdate...');
            
            var formData = new FormData(this);
            $.ajax({
                url: 'actions/dashboard/ac_abnormal.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    var res = JSON.parse(resp);
                    if(res.status == 'success') {
                        Swal.fire('Sukses', res.message, 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        $('#btnSaveStatus').prop('disabled', false).text('Update Status');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    $('#btnSaveStatus').prop('disabled', false).text('Update Status');
                }
            });
        });

        // Verify Button (Using delegation for DataTables)
        $(document).on('click', '.btn-verify-case', function() {
            var caseId = $(this).data('id');
            var caseType = $(this).data('type');
            
            Swal.fire({
                title: 'Konfirmasi Verifikasi',
                text: 'Apakah Anda yakin ingin memverifikasi kasus ini? Setelah diverifikasi, data tidak bisa diubah lagi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Verifikasi!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'actions/dashboard/ac_abnormal.php',
                        type: 'POST',
                        data: {
                            action: 'verify_case',
                            id: caseId,
                            type: caseType
                        },
                        success: function(resp) {
                            var res = JSON.parse(resp);
                            if(res.status == 'success') {
                                Swal.fire('Terverifikasi!', res.message, 'success').then(() => window.location.reload());
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
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
            success: function(response) {
                if (response.error) {
                    console.error("Error fetching data:", response.error);
                    return;
                }
                
                // Bersihkan marker map sebelumnya
                $('.map-container-custom .map-marker').remove();
                
                response.forEach(function(item) {
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
                $('.map-marker').on('mouseenter', function(e) {
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
                    if(status === 'Proses') {
                        $statusBadge.addClass('bg-warning text-dark').text('Proses');
                    } else if(status === 'OK') {
                        $statusBadge.addClass('bg-success').text('OK');
                    } else {
                        $statusBadge.addClass('bg-danger').text('Abnormal');
                    }
                    $tooltip.show();
                }).on('mouseleave', function() {
                    $('#marker-tooltip').hide();
                }).on('mousemove', function(e) {
                    var $tooltip = $('#marker-tooltip');
                    var containerOffset = $('.map-container-custom').offset();
                    if(!containerOffset) return;
                    
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
                    
                    if(leftPos + tooltipWidth > containerWidth) {
                        leftPos = relX - tooltipWidth - 15;
                    }
                    if(topPos + tooltipHeight > containerHeight) {
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
            error: function(xhr, status, error) {
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
                dataPointSelection: function(event, chartContext, config) {
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
                dataPointSelection: function(event, chartContext, config) {
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