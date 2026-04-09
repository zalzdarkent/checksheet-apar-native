<?php
include(__DIR__ . '/../../actions/hydrant/ac_get_detail.php');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);

if (!$hydrant) {
    echo "<div class='page-inner'><div class='alert alert-danger'>Hydrant tidak ditemukan atau ID tidak valid.</div></div>";
    return;
}

$statusClass = ($hydrant['status'] === 'OK' || $hydrant['status'] === 'Good') ? 'status-ok' : 'status-abnormal';
?>

<div class="page-inner">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold text-info mb-0">Hydrant Detail</h3>
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

        .hydrant-large-icon {
            font-size: 80px;
            color: #e67e22;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(230, 126, 34, 0.3));
        }

        .hydrant-large-code {
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
            background: #005a87;
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

        .status-ok {
            background: #27ae60;
            color: #fff;
        }

        .status-abnormal {
            background: #e74c3c;
            color: #fff;
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
        <div class="hydrant-large-icon">
            <?php
            $qr_url = $base_url . "index.php?page=hydrant-detail&id=" . $hydrant['id'];
            ?>
            <img src="actions/ac_generate_qrcode.php?data=<?php echo urlencode($qr_url); ?>" alt="QR Code"
                style="width: 150px; height: 150px; background: white; padding: 10px; border-radius: 10px;">
        </div>
        <div class="hydrant-large-code"><?php echo $hydrant['code']; ?></div>

        <a href="?page=hydrant-inspect&id=<?php echo $hydrant['id']; ?>" class="btn btn-inspeksi">
            <i class="fas fa-clipboard-check"></i> Mulai Inspeksi
        </a>
        <button type="button" class="btn btn-inspeksi bg-warning text-dark border-0 m-0 mb-4" id="btn-scan-qr"
            style="cursor:pointer; display:inline-block; margin-bottom: 25px !important;">
            <i class="fas fa-qrcode"></i> Scan QR
        </button>
        <input type="file" id="qr-upload-input" accept="image/*" capture="environment" style="display: none;">

        <div class="d-block">
            <div class="status-badge <?php echo $statusClass; ?>">
                <?php echo $hydrant['status']; ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <span class="label">Area</span>
                <span class="value"><?php echo $hydrant['area']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Location</span>
                <span class="value"><?php echo $hydrant['location']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Type</span>
                <span class="value"><?php echo $hydrant['type'] ?: 'Standard'; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Last Inspection</span>
                <span class="value"><?php echo $hydrant['last_inspection_fmt']; ?></span>
            </div>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-camera me-2"></i>Scanner Live</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div id="scanner-container" class="position-relative overflow-hidden rounded bg-black" style="width: 100%; aspect-ratio: 1/1;">
                        <video id="scanner-video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                        <div class="scanner-overlay"></div>
                    </div>
                    <p class="mt-3 mb-0 small opacity-75">Arahkan kamera ke QR Code unit.</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="$('#qr-upload-input').click()">
                        <i class="fas fa-image me-1"></i> Upload dari Galeri
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: 70%;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
            border-radius: 20px;
        }
        .scanner-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #0088cc;
            box-shadow: 0 0 10px #0088cc;
            animation: scan-line 2s linear infinite;
        }
        @keyframes scan-line {
            0% { top: 0; }
            100% { top: 100%; }
        }
    </style>

    <div class="section-title">Riwayat Pengecekan Rutin (Bimonthly)</div>
    <?php if (empty($hydrant['history'])): ?>
        <div class="empty-state">Belum ada data pemeriksaan.</div>
    <?php else: ?>
        <table class="table table-striped table-hover table-sm" id="table-history" style="width:100%">
            <thead class="table-dark">
                <tr>
                    <th>Tanggal</th>
                    <th>Oleh</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hydrant['history'] as $h): ?>
                    <tr>
                        <td><?php echo $h['inspection_date_fmt']; ?></td>
                        <td><?php echo $h['inspector_name'] ?: 'Unknown'; ?></td>
                        <td>
                            <?php if (isset($h['insp_status']) && $h['insp_status'] === 'NG'): ?>
                                <span class="badge bg-danger">✗ NG</span><br>
                                <small class="text-danger fw-bold">(<?php echo htmlspecialchars($h['ng_text']); ?>)</small>
                            <?php else: ?>
                                <span class="badge bg-success">✓ OK</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $h['notes'] ?: '-'; ?></td>
                        <td>
                            <button class='btn btn-sm btn-info text-white btn-view-history' data-info='<?php echo htmlspecialchars(json_encode($h['full_items']), ENT_QUOTES, 'UTF-8'); ?>'><i class='fas fa-eye'></i> Detail</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>


    <div class="section-title">Riwayat Kerusakan & Perbaikan</div>
    <?php if (empty($hydrant['cases'])): ?>
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
                <?php foreach ($hydrant['cases'] as $c): ?>
                    <tr>
                        <td><?php echo isset($c['created_at_fmt']) ? $c['created_at_fmt'] : '-'; ?></td>
                        <td><?php echo $c['abnormal_case']; ?></td>
                        <td><?php echo $c['countermeasure'] ?: '-'; ?></td>
                        <td><?php echo isset($c['due_date_fmt']) ? $c['due_date_fmt'] : '-'; ?></td>
                        <td><?php echo $c['pic_name'] ?: 'Unassigned'; ?></td>
                        <td>
                            <span class="badge 
                                <?php
                                echo $c['status'] === 'Open' ? 'bg-danger' :
                                    ($c['status'] === 'On Progress' ? 'bg-warning' :
                                        ($c['status'] === 'Verified' ? 'bg-success' : 'bg-secondary'));
                                ?>">
                                <?php echo $c['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (isset($c['verified_by_name']) && $c['verified_by_name']): ?>
                                <span class="badge bg-success">✓ <?php echo $c['verified_by_name']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($c['repair_photo']) && $c['repair_photo']): ?>
                                <a href="storage/<?php echo $c['repair_photo']; ?>" target="_blank"
                                    class="btn btn-sm btn-outline-info">View</a>
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

