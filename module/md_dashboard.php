<?php
include("actions/dashboard/ac_dashboard.php");

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
    .status-card-custom {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        padding: 15px 5px;
        text-align: center;
        border-left: 3px solid #ccc;
    }

    .border-left-primary {
        border-left-color: #007bff !important;
    }

    .border-left-warning {
        border-left-color: #ffc107 !important;
    }

    .border-left-success {
        border-left-color: #28a745 !important;
    }

    .border-left-danger {
        border-left-color: #dc3545 !important;
    }

    .status-card-custom h3 {
        margin: 0;
        font-size: 22px;
        font-weight: bold;
        color: #000;
    }

    .status-card-custom p {
        margin: 0;
        font-size: 11px;
        color: #000;
    }
</style>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Dashboard</h3>
            <h6 class="op-7 mb-2">Sistem Monitoring APAR & Hydrant</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <?php
            $formatter = new IntlDateFormatter(
                'id_ID',
                IntlDateFormatter::FULL,
                IntlDateFormatter::NONE
            );
            echo '<i class="fas fa-calendar-alt"></i> ' . $formatter->format(new DateTime());
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-round h-100 shadow-sm">
                <div class="card-header border-0 bg-transparent pt-3 pb-0">
                    <h4 class="card-title fw-bold mb-0" style="font-size: 1.1rem;">
                        <i class="fas fa-fire-extinguisher text-danger me-2"></i>Monitoring APAR
                    </h4>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-2 text-center">
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-primary border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalApar; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalAparProses; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Proses</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-success border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalAparOK; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">OK</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalAparAbnormal; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Abnormal</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card card-round h-100 shadow-sm">
                <div class="card-header border-0 bg-transparent pt-3 pb-0">
                    <h4 class="card-title fw-bold mb-0" style="font-size: 1.1rem;">
                        Monitoring Hydrant
                    </h4>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-2 text-center">
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-info border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalHydrant; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-warning border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalHydrantProses; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Proses</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-success border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalHydrantOK; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">OK</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="card shadow-sm border-0 border-start border-danger border-4 h-100 py-2 mb-0">
                                <h4 class="fw-bold mb-0 text-dark">
                                    <?php echo $totalHydrantAbnormal; ?>
                                </h4>
                                <small class="text-uppercase text-dark" style="font-size: 0.65rem;">Abnormal</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart APAR & Hydrant -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-white border-0 pb-0 pt-4">
                    <div class="card-title fw-bold" style="font-size: 1.1rem;"><i
                            class="fas fa-fire-extinguisher text-danger me-2"></i> Monitoring APAR</div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mt-4">
                        <div id="chartAPAR" style="width: 100%; max-width: 320px; min-height: 250px;"></div>
                    </div>

                    <a href="#" class="btn btn-primary btn-round w-100">View Map</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-round shadow-sm">
                <div class="card-header bg-white border-0 pb-0 pt-4">
                    <div class="card-title fw-bold" style="font-size: 1.1rem;"> Monitoring Hydrant</div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center mt-4">
                        <div id="chartHydrant" style="width: 100%; max-width: 320px; min-height: 250px;"></div>
                    </div>

                    <a href="#" class="btn btn-primary btn-round w-100">View Map</a>
                </div>
            </div>
        </div>
    </div>
    <!-- End Chart APAR & Hydrant -->

    <!-- Monitoring Data Tabs -->
    <div class="card">
        <div class="card-header pb-0 border-0">
            <ul class="nav nav-tabs" role="tablist" id="dataTab">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="apar-tab" data-bs-toggle="tab" data-bs-target="#apar-content"
                        type="button" role="tab" aria-controls="apar-content" aria-selected="true">
                        <i class="fas fa-fire-extinguisher text-danger me-2"></i>APAR
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="hydrant-tab" data-bs-toggle="tab" data-bs-target="#hydrant-content"
                        type="button" role="tab" aria-controls="hydrant-content" aria-selected="false">
                        <i class="fas fa-water text-info me-2"></i>Hydrant
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="dataTabContent">
                <!-- APAR Tab -->
                <div class="tab-pane fade show active" id="apar-content" role="tabpanel" aria-labelledby="apar-tab">
                    <div class="table-responsive">
                        <table id="table-apar" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Area</th>
                                    <th>Location</th>
                                    <th>Code</th>
                                    <th>Abnormal Case</th>
                                    <th>Countermeasure</th>
                                    <th>Due Date</th>
                                    <th>PIC</th>
                                    <th>Status</th>
                                    <th>Foto Repair</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($aparAbnormalCases)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($aparAbnormalCases as $case): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($case['area'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['location'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['code'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['abnormal_case'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['countermeasure'] ?? '-'); ?></td>
                                            <td><?php echo $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($case['pic_name'] ?? '-'); ?></td>
                                            <td>
                                                <?php
                                                $status = $case['status'];
                                                if ($status === 'Open') {
                                                    echo '<span class="badge bg-danger">Open</span>';
                                                } elseif ($status === 'Closed') {
                                                    echo '<span class="badge bg-info">Closed</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning">' . htmlspecialchars($status) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($case['repair_photo'] ?? null): ?>
                                                    <a href="storage/<?php echo htmlspecialchars($case['repair_photo']); ?>" target="_blank" class="btn btn-sm btn-info" title="View Photo">
                                                        <i class="fas fa-image"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-success" title="Mark Verified">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">No abnormal cases</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hydrant Tab -->
                <div class="tab-pane fade" id="hydrant-content" role="tabpanel" aria-labelledby="hydrant-tab">
                    <div class="table-responsive">
                        <table id="table-hydrant" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Area</th>
                                    <th>Location</th>
                                    <th>Code</th>
                                    <th>Abnormal Case</th>
                                    <th>Countermeasure</th>
                                    <th>Due Date</th>
                                    <th>PIC</th>
                                    <th>Status</th>
                                    <th>Foto Repair</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($hydrantAbnormalCases)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($hydrantAbnormalCases as $case): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($case['area'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['location'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['code'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['abnormal_case'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($case['countermeasure'] ?? '-'); ?></td>
                                            <td><?php echo $case['due_date'] ? $case['due_date']->format('d/m/Y') : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($case['pic_name'] ?? '-'); ?></td>
                                            <td>
                                                <?php
                                                $status = $case['status'];
                                                if ($status === 'Open') {
                                                    echo '<span class="badge bg-danger">Open</span>';
                                                } elseif ($status === 'Closed') {
                                                    echo '<span class="badge bg-info">Closed</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning">' . htmlspecialchars($status) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($case['repair_photo'] ?? null): ?>
                                                    <a href="storage/<?php echo htmlspecialchars($case['repair_photo']); ?>" target="_blank" class="btn btn-sm btn-info" title="View Photo">
                                                        <i class="fas fa-image"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-success" title="Mark Verified">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">No abnormal cases</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Javascript -->
<script src="assets/js/core/jquery-3.7.1.min.js"></script>
<script src="assets/js/apexchart.js"></script>
<script src="assets/js/plugin/datatables/datatables.min.js"></script>
<!-- apar dan hydrant -->
<script>
    var totalAparProses = <?php echo $totalAparProses; ?>;
    var totalAparOK = <?php echo $totalAparOK; ?>;
    var totalAparAbnormal = <?php echo $totalAparAbnormal; ?>;

    var totalHydrantProses = <?php echo $totalHydrantProses; ?>;
    var totalHydrantOK = <?php echo $totalHydrantOK; ?>;
    var totalHydrantAbnormal = <?php echo $totalHydrantAbnormal; ?>;

    var APAR_options = {
        series: [totalAparProses, totalAparOK, totalAparAbnormal],
        chart: {
            width: '100%',
            type: 'pie',
        },
        labels: ['Proses', 'OK', 'Abnormal'],
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'top' }
    };

    var chartAPAR = new ApexCharts(document.querySelector("#chartAPAR"), APAR_options);
    chartAPAR.render();

    var Hydrant_options = {
        series: [totalHydrantProses, totalHydrantOK, totalHydrantAbnormal],
        chart: {
            width: '100%',
            type: 'pie',
        },
        labels: ['Proses', 'OK', 'Abnormal'],
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'top' }
    };

    var chartHydrant = new ApexCharts(document.querySelector("#chartHydrant"), Hydrant_options);
    chartHydrant.render();
</script>

<!-- datatables -->
<script>
    $(document).ready(function () {
        // Initialize APAR table
        var aparTable = $("#table-apar").DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true
        });

        // Initialize Hydrant table
        var hydrantTable = $("#table-hydrant").DataTable({
            paging: true,
            searching: true,
            ordering: true,
            responsive: true
        });

        // Re-initialize DataTable when tab changes for proper column width adjustment
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("data-bs-target");
            if (target === '#apar-content') {
                aparTable.columns.adjust().draw();
            } else if (target === '#hydrant-content') {
                hydrantTable.columns.adjust().draw();
            }
        });
    });
</script>