<?php
require_once __DIR__ . '/../../config/db_koneksi.php';

// Get month filter
$month = $_GET['month'] ?? 'all';
$dateFilter = "";
$monthTitle = "All Time";

if ($month !== 'all') {
    $monthNum = intval($month);
    $year = date('Y');
    $monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $monthTitle = ($monthNum >= 1 && $monthNum <= 12) ? $monthNames[$monthNum - 1] . " " . $year : "All Time";
    $dateFilter = "AND MONTH(bi.inspection_date) = $monthNum AND YEAR(bi.inspection_date) = $year";
}

// Initialize KPI values
$total_apar = 0;
$total_hydrant = 0;
$apar_abnormal = 0;
$hydrant_abnormal = 0;

// Load Total Counts from unified MASTER
$sql_m = "SELECT 
            SUM(CASE WHEN asset_type = 'APAR' THEN 1 ELSE 0 END) as t_apar, 
            SUM(CASE WHEN asset_type = 'HYDRANT' THEN 1 ELSE 0 END) as t_hydrant 
          FROM [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] WHERE is_active = 1";
$stmt_m = sqlsrv_query($koneksi, $sql_m);
if ($stmt_m && $row_m = sqlsrv_fetch_array($stmt_m, SQLSRV_FETCH_ASSOC)) {
    $total_apar = $row_m['t_apar'] ?? 0;
    $total_hydrant = $row_m['t_hydrant'] ?? 0;
}

// Abnormal Check item lists
$apar_items = ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'];
$apar_ng_check = "AND (" . implode(" != 1 OR ", $apar_items) . " != 1)";

$hydrant_items = ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];
$hydrant_ng_check = "AND (" . implode(" != 1 OR ", $hydrant_items) . " != 1)";

// Get Abnormal Counts from unified TRANS
$sql_ab_apar = "SELECT COUNT(*) as total FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
                INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
                WHERE m.asset_type = 'APAR' $dateFilter $apar_ng_check";
$stmt_ab_apar = sqlsrv_query($koneksi, $sql_ab_apar);
if ($stmt_ab_apar && $row_a = sqlsrv_fetch_array($stmt_ab_apar, SQLSRV_FETCH_ASSOC)) {
    $apar_abnormal = $row_a['total'] ?? 0;
}

$sql_ab_hydrant = "SELECT COUNT(*) as total FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
                   INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
                   WHERE m.asset_type = 'HYDRANT' $dateFilter $hydrant_ng_check";
$stmt_ab_hydrant = sqlsrv_query($koneksi, $sql_ab_hydrant);
if ($stmt_ab_hydrant && $row_h = sqlsrv_fetch_array($stmt_ab_hydrant, SQLSRV_FETCH_ASSOC)) {
    $hydrant_abnormal = $row_h['total'] ?? 0;
}

// Function to get inspections data
function getInspections($type, $dateFilter)
{
    global $koneksi;
    $data = array();
    $asset_type = strtoupper($type);

    try {
        $query = "SELECT TOP 5
                    bi.id,
                    bi.inspection_date,
                    m.asset_code as code,
                    m.area,
                    m.location,
                    bi.*
                  FROM [apar].[dbo].[SE_FIRE_PROTECTION_TRANS] bi
                  INNER JOIN [apar].[dbo].[SE_FIRE_PROTECTION_MASTER] m ON bi.asset_id = m.id
                  WHERE m.asset_type = '$asset_type' $dateFilter
                  ORDER BY bi.inspection_date DESC";

        $stmt = sqlsrv_query($koneksi, $query);
        if ($stmt === false) return array();

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $check_items = ($type === 'apar') ? 
                ['exp_date_ok', 'pressure_ok', 'weight_co2_ok', 'tube_ok', 'hose_ok', 'bracket_ok', 'wi_ok', 'form_kejadian_ok', 'sign_box_ok', 'sign_triangle_ok', 'marking_tiger_ok', 'marking_beam_ok', 'sr_apar_ok', 'kocok_apar_ok', 'label_ok'] :
                ['body_hydrant_ok', 'selang_ok', 'couple_join_ok', 'nozzle_ok', 'check_sheet_ok', 'valve_kran_ok', 'lampu_ok', 'cover_lampu_ok', 'box_display_ok', 'konsul_hydrant_ok', 'jr_ok', 'marking_ok', 'label_ok'];

            $all_ok = true;
            foreach ($check_items as $item) {
                if (isset($row[$item]) && $row[$item] != 1) {
                    $all_ok = false;
                    break;
                }
            }

            $formatted_date = '-';
            if ($row['inspection_date'] instanceof DateTime) {
                $formatted_date = $row['inspection_date']->format('d-m-Y');
            }

            $data[] = array(
                'id' => (int) $row['id'],
                'inspection_date' => $formatted_date,
                'code' => (string) $row['code'],
                'area' => (string) $row['area'],
                'location' => (string) $row['location'],
                'status' => $all_ok ? 'OK' : 'Abnormal'
            );
        }
    } catch (Exception $e) { }
    return $data;
}

