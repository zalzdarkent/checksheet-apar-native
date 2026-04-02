<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

// Get month filter
$month = $_GET['month'] ?? 'all';
$dateFilter = "";
$monthTitle = "All Time";

if ($month !== 'all') {
    $monthNum = intval($month);
    $year = date('Y');
    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $monthTitle = ($monthNum >= 1 && $monthNum <= 12) ? $monthNames[$monthNum - 1] . " " . $year : "All Time";
    $dateFilter = "AND MONTH(bi.inspection_date) = $monthNum AND YEAR(bi.inspection_date) = $year";
}

// Initialize KPI values
$total_apar = 0;
$total_hydrant = 0;
$apar_abnormal = 0;
$hydrant_abnormal = 0;

// Load Total APAR count
$stmt = sqlsrv_query($koneksi, "SELECT COUNT(*) as total FROM [apar].[dbo].[apars] WHERE is_active = 1");
if ($stmt !== false && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $total_apar = $row['total'] ?? 0;
}

// Load Total Hydrant count
$stmt = sqlsrv_query($koneksi, "SELECT COUNT(*) as total FROM [apar].[dbo].[hydrants] WHERE is_active = 1");
if ($stmt !== false && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $total_hydrant = $row['total'] ?? 0;
}

// Load APAR Abnormal count
$query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_apar_inspections]
          WHERE 1=1 $dateFilter
          AND (exp_date_ok != 1 OR pressure_ok != 1 OR weight_co2_ok != 1 
               OR tube_ok != 1 OR hose_ok != 1 OR bracket_ok != 1 
               OR wi_ok != 1 OR form_kejadian_ok != 1 OR sign_box_ok != 1 
               OR sign_triangle_ok != 1 OR marking_tiger_ok != 1 OR marking_beam_ok != 1 
               OR sr_apar_ok != 1 OR kocok_apar_ok != 1 OR label_ok != 1)";
$stmt = sqlsrv_query($koneksi, $query);
if ($stmt !== false && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $apar_abnormal = $row['total'] ?? 0;
}

// Load Hydrant Abnormal count
$query = "SELECT COUNT(*) as total FROM [apar].[dbo].[bimonthly_hydrant_inspections]
          WHERE 1=1 $dateFilter
          AND (body_hydrant_ok != 1 OR selang_ok != 1 OR couple_join_ok != 1 
               OR nozzle_ok != 1 OR check_sheet_ok != 1 OR valve_kran_ok != 1 
               OR lampu_ok != 1 OR cover_lampu_ok != 1 OR box_display_ok != 1 
               OR konsul_hydrant_ok != 1 OR jr_ok != 1 OR marking_ok != 1 
               OR label_ok != 1)";
$stmt = sqlsrv_query($koneksi, $query);
if ($stmt !== false && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $hydrant_abnormal = $row['total'] ?? 0;
}

