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

    /* Custom Slim Datatable */
    table.dataTable.table-slim > thead > tr > th,
    table.dataTable.table-slim > tbody > tr > td {
        padding: 6px 10px !important;
        font-size: 13px !important;
        vertical-align: middle !important;
    }

    table.dataTable.table-slim > thead > tr > th {
        padding-right: 25px !important; /* Room for sort arrows */
    }

    table.dataTable.table-slim > thead > tr > th::before,
    table.dataTable.table-slim > thead > tr > th::after {
        bottom: 6px !important; /* Adjust arrow vertical position */
        right: 5px !important; /* Adjust arrow horizontal position */
    }
    table.dataTable.table-slim .btn {
        padding: 3px 8px !important;
        font-size: 12px !important;
        line-height: 1.5;
    }
    table.dataTable.table-slim .badge {
        font-size: 11px !important;
        padding: 4px 6px !important;
        font-weight: 500;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 13px !important;
        margin-bottom: 8px;
    }
    table.dataTable.table-slim th {
        background-color: #f8f9fa !important;
    }

    /* Modal List & Map View */
    .map-container-custom {
        position: relative;
        width: 100%;
        overflow: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 400px;
        background: #f8f9fa;
        text-align: center;
    }
    .map-container-custom img {
        width: 100%;
        height: auto;
        display: block;
    }
    .map-marker {
        position: absolute;
        width: 28px;
        height: 28px;
        border: 1px solid #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 7px;
        font-weight: bold;
        transform: translate(-50%, -50%);
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        z-index: 10;
        text-align: center;
        line-height: 1.1;
    }
    .map-marker:hover {
        transform: translate(-50%, -50%) scale(1.2);
        z-index: 20;
    }
    .marker-proses { background-color: #ffc107; color: #000; }
    .marker-ok { background-color: #28a745; color: #fff; }
    .marker-abnormal { background-color: #dc3545; color: #fff; }
    .map-hover-text {
        color: #495057;
        font-size: 14px;
        text-align: left;
    }
    .custom-map-tooltip {
        position: absolute;
        background: rgba(240, 252, 252, 0.95);
        border: 1px solid #e0f2f1;
        border-radius: 8px;
        padding: 12px 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        pointer-events: none;
        display: none;
        min-width: 250px;
        font-size: 13px;
        color: #333;
        text-align: left;
    }
    .custom-map-tooltip table { margin-bottom: 0; }
    .custom-map-tooltip table td { padding: 4px 0; vertical-align: top; }
    #modal-list-table_wrapper {
        padding: 10px 0;
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
                        <table id="table-apar" class="display table table-striped table-hover table-slim">
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Hydrant Tab -->
                <div class="tab-pane fade" id="hydrant-content" role="tabpanel" aria-labelledby="hydrant-tab">
                    <div class="table-responsive">
                        <table id="table-hydrant" class="display table table-striped table-hover table-slim">
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
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Detail Modal -->
    <div class="modal fade" id="chartDetailModal" tabindex="-1" aria-labelledby="chartDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="chartDetailModalLabel">Detail APAR – Proses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav tabs -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="nav btn-group" id="detailTabs" role="tablist">
                            <button class="btn btn-outline-primary active btn-sm" id="map-view-tab" data-bs-toggle="tab" data-bs-target="#map-view" type="button" role="tab" aria-controls="map-view" aria-selected="true" style="padding: 5px 15px; font-size: 13px;">
                                <i class="fas fa-map-marked-alt me-1"></i> Map View
                            </button>
                            <button class="btn btn-outline-primary btn-sm" id="list-view-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button" role="tab" aria-controls="list-view" aria-selected="false" style="padding: 5px 15px; font-size: 13px;">
                                <i class="fas fa-list me-1"></i> List View
                            </button>
                        </div>
                    </div>

                    <!-- Tab panes -->
                    <div class="tab-content border-top pt-3" id="detailTabsContent">
                        <div class="tab-pane fade show active" id="map-view" role="tabpanel" aria-labelledby="map-view-tab">
                            <div class="map-hover-text mb-2 text-primary fw-bold"><i class="fas fa-info-circle me-1"></i> Sentuh/Hover marker untuk detail</div>
                            <div class="map-container-custom position-relative">
                                <img src="assets/img/ati-layout.jpeg" alt="Layout Map">
                                <!-- Marker tooltip container -->
                                <div id="marker-tooltip" class="custom-map-tooltip">
                                    <h5 id="tt-kode" class="fw-bold mb-2 pb-2 border-bottom" style="color: #0dcaf0;"></h5>
                                    <table class="w-100 table-borderless m-0">
                                        <tr><td class="fw-bold text-secondary" style="width: 85px;">Status:</td><td><span id="tt-status" class="badge"></span></td></tr>
                                        <tr><td class="fw-bold text-secondary">Jenis:</td><td id="tt-jenis"></td></tr>
                                        <tr><td class="fw-bold text-secondary">Lokasi:</td><td id="tt-lokasi"></td></tr>
                                        <tr><td class="fw-bold text-secondary">Area:</td><td id="tt-area"></td></tr>
                                        <tr><td class="fw-bold text-secondary">Keterangan:</td><td id="tt-keterangan"></td></tr>
                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-primary me-2" onclick="window.open('assets/img/ati-layout.jpeg', '_blank')">
                                    <i class="fas fa-map"></i> Buka Map Penuh
                                </button>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="list-view" role="tabpanel" aria-labelledby="list-view-tab">
                            <div class="table-responsive">
                                <table id="modal-list-table" class="display table table-striped table-hover table-slim w-100">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Jenis</th>
                                            <th>Lokasi</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                            <th>Keterangan</th>
                                            <th>Foto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Chart Detail Modal -->

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
        var statusLabel = chartStatuses[statusIndex];
        var titleType = deviceType.toUpperCase();
        
        $('#chartDetailModalLabel').text('Detail ' + titleType + ' \u2013 ' + statusLabel);
        
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
                
                var modalTable = $('#modal-list-table').DataTable();
                modalTable.clear();
                
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
                    
                    var photoHtml = '-';
                    if (item.foto) {
                        photoHtml = '<a href="storage/' + item.foto + '" target="_blank" class="btn btn-sm btn-info" title="View Foto"><i class="fas fa-image"></i></a>';
                    }
                    
                    modalTable.row.add([
                        item.kode || '-',
                        item.jenis || '-',
                        item.lokasi || '-',
                        item.area || '-',
                        statusBadge,
                        item.keterangan || '-',
                        photoHtml
                    ]);

                    // Tambahkan marker ke map jika ada koordinat
                    if (item.x_coordinate && item.y_coordinate) {
                        var shortCode = item.kode ? item.kode : '';
                        var rawStatus = item.status_badge;
                        var markerHtml = '<div class="map-marker ' + markerClass + '" ' +
                            'style="left: ' + item.x_coordinate + '%; top: ' + item.y_coordinate + '%;" ' +
                            'data-kode="' + (item.kode || '-') + '" ' +
                            'data-status="' + rawStatus + '" ' +
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
                    var $tooltip = $('#marker-tooltip');
                    var kode = $(this).data('kode');
                    var status = $(this).data('status');
                    var jenis = $(this).data('jenis');
                    var lokasi = $(this).data('lokasi');
                    var area = $(this).data('area');
                    var keterangan = $(this).data('keterangan');
                    
                    $('#tt-kode').text(kode);
                    $('#tt-jenis').text(jenis);
                    $('#tt-lokasi').text(lokasi);
                    $('#tt-area').text(area);
                    $('#tt-keterangan').text(keterangan);
                    
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
                    $(this).trigger('mousemove', e);
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

                modalTable.draw();
                
                var myModal = new bootstrap.Modal(document.getElementById('chartDetailModal'), {
                    keyboard: false
                });
                myModal.show();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    var APAR_options = {
        series: [totalAparProses, totalAparOK, totalAparAbnormal],
        chart: {
            width: '100%',
            type: 'pie',
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    fetchChartDetail('apar', config.dataPointIndex);
                }
            }
        },
        labels: chartStatuses,
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'top' },
        cursor: 'pointer'
    };

    var chartAPAR = new ApexCharts(document.querySelector("#chartAPAR"), APAR_options);
    chartAPAR.render();

    var Hydrant_options = {
        series: [totalHydrantProses, totalHydrantOK, totalHydrantAbnormal],
        chart: {
            width: '100%',
            type: 'pie',
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    fetchChartDetail('hydrant', config.dataPointIndex);
                }
            }
        },
        labels: chartStatuses,
        colors: ['#FFC107', '#28A745', '#DC3545'],
        legend: { position: 'top' },
        cursor: 'pointer'
    };

    var chartHydrant = new ApexCharts(document.querySelector("#chartHydrant"), Hydrant_options);
    chartHydrant.render();
</script>

<!-- Javascript logic datatables -->
<script>
    $(document).ready(function () {
        var aparTable, hydrantTable;
        var hydrantTableInitialized = false;

        // Configuration for both tables
        var dtConfig = {
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            retrieve: true, // Safety against re-initialization
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on Action column
            ],
            language: {
                emptyTable: "No abnormal cases"
            }
        };

        // Initialize APAR table immediately
        aparTable = $("#table-apar").DataTable(dtConfig);

        // Initialize Hydrant table when tab is shown
        $('#hydrant-tab').on('shown.bs.tab', function (e) {
            if (!hydrantTableInitialized) {
                hydrantTable = $("#table-hydrant").DataTable(dtConfig);
                hydrantTableInitialized = true;
            } else if (hydrantTable) {
                hydrantTable.columns.adjust().draw();
            }
        });

        // Initialize Modal table
        var modalDtConfig = {
            paging: true,
            searching: true,
            ordering: true,
            responsive: true,
            retrieve: true,
            language: {
                emptyTable: "Data tidak ditemukan"
            }
        };
        $("#modal-list-table").DataTable(modalDtConfig);

        // Ensure columns adjust when tabs are switched
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            var targetId = $(e.target).attr('data-bs-target');
            var tableId = '';
            
            if (targetId === '#apar-content') tableId = '#table-apar';
            else if (targetId === '#hydrant-content') tableId = '#table-hydrant';
            else if (targetId === '#list-view') tableId = '#modal-list-table';

            if (tableId) {
                var table = $(tableId).DataTable();
                if (table) {
                    setTimeout(function() {
                        table.columns.adjust().draw();
                    }, 100);
                }
            }
        });
    });
</script>