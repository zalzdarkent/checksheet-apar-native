<?php
include(__DIR__ . '/../../actions/hydrant/ac_get_detail.php');

if (!$hydrant) {
    echo "<div class='alert alert-danger p-4'>Hydrant tidak ditemukan atau ID tidak valid.</div>";
    return;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);
?>

<div class="page-inner">
    <style>
        /* Ensure button text is always visible */
        .btn-check:checked+.btn {
            color: white !important;
        }

        .btn-outline-success:hover,
        .btn-outline-danger:hover {
            color: white !important;
        }
    </style>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold mb-0">Check Sheet Pemeriksaan Hydrant Per 2 Bulan</h3>
        <button onclick="history.back()" class="btn btn-sm btn-secondary">Kembali</button>
    </div>

    <!-- Info Card -->
    <div class="card mb-4 border-0 shadow-sm"
        style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Kode Hydrant</small>
                    <div class="fw-bold"><?php echo $hydrant['code']; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Tipe</small>
                    <div class="fw-bold"><?php echo $hydrant['type'] ?: 'Standard'; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Lokasi</small>
                    <div class="fw-bold"><?php echo $hydrant['location']; ?></div>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-white-50">Area</small>
                    <div class="fw-bold"><?php echo $hydrant['area']; ?></div>
                </div>
            </div>
        </div>
    </div>

    <form id="form-inspection" method="POST" enctype="multipart/form-data">
        <!-- Tanggal & Jenis Pemeriksaan Section -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom ps-4 py-3">
                <h5 class="mb-0">Tanggal & Jenis Pemeriksaan</h5>
            </div>
            <div class="card-body">
                <input type="hidden" name="jenis_hydrant"
                    value="<?php echo htmlspecialchars($hydrant['type'] ?: 'Standard'); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="form-label fw-semibold">Tanggal Pemeriksaan</label>
                        <input type="datetime-local" name="inspection_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Foto Unit Hydrant</label>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hType = strtolower(trim($hydrant['type'] ?? ''));
                            $isIndoor = ($hType === 'indoor' || strpos($hType, 'indoor') !== false);
                            $isOutdoor = ($hType === 'outdoor' || strpos($hType, 'outdoor') !== false);

                            $items = [
                                ['key' => 'body_hydrant', 'name' => 'Body Hydrant', 'standard' => 'Tidak penyok', 'req' => 'both'],
                                ['key' => 'selang', 'name' => 'Selang', 'standard' => 'Tidak robek', 'req' => 'both'],
                                ['key' => 'couple_join', 'name' => 'Couple Join', 'req' => 'both'],
                                ['key' => 'nozzle', 'name' => 'Nozzle', 'standard' => 'Ada', 'req' => 'both'],
                                ['key' => 'check_sheet', 'name' => 'Check Sheet', 'req' => 'both'],
                                ['key' => 'valve_kran', 'name' => 'Valve/Kran', 'standard' => 'Tidak patah, tidak bocor', 'req' => 'indoor'],
                                ['key' => 'lampu', 'name' => 'Lampu', 'standard' => 'Menyala', 'req' => 'indoor'],
                                ['key' => 'cover_lampu', 'name' => 'Cover Lampu', 'standard' => 'Tidak pecah', 'req' => 'indoor'],
                                ['key' => 'kunci_pilar_hydrant', 'name' => 'Kunci Pilar Hydrant', 'req' => 'outdoor'],
                                ['key' => 'pilar_hydrant', 'name' => 'Pilar Hydrant', 'standard' => 'Mudah dibuka & tidak bocor', 'req' => 'outdoor'],
                                ['key' => 'marking', 'name' => 'Marking', 'standard' => 'Ada', 'req' => 'both'],
                                ['key' => 'sign_larangan', 'name' => 'Sign Larangan', 'standard' => 'Ada, tidak sobek', 'req' => 'both'],
                                ['key' => 'nomor_hydrant', 'name' => 'Nomor Hydrant', 'standard' => 'Ada', 'req' => 'both'],
                                ['key' => 'wi_hydrant', 'name' => 'WI Hydrant', 'standard' => 'Ada, tidak sobek', 'req' => 'both'],
                            ];

                            foreach ($items as $idx => $item):
                                $isDisabled = false;
                                $badge = '';

                                if ($item['req'] === 'indoor' && !$isIndoor) {
                                    $isDisabled = true;
                                    $badge = '<br><span class="badge bg-secondary" style="font-size: 0.7em;">Khusus Indoor</span>';
                                } elseif ($item['req'] === 'outdoor' && !$isOutdoor) {
                                    $isDisabled = true;
                                    $badge = '<br><span class="badge bg-secondary" style="font-size: 0.7em;">Khusus Outdoor</span>';
                                }

                                $disabledAttr = $isDisabled ? 'disabled' : 'required';
                                $fileRequiredAttr = $isDisabled ? 'disabled' : 'required';
                                ?>
                                <tr class="<?php echo $isDisabled ? 'opacity-50 bg-light' : ''; ?>">
                                    <td class="text-center align-middle"><?php echo $idx + 1; ?></td>
                                    <td class="align-middle">
                                        <?php echo $item['name']; ?>
                                        <?php if (!$isDisabled): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>

                                        <?php if (isset($item['standard'])): ?>
                                            <br>
                                            <span style="font-size: 0.8em; color: #6c757d;">
                                                (<?php echo $item['standard']; ?>)
                                            </span>
                                        <?php endif; ?>

                                        <?php echo $badge; ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <input type="radio" class="btn-check" name="<?php echo $item['key']; ?>_ok"
                                                id="<?php echo $item['key']; ?>_ok" value="1" <?php echo $disabledAttr; ?>>
                                            <label class="btn btn-outline-success btn-sm"
                                                for="<?php echo $item['key']; ?>_ok">OK</label>

                                            <input type="radio" class="btn-check" name="<?php echo $item['key']; ?>_ok"
                                                id="<?php echo $item['key']; ?>_abnormal" value="0" <?php echo $disabledAttr; ?>>
                                            <label class="btn btn-outline-danger btn-sm"
                                                for="<?php echo $item['key']; ?>_abnormal">Abnormal</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="file" name="<?php echo $item['key']; ?>_foto" accept="image/*"
                                            class="form-control form-control-sm" <?php echo $fileRequiredAttr; ?>>
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
                <textarea name="general_notes" class="form-control" rows="4"
                    placeholder="Tulis catatan atau temuan lainnya..."></textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card border-0">
            <div class="card-body">
                <div class="d-flex gap-2">
                    <button type="submit" name="action" value="submit" class="btn btn-primary btn-lg flex-grow-1">
                        <i class="fas fa-check-circle me-2"></i> Simpan Inspeksi
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
    document.getElementById('form-inspection').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('type', 'hydrant');
        formData.append('equipment_id', '<?php echo $hydrant['id']; ?>');

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