<script src="assets/vendor/zxing/zxing.min.js"></script>
<script src="assets/js/qr-scanner.js"></script>
<script>
    $(document).ready(function () {
        // Live Scanner Modal handling
        const scannerModalEL = document.getElementById('scannerModal');
        const scannerModal = new bootstrap.Modal(scannerModalEL);

        $('#btn-scan-qr').on('click', function() {
            scannerModal.show();
        });

        scannerModalEL.addEventListener('shown.bs.modal', function() {
            SystemQRScanner.startScan('scanner-video', function(resultText) {
                if (resultText) {
                    if (resultText.startsWith("http")) {
                        window.location.href = resultText;
                    } else {
                        alert("QR Code tidak valid: " + resultText);
                        SystemQRScanner.stopScan();
                        scannerModal.hide();
                    }
                }
            });
        });

        scannerModalEL.addEventListener('hidden.bs.modal', function() {
            SystemQRScanner.stopScan();
        });

        $('#qr-upload-input').on('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;

            // Tampilkan loading state
            var scanBtn = $('#btn-scan-qr');
            var originalBtnHtml = scanBtn.html();
            scanBtn.html('<i class="fas fa-spinner fa-spin"></i> Reading...');
            
            var formData = new FormData();
            formData.append('qr_image', file);

            $.ajax({
                url: 'actions/ac_decode_qr.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    scanBtn.html(originalBtnHtml);
                    $('#qr-upload-input').val(''); // reset
                    scannerModal.hide();

                    try {
                        var actRes = typeof response === 'string' ? JSON.parse(response) : response;
                        if (actRes.success) {
                            if (actRes.text.startsWith("http")) {
                                window.location.href = actRes.text;
                            } else {
                                alert("QR Code tidak valid atau bukan link sistem: " + actRes.text);
                            }
                        } else {
                            alert(actRes.message || 'Gagal membaca QR code dari gambar.');
                        }
                    } catch (err) {
                        alert('Terjadi kesalahan menterjemahkan QR Code.');
                    }
                },
                error: function () {
                    scanBtn.html(originalBtnHtml);
                    $('#qr-upload-input').val(''); // reset
                    alert('Gagal menghubungi server untuk membaca QR code.');
                }
            });
        });

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

        // Modal Trigger for view history
        $(document).on('click', '.btn-view-history', function() {
            var items = $(this).data('info');
            var container = $('#history_items_container');
            container.empty();
            
            if (!items || items.length === 0) {
                container.append('<div class="col-12 text-center text-muted">Tidak ada rincian data item.</div>');
            } else {
                items.forEach(function(item) {
                    var isOk = (item.ok === 1 || item.ok === '1' || item.ok === true || item.ok === 'true');
                    var statusHtml = isOk ? '<strong class="d-block text-success">✓ OK</strong>' : '<strong class="d-block text-danger">✗ NG</strong>';
                    
                    var photoHtml = (item.photo && item.photo.trim() !== '') ? '<img src="storage/inspections/' + item.photo + '" class="img-fluid rounded border mt-2" style="max-height:120px; object-fit:cover;">' : '<div class="text-muted small mt-2 fst-italic">Tanpa foto</div>';
                    
                    var ketHtml = item.keterangan ? '<div class="small mt-1 text-secondary">Ket: ' + item.keterangan + '</div>' : '';

                    container.append(`
                        <div class="col-md-6 col-lg-4">
                            <div class="border p-2 rounded bg-light items-box h-100">
                                <span class="d-block text-muted small fw-bold mb-1">` + item.label + `</span>
                                ` + statusHtml + `
                                ` + ketHtml + `
                                ` + photoHtml + `
                            </div>
                        </div>
                    `);
                });
            }
            var modal = new bootstrap.Modal(document.getElementById('historyDetailModal'));
            modal.show();
        });
    });
</script>

<!-- Modal View Inspection History Data -->
<div class="modal fade" id="historyDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Rincian Hasil Inspeksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="history_items_container">
                    <!-- Populated by JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