$aparData = getInspections('apar', $dateFilter);
$hydrantData = getInspections('hydrant', $dateFilter);
?>
<!-- Rest of the UI remains consistent -->
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

        .export-btn-excel { background: #28a745; color: white; }
        .export-btn-pdf { background: #dc3545; color: white; }

        .table-slim { font-size: 0.85rem; margin-bottom: 0; }
        .table-slim td, .table-slim th { padding: 6px 10px; vertical-align: middle; }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
            text-align: center;
            display: inline-block;
            min-width: 70px;
        }

        .status-ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-abnormal { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>

    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">Filter Bulan:</label>
            <select id="month-filter" class="form-select" style="width: 180px;">
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

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-primary">
                <p class="text-muted">Total APAR</p>
                <h3><?php echo $total_apar; ?></h3>
                <small class="text-muted">Unit aktif</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-warning">
                <p class="text-muted">Total Hydrant</p>
                <h3><?php echo $total_hydrant; ?></h3>
                <small class="text-muted">Unit aktif</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-danger">
                <p class="text-muted">APAR Abnormal</p>
                <h3><?php echo $apar_abnormal; ?></h3>
                <small class="text-muted">Pemeriksaan NG</small>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="status-card-custom border-left-info">
                <p class="text-muted">Hydrant Abnormal</p>
                <h3><?php echo $hydrant_abnormal; ?></h3>
                <small class="text-muted">Pemeriksaan NG</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Inspections (Last 5)</h4>
                    <div class="export-button-group">
                        <button class="export-btn export-btn-excel btn-apar-excel"><i class="fas fa-file-excel"></i> APAR Excel</button>
                        <button class="export-btn export-btn-pdf btn-apar-pdf"><i class="fas fa-file-pdf"></i> APAR PDF</button>
                        <button class="export-btn export-btn-excel btn-hydrant-excel"><i class="fas fa-file-excel"></i> Hydrant Excel</button>
                        <button class="export-btn export-btn-pdf btn-hydrant-pdf"><i class="fas fa-file-pdf"></i> Hydrant PDF</button>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="apar-tab" data-bs-toggle="tab" data-bs-target="#apar-content" type="button" role="tab">APAR Inspections</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="hydrant-tab" data-bs-toggle="tab" data-bs-target="#hydrant-content" type="button" role="tab">Hydrant Inspections</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="apar-content" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-apar" class="table table-striped table-hover table-slim">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kode</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($aparData)): foreach ($aparData as $row): ?>
                                            <tr>
                                                <td><?php echo $row['inspection_date']; ?></td>
                                                <td><?php echo $row['code']; ?></td>
                                                <td><?php echo $row['area']; ?></td>
                                                <td><span class="status-badge <?php echo $row['status'] === 'OK' ? 'status-ok' : 'status-abnormal'; ?>"><?php echo $row['status']; ?></span></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="hydrant-content" role="tabpanel">
                            <div class="table-responsive">
                                <table id="table-hydrant" class="table table-striped table-hover table-slim">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kode</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($hydrantData)): foreach ($hydrantData as $row): ?>
                                            <tr>
                                                <td><?php echo $row['inspection_date']; ?></td>
                                                <td><?php echo $row['code']; ?></td>
                                                <td><?php echo $row['area']; ?></td>
                                                <td><span class="status-badge <?php echo $row['status'] === 'OK' ? 'status-ok' : 'status-abnormal'; ?>"><?php echo $row['status']; ?></span></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
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
    $(document).ready(function () {
        $('#btn-apply-filter').on('click', function () {
            const month = $('#month-filter').val();
            window.location.href = '?page=report-index&month=' + month;
        });

        // Set current filter
        const urlParams = new URLSearchParams(window.location.search);
        $('#month-filter').val(urlParams.get('month') || 'all');

        $('.btn-apar-excel').on('click', function () { window.location.href = 'actions/report/ac_export_excel_new.php?type=apar'; });
        $('.btn-apar-pdf').on('click', function () { window.location.href = 'actions/report/ac_export_pdf.php?type=apar'; });
        $('.btn-hydrant-excel').on('click', function () { window.location.href = 'actions/report/ac_export_excel_new.php?type=hydrant'; });
        $('.btn-hydrant-pdf').on('click', function () { window.location.href = 'actions/report/ac_export_pdf.php?type=hydrant'; });

        // Initialize DataTables
        const dtConfig = {
            paging: true,
            pageLength: 10,
            searching: true,
            ordering: true,
            info: true,
            language: {
                search: "Search:",
                lengthMenu: "_MENU_ per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    next: "Next",
                    previous: "Prev"
                }
            }
        };

        const tableApar = $('#table-apar').DataTable(dtConfig);
        const tableHydrant = $('#table-hydrant').DataTable(dtConfig);

        // Adjust columns on tab switch
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            tableApar.columns.adjust().draw();
            tableHydrant.columns.adjust().draw();
        });
    });
</script>