// Function to get inspections data
function getInspections($type, $dateFilter) {
    global $koneksi;
    $data = array();
    
    try {
        if ($type === 'apar') {
            $query = "SELECT TOP 5
                        bi.id,
                        bi.inspection_date,
                        a.code,
                        a.area,
                        a.location,
                        bi.exp_date_ok,
                        bi.pressure_ok,
                        bi.weight_co2_ok,
                        bi.tube_ok,
                        bi.hose_ok,
                        bi.bracket_ok,
                        bi.wi_ok,
                        bi.form_kejadian_ok,
                        bi.sign_box_ok,
                        bi.sign_triangle_ok,
                        bi.marking_tiger_ok,
                        bi.marking_beam_ok,
                        bi.sr_apar_ok,
                        bi.kocok_apar_ok,
                        bi.label_ok
                      FROM [apar].[dbo].[bimonthly_apar_inspections] bi
                      INNER JOIN [apar].[dbo].[apars] a ON bi.apar_id = a.id
                      WHERE 1=1 $dateFilter
                      ORDER BY bi.inspection_date DESC";
        } else {
            $query = "SELECT TOP 5
                        bi.id,
                        bi.inspection_date,
                        h.code,
                        h.area,
                        h.location,
                        bi.body_hydrant_ok,
                        bi.selang_ok,
                        bi.couple_join_ok,
                        bi.nozzle_ok,
                        bi.check_sheet_ok,
                        bi.valve_kran_ok,
                        bi.lampu_ok,
                        bi.cover_lampu_ok,
                        bi.box_display_ok,
                        bi.konsul_hydrant_ok,
                        bi.jr_ok,
                        bi.marking_ok,
                        bi.label_ok
                      FROM [apar].[dbo].[bimonthly_hydrant_inspections] bi
                      INNER JOIN [apar].[dbo].[hydrants] h ON bi.hydrant_id = h.id
                      WHERE 1=1 $dateFilter
                      ORDER BY bi.inspection_date DESC";
        }
        
        $stmt = sqlsrv_query($koneksi, $query);
        
        if ($stmt === false) {
            error_log("Query Error for $type: " . print_r(sqlsrv_errors(), true));
            return array();
        }
        
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($type === 'apar') {
                $params = array(
                    $row['exp_date_ok'], $row['pressure_ok'], $row['weight_co2_ok'],
                    $row['tube_ok'], $row['hose_ok'], $row['bracket_ok'], $row['wi_ok'],
                    $row['form_kejadian_ok'], $row['sign_box_ok'], $row['sign_triangle_ok'],
                    $row['marking_tiger_ok'], $row['marking_beam_ok'], $row['sr_apar_ok'],
                    $row['kocok_apar_ok'], $row['label_ok']
                );
            } else {
                $params = array(
                    $row['body_hydrant_ok'], $row['selang_ok'], $row['couple_join_ok'],
                    $row['nozzle_ok'], $row['check_sheet_ok'], $row['valve_kran_ok'], $row['lampu_ok'],
                    $row['cover_lampu_ok'], $row['box_display_ok'], $row['konsul_hydrant_ok'],
                    $row['jr_ok'], $row['marking_ok'], $row['label_ok']
                );
            }
            
            $all_ok = true;
            foreach ($params as $param) {
                if ($param != 1) {
                    $all_ok = false;
                    break;
                }
            }
            
            $status = $all_ok ? 'OK' : 'Abnormal';
            
            // Format date - handle DateTime object properly
            $formatted_date = '-';
            if ($row['inspection_date'] !== null) {
                if ($row['inspection_date'] instanceof DateTime) {
                    $formatted_date = $row['inspection_date']->format('d-m-Y');
                } elseif (is_string($row['inspection_date'])) {
                    $formatted_date = $row['inspection_date'];
                } else {
                    // Try to convert via strtotime if it's a timestamp or other format
                    try {
                        $timestamp = strtotime($row['inspection_date']);
                        if ($timestamp) {
                            $formatted_date = date('d-m-Y', $timestamp);
                        }
                    } catch (Exception $e) {
                        $formatted_date = '-';
                    }
                }
            }
            
            $data[] = array(
                'id' => (int)$row['id'],
                'inspection_date' => $formatted_date,
                'code' => (string)$row['code'],
                'area' => (string)$row['area'],
                'location' => (string)$row['location'],
                'status' => $status
            );
        }
    } catch (Exception $e) {
        // Return empty array if error
        error_log("Exception in getInspections($type): " . $e->getMessage());
    }
    
    return $data;
}

// Get data
$aparData = getInspections('apar', $dateFilter);
$hydrantData = getInspections('hydrant', $dateFilter);
?>

