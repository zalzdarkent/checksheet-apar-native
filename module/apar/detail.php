<?php
include(__DIR__ . '/../../actions/ac_get_apar_detail.php');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);

if (!$apar) {
    echo "<div class='page-inner'><div class='alert alert-danger'>APAR tidak ditemukan atau ID tidak valid.</div></div>";
    return;
}

$statusClass = ($apar['status'] === 'OK' || $apar['status'] === 'Good') ? 'status-ok' : 'status-abnormal';
?>

<!-- Bootstrap Notify Library -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-notify/0.2.0/css/bootstrap-notify.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-notify/0.2.0/js/bootstrap-notify.min.js"></script>

<div class="page-inner">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold text-info mb-0">APAR Detail</h3>
        <button onclick="history.back()" class="btn btn-sm btn-light border shadow-sm">Back</button>
    </div>

    <style>
        .detail-card {
            background: #1a2035;
            border-radius: 15px;
            padding: 40px 20px;
            color: #fff;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 30px;
        }

        .apar-large-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(231, 76, 60, 0.3));
        }

        .apar-large-code {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        .btn-inspeksi {
            background: #0088cc;
            color: #fff;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            margin-bottom: 25px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 136, 204, 0.3);
        }

        .btn-inspeksi:hover {
            background: #0077b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 136, 204, 0.4);
            color: #fff;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .status-ok { background: #27ae60; color: #fff; }
        .status-abnormal { background: #e74c3c; color: #fff; }
        
        .expired-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.95rem;
            background: #e74c3c;
            color: #fff;
            margin-left: 10px;
            box-shadow: 0 3px 8px rgba(231, 76, 60, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-box .label {
            display: block;
            font-size: 0.85rem;
            color: #a0a0a0;
            margin-bottom: 8px;
        }

        .info-box .value {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 40px 0 20px;
            padding: 15px 20px;
            border-bottom: 3px solid #0088cc;
            color: #000000;
            background: rgba(0, 136, 204, 0.1);
            border-radius: 8px 8px 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .empty-state {
            color: #888;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }

        /* DataTable styling */
        .table {
            color: #ddd;
            background-color: transparent;
            border-color: rgba(255, 255, 255, 0.05);
        }

        .table thead {
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .table thead th {
            color: #fff;
            font-weight: 700;
            background-color: rgba(30, 32, 53, 1);
            border-color: rgba(255, 255, 255, 0.05);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            border-color: rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            padding: 12px 15px;
        }

        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table .badge {
            white-space: nowrap;
        }
    </style>

    <div class="detail-card">
        <div class="apar-large-icon">
            <?php 
                $qr_url = $base_url . "index.php?page=apar-detail&id=" . $apar['id'];
            ?>
            <img src="actions/ac_generate_qrcode.php?data=<?php echo urlencode($qr_url); ?>" 
                 alt="QR Code" style="width: 150px; height: 150px; background: white; padding: 10px; border-radius: 10px;">
        </div>
        <div class="apar-large-code"><?php echo $apar['code']; ?></div>
        
        <a href="?page=apar-inspect&id=<?php echo $apar['id']; ?>" 
           class="btn btn-inspeksi btn-mulai-inspeksi" 
           id="btn-mulai-inspeksi"
           data-expired="<?php echo (isset($apar['is_expired']) && $apar['is_expired']) ? '1' : '0'; ?>"
           <?php echo (isset($apar['is_expired']) && $apar['is_expired']) ? 'style="opacity: 0.5; cursor: not-allowed; pointer-events: none;"' : ''; ?>>
            <i class="fas fa-clipboard-check"></i> Mulai Inspeksi
        </a>
        <a href="print_qr.php?type=apar&ids=<?php echo $apar['id']; ?>" target="_blank" class="btn btn-inspeksi bg-warning text-dark border-0">
            <i class="fas fa-print"></i> Print QR
        </a>

        <div class="d-block">
            <div class="status-badge <?php echo $statusClass; ?>">
                <?php echo $apar['status']; ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <span class="label">Area</span>
                <span class="value"><?php echo $apar['area']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Location</span>
                <span class="value"><?php echo $apar['location']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Weight</span>
                <span class="value"><?php echo $apar['weight']; ?> Kg</span>
            </div>
            <div class="info-box">
                <span class="label">Expired Date</span>
                <span class="value"><?php echo $apar['expired_date_fmt']; ?></span>
            </div>
        </div>
    </div>

    <div class="section-title">Riwayat Pemeriksaan</div>
    <?php if (empty($apar['history'])): ?>
        <div class="empty-state">Belum ada data pemeriksaan.</div>
    <?php else: ?>
        <table class="table table-striped table-hover table-sm" id="table-history" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>Oleh</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apar['history'] as $h): ?>
                    <tr>
                        <td><?php echo $h['inspection_date_fmt']; ?></td>
                        <td><?php echo $h['inspector_name'] ?: 'Unknown'; ?></td>
                        <td><span class="badge bg-success">✓ OK</span></td>
                        <td><?php echo $h['notes'] ?: '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="section-title">Abnormal Case</div>
    <?php if (empty($apar['cases'])): ?>
        <div class="empty-state">Belum ada abnormal case.</div>
    <?php else: ?>
        <table class="table table-striped table-hover table-sm" id="table-cases" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>Abnormal Case</th>
                    <th>Countermeasure</th>
                    <th>Due Date</th>
                    <th>PIC</th>
                    <th>Status</th>
                    <th>Verified</th>
                    <th>Foto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apar['cases'] as $c): ?>
                    <tr>
                        <td><?php echo isset($c['created_at_fmt']) ? $c['created_at_fmt'] : '-'; ?></td>
                        <td><?php echo $c['abnormal_case']; ?></td>
                        <td><?php echo $c['countermeasure'] ?: '-'; ?></td>
                        <td><?php echo isset($c['due_date_fmt']) ? $c['due_date_fmt'] : '-'; ?></td>
                        <td><?php echo $c['pic_name'] ?: 'Unassigned'; ?></td>
                        <td>
                            <span class="badge <?php echo $c['status'] === 'Fixed' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $c['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (isset($c['verified']) && $c['verified']): ?>
                                <span class="badge bg-success">✓ Verified</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($c['foto']) && $c['foto']): ?>
                                <a href="<?php echo $c['foto']; ?>" target="_blank" class="btn btn-sm btn-outline-info">View</a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Initialize history table if exists
    if ($('#table-history').length) {
        $('#table-history').DataTable({
            "paging": true,
            "pageLength": 10,
            "searching": true,
            "ordering": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "info": "Menampilkan _START_ ke _END_ dari _TOTAL_ entri"
            }
        });
    }
    
    // Initialize cases table if exists
    if ($('#table-cases').length) {
        $('#table-cases').DataTable({
            "paging": true,
            "pageLength": 10,
            "searching": true,
            "ordering": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "info": "Menampilkan _START_ ke _END_ dari _TOTAL_ entri"
            }
        });
    }
    
    // Handle expired APAR notification
    var ekspiredBtn = $('#btn-mulai-inspeksi');
    if (ekspiredBtn.data('expired') == '1') {
        $.notify({
            icon: 'fas fa-exclamation-circle',
            title: '<strong>⚠️ APAR Expired</strong>',
            message: 'APAR ini sudah kedaluwarsa (expired). Harus segera diganti atau ditangani sebelum bisa diinspeksi.',
            type: 'danger'
        }, {
            element: 'body',
            position: null,
            allow_dismiss: true,
            placement: {
                from: "top",
                align: "center"
            },
            offset: 20,
            spacing: 10,
            z_index: 9999,
            delay: 0,
            timer: 0,
            url_target: '_blank'
        });
    }
    
    // Prevent click if expired
    ekspiredBtn.on('click', function(e) {
        if ($(this).data('expired') == '1') {
            e.preventDefault();
            $.notify({
                icon: 'fas fa-ban',
                title: '<strong>❌ Action Blocked</strong>',
                message: 'Inspeksi tidak bisa dimulai karena APAR sudah expired. Silakan hubungi admin untuk penggantian unit.',
                type: 'danger'
            }, {
                element: 'body',
                position: null,
                placement: {
                    from: "top",
                    align: "center"
                },
                offset: 20,
                spacing: 10,
                z_index: 9999,
                delay: 3000
            });
            return false;
        }
    });
});
</script>
