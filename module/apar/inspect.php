<?php
include(__DIR__ . '/../../actions/ac_get_apar_detail.php');

if (!$apar) {
    echo "<div class='alert alert-danger p-4'>APAR tidak ditemukan atau ID tidak valid.</div>";
    return;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);
?>

<div class="page-inner">
    <style>
        /* Ensure button text is always visible */
        .btn-check:checked + .btn {
            color: white !important;
        }
        .btn-outline-success:hover, 
        .btn-outline-danger:hover {
            color: white !important;
        }
    </style>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">Check Sheet Pemeriksaan APAR Per 2 Bulan</h3>
        <button onclick="history.back()" class="btn btn-sm btn-secondary">Kembali</button>
    </div>

    <!-- Info Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Kode APAR</small>
                    <div class="fw-bold"><?php echo $apar['code']; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Tipe</small>
                    <div class="fw-bold"><?php echo $apar['type']; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Lokasi</small>
                    <div class="fw-bold"><?php echo $apar['location']; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Expired</small>
                    <div class="fw-bold"><?php echo $apar['expired_date_fmt']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <form id="form-inspection" method="POST" enctype="multipart/form-data">
        <!-- Tanggal Pemeriksaan Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom ps-4 py-3">
                <h5 class="mb-0">Tanggal & Foto Pemeriksaan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Pemeriksaan</label>
                        <input type="datetime-local" name="inspection_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Foto Unit APAR</label>
                        <input type="file" name="unit_photo" accept="image/*" class="form-control">
                        <small class="text-muted">Format: JPG, PNG (Optional)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Pemeriksaan Table -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom ps-4 py-3">
                <h5 class="mb-0">Daftar Pemeriksaan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%" class="text-center">No.</th>
                                <th style="width: 25%">Item Pemeriksaan</th>
                                <th style="width: 15%" class="text-center">Hasil</th>
                                <th style="width: 20%">Foto/Evidence</th>
                                <th style="width: 35%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items = [
                                ['key' => 'exp_date', 'name' => 'Exp. Date (Maksimal 4 Tahun)', 'has_notes' => true],
                                ['key' => 'pressure', 'name' => 'Pressure (Zona Hijau)', 'has_notes' => false],
                                ['key' => 'weight_co2', 'name' => 'Weight CO2', 'has_notes' => false],
                                ['key' => 'tube', 'name' => 'Tube', 'has_notes' => false],
                                ['key' => 'hose', 'name' => 'Hose', 'has_notes' => false],
                                ['key' => 'bracket', 'name' => 'Bracket (Tidak Rusak)', 'has_notes' => false],
                                ['key' => 'wi', 'name' => 'WI (Ada & Terbaca)', 'has_notes' => false],
                                ['key' => 'form_kejadian', 'name' => 'Form Kejadian (Ada)', 'has_notes' => false],
                                ['key' => 'sign_box', 'name' => 'SIGN Kotak (Ada)', 'has_notes' => false],
                                ['key' => 'sign_triangle', 'name' => 'SIGN Segitiga (Ada)', 'has_notes' => false],
                                ['key' => 'marking_tiger', 'name' => 'Marking Tiger (Ada)', 'has_notes' => false],
                                ['key' => 'marking_beam', 'name' => 'Marking Beam (Ada)', 'has_notes' => false],
                                ['key' => 'sr_apar', 'name' => '5R APAR (Bersih)', 'has_notes' => false],
                                ['key' => 'kocok_apar', 'name' => 'Kocok APAR (Desiran Tepung)', 'has_notes' => true],
                                ['key' => 'label', 'name' => 'Label (Ada/Terbaca)', 'has_notes' => false],
                            ];
                            
                            foreach ($items as $idx => $item):
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $idx + 1; ?></td>
                                <td class="align-middle"><?php echo $item['name']; ?></td>
                                <td class="text-center align-middle">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" name="<?php echo $item['key']; ?>_ok" id="<?php echo $item['key']; ?>_ok" value="1">
                                        <label class="btn btn-outline-success btn-sm" for="<?php echo $item['key']; ?>_ok">OK</label>
                                        
                                        <input type="radio" class="btn-check" name="<?php echo $item['key']; ?>_ok" id="<?php echo $item['key']; ?>_abnormal" value="0">
                                        <label class="btn btn-outline-danger btn-sm" for="<?php echo $item['key']; ?>_abnormal">Abnormal</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="file" name="<?php echo $item['key']; ?>_foto" accept="image/*" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <?php if ($item['key'] === 'exp_date'): ?>
                                        <input type="text" name="<?php echo $item['key']; ?>_keterangan" class="form-control form-control-sm" placeholder="Catatan exp date...">
                                    <?php elseif ($item['key'] === 'kocok_apar'): ?>
                                        <input type="text" name="<?php echo $item['key']; ?>_keterangan" class="form-control form-control-sm" placeholder="Catatan...">
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Catatan Umum Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom ps-4 py-3">
                <h5 class="mb-0">Catatan Umum</h5>
            </div>
            <div class="card-body">
                <textarea name="general_notes" class="form-control" rows="4" placeholder="Tulis catatan atau temuan lainnya..."></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-check-circle me-2"></i> Simpan Inspeksi
                    </button>
                    <button type="submit" name="action" value="draft" class="btn btn-warning btn-lg flex-grow-1">
                        <i class="fas fa-save me-2"></i> Simpan Sebagai Draft
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg" onclick="history.back()">
                        <i class="fas fa-times me-2"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('form-inspection').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('type', 'apar');
    formData.append('equipment_id', '<?php echo $apar['id']; ?>');
    
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    fetch('actions/ac_store_inspection.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Inspeksi berhasil disimpan!');
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Simpan Inspeksi';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Simpan Inspeksi';
    });
});

// Set default datetime to now
const now = new Date();
const year = now.getFullYear();
const month = String(now.getMonth() + 1).padStart(2, '0');
const day = String(now.getDate()).padStart(2, '0');
const hours = String(now.getHours()).padStart(2, '0');
const minutes = String(now.getMinutes()).padStart(2, '0');
const dateTimeLocal = `${year}-${month}-${day}T${hours}:${minutes}`;
document.querySelector('input[name="inspection_date"]').value = dateTimeLocal;
</script>