<div class="page-inner">
    <style>
        .filter-bar {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-label {
            font-weight: 600;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .form-control-sm {
            height: 35px;
        }

        .status-card-custom {
            border-left: 3px solid;
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .border-left-primary { border-left-color: #007bff !important; }
        .border-left-warning { border-left-color: #ffc107 !important; }
        .border-left-success { border-left-color: #28a745 !important; }
        .border-left-danger { border-left-color: #dc3545 !important; }
        .border-left-info { border-left-color: #17a2b8 !important; }

        .status-card-custom h3 {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 10px 0;
            color: #333;
        }

        .status-card-custom p {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            margin: 5px 0;
        }

        .progress-bar {
            background: linear-gradient(90deg, #007bff, #0056b3);
            height: 6px;
            border-radius: 3px;
            margin-top: 8px;
        }

        .progress {
            background: #e9ecef;
            height: 6px;
            border-radius: 3px;
        }

        .export-button-group {
            margin-left: auto;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .export-btn {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .export-btn-excel {
            background: #28a745;
            color: white;
        }

        .export-btn-excel:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .export-btn-pdf {
            background: #dc3545;
            color: white;
        }

        .export-btn-pdf:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .table-slim {
            font-size: 0.85rem;
            margin-bottom: 0;
        }

        .table-slim td, .table-slim th {
            padding: 6px 10px;
            vertical-align: middle;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
            text-align: center;
            display: inline-block;
            min-width: 70px;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-abnormal {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 15px;
        }

        .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .nav-link.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background: none;
        }

        [data-background-color="dark"] .filter-bar,
        [data-background-color="dark"] .status-card-custom {
            background: #1a2035;
            color: #fff;
        }

        [data-background-color="dark"] .filter-label,
        [data-background-color="dark"] .status-card-custom h3,
        [data-background-color="dark"] .status-card-custom p {
            color: #fff;
        }

        .breadcrumb-item a { color: #3498db; }
        .breadcrumb-item.active { color: #666; }
    </style>

    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="?page=dashboard">E-Checksheet</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Report
                    </li>
                </ol>
            </nav>
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

    <!-- Month Filter Section -->
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">Filter Bulan:</label>
            <select id="month-filter" class="form-select form-control-sm" style="width: 180px;">
                <option value="all">All Months</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
        </div>
        <button id="btn-apply-filter" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> Apply
        </button>
    </div>

    <!-- KPI Cards Section -->
    <div class="row mb-4">
        <!-- Total APAR Card -->
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted">Total APAR</p>
                        <h3 id="total-apar"><?php echo $total_apar; ?></h3>
                    </div>
                    <i class="fas fa-fire-extinguisher fa-2x" style="color: #007bff; opacity: 0.3;"></i>
                </div>
                <small class="text-muted">Unit aktif</small>
            </div>
        </div>

        <!-- Total Hydrant Card -->
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted">Total Hydrant</p>
                        <h3 id="total-hydrant"><?php echo $total_hydrant; ?></h3>
                    </div>
                    <i class="fas fa-water fa-2x" style="color: #ffc107; opacity: 0.3;"></i>
                </div>
                <small class="text-muted">Unit aktif</small>
            </div>
        </div>

        <!-- APAR Abnormal Cases Card -->
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted">APAR Abnormal</p>
                        <h3 id="apar-abnormal"><?php echo $apar_abnormal; ?></h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x" style="color: #dc3545; opacity: 0.3;"></i>
                </div>
                <small class="text-muted">Kasus terbuka</small>
            </div>
        </div>

        <!-- Hydrant Abnormal Cases Card -->
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted">Hydrant Abnormal</p>
                        <h3 id="hydrant-abnormal"><?php echo $hydrant_abnormal; ?></h3>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x" style="color: #17a2b8; opacity: 0.3;"></i>
                </div>
                <small class="text-muted">Kasus terbuka</small>
            </div>
        </div>
    </div>

    <!-- Recent Inspections Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Inspections (Last 5)</h4>
                    <div class="export-button-group">
                        <button class="export-btn export-btn-excel btn-apar-excel" title="Export APAR to Excel">
                            <i class="fas fa-file-excel"></i> APAR Excel
                        </button>
                        <button class="export-btn export-btn-pdf btn-apar-pdf" title="Export APAR to PDF">
                            <i class="fas fa-file-pdf"></i> APAR PDF
                        </button>
                        <button class="export-btn export-btn-excel btn-hydrant-excel" title="Export Hydrant to Excel">
                            <i class="fas fa-file-excel"></i> Hydrant Excel
                        </button>
                        <button class="export-btn export-btn-pdf btn-hydrant-pdf" title="Export Hydrant to PDF">
                            <i class="fas fa-file-pdf"></i> Hydrant PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="apar-tab" data-bs-toggle="tab" data-bs-target="#apar-content" type="button" role="tab">
                                <i class="fas fa-fire-extinguisher"></i> APAR Inspections
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="hydrant-tab" data-bs-toggle="tab" data-bs-target="#hydrant-content" type="button" role="tab">
                                <i class="fas fa-water"></i> Hydrant Inspections
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- APAR Tab -->
                        <div class="tab-pane fade show active" id="apar-content" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-apar" class="table table-striped table-hover table-slim">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Kode</th>
                                            <th>Area</th>
                                            <th>Lokasi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (!empty($aparData)):
                                            foreach ($aparData as $index => $row):
                                                $badgeClass = $row['status'] === 'OK' ? 'status-ok' : 'status-abnormal';
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['inspection_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['area']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
                                        </tr>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data inspeksi</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Hydrant Tab -->
                        <div class="tab-pane fade" id="hydrant-content" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-hydrant" class="table table-striped table-hover table-slim">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Kode</th>
                                            <th>Area</th>
                                            <th>Lokasi</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (!empty($hydrantData)):
                                            foreach ($hydrantData as $index => $row):
                                                $badgeClass = $row['status'] === 'OK' ? 'status-ok' : 'status-abnormal';
                                        ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['inspection_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['area']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><span class="status-badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span></td>
                                        </tr>
                                        <?php
                                            endforeach;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data inspeksi</td>
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
    </div>
</div>

<script>
$(document).ready(function() {
    let aparTable = null;
    let hydrantTable = null;

    // Initialize DataTables on existing HTML tables
    function initializeDataTables() {
        aparTable = $('#table-apar').DataTable({
            pageLength: 10,
            responsive: true,
            searching: true,
            info: true,
            paging: true,
            ordering: true,
            language: {
                emptyTable: "Tidak ada data inspeksi",
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            columnDefs: [
                { orderable: false, targets: 0 },
                { orderable: false, targets: 5 }
            ]
        });

        hydrantTable = $('#table-hydrant').DataTable({
            pageLength: 10,
            responsive: true,
            searching: true,
            info: true,
            paging: true,
            ordering: true,
            language: {
                emptyTable: "Tidak ada data inspeksi",
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            columnDefs: [
                { orderable: false, targets: 0 },
                { orderable: false, targets: 5 }
            ]
        });
    }

    // Apply Filter - Reload page with month parameter
    $('#btn-apply-filter').on('click', function() {
        const month = $('#month-filter').val();
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('month', month);
        window.location.href = currentUrl.toString();
    });

    // Export Functions - Bimonthly Format & Detail Data
    function exportReport(type, format) {
        if (format === 'excel') {
            window.location.href = '../../actions/report/ac_export_excel_new.php?type=' + type;
        } else if (format === 'pdf') {
            window.location.href = '../../actions/report/ac_export_pdf.php?type=' + type;
        }
    }

    $('.btn-apar-excel').on('click', function() {
        exportReport('apar', 'excel');
    });

    $('.btn-apar-pdf').on('click', function() {
        exportReport('apar', 'pdf');
    });

    $('.btn-hydrant-excel').on('click', function() {
        exportReport('hydrant', 'excel');
    });

    $('.btn-hydrant-pdf').on('click', function() {
        exportReport('hydrant', 'pdf');
    });

    // Set current filter value
    const urlParams = new URLSearchParams(window.location.search);
    const monthParam = urlParams.get('month') || 'all';
    $('#month-filter').val(monthParam);

    // Initialize DataTables
    initializeDataTables();
});
</script>