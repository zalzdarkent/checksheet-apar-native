<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Tambah APAR Baru</h3>
            <h6 class="op-7 mb-2">Registrasi unit APAR baru beserta inspeksi awal.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="javascript:history.back()" class="btn btn-label-info btn-round me-2">
                Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-wizard">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between">
                        <div class="wizard-step active" id="step-1-header">
                            <div class="step-icon"><i class="fas fa-edit"></i></div>
                            <p>Data & Lokasi</p>
                        </div>
                        <div class="wizard-step" id="step-2-header">
                            <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                            <p>Inspeksi Awal</p>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 4px;">
                        <div id="wizard-progress" class="progress-bar bg-primary" role="progressbar" style="width: 50%"
                            aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <form id="form-create-apar" enctype="multipart/form-data">
                    <!-- Step 1: Data & Lokasi -->
                    <div class="wizard-content active" id="step-1-content">
                        <div class="card-body">
                            <div class="row">
                                <!-- Form Left -->
                                <div class="col-lg-5">
                                    <h4 class="fw-bold mb-4">Informasi Perangkat</h4>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Area</label>
                                            <select name="area" id="input-area" class="form-select" required>
                                                <option value="">-- Pilih Area --</option>
                                                <option value="Ace">Ace</option>
                                                <option value="Disa">Disa</option>
                                                <option value="Machining">Machining</option>
                                                <option value="Office">Office</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Kode APAR</label>
                                            <input type="text" name="code" id="input-code" class="form-control"
                                                placeholder="Pilih area..." required readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Lokasi Spesifik</label>
                                            <input type="text" name="location" class="form-control"
                                                placeholder="Contoh: Depan Pintu Gudang A" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Tipe APAR</label>
                                            <select name="type" class="form-select" required>
                                                <option value="">-- Pilih Tipe --</option>
                                                <option value="Powder">Powder</option>
                                                <option value="CO2">CO2</option>
                                                <option value="Foam">Foam</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Berat (Kg)</label>
                                            <input type="number" step="0.1" name="weight" class="form-control"
                                                placeholder="3.5" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">PIC Bertanggung Jawab</label>
                                            <select name="pic_empid" id="input-pic" class="form-select select2"
                                                required>
                                                <option value="">-- Loading Users... --</option>
                                            </select>
                                            <small class="text-muted" id="pic-info">Pilih area untuk saran PIC.</small>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold">Expired Date</label>
                                            <input type="date" name="expired_date" class="form-control" required>
                                        </div>

                                        <input type="hidden" name="x_coordinate" id="x_coord" required>
                                        <input type="hidden" name="y_coordinate" id="y_coord" required>
                                    </div>
                                </div>

                                <!-- Map Right -->
                                <div class="col-lg-7">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="fw-bold mb-0">Plot Lokasi pada Peta</h4>
                                        <span class="badge bg-info">Klik pada gambar untuk menentukan posisi</span>
                                    </div>
                                    <div class="map-picker-wrapper border rounded bg-light p-2 position-relative"
                                        style="min-height: 450px; overflow: auto; max-height: 600px;">
                                        <div id="map-picker-container" class="position-relative mx-auto"
                                            style="cursor: crosshair;">
                                            <img src="assets/img/ati-layout.jpeg" alt="Layout Map"
                                                style="width: 100%; display: block;">
                                            <div id="marker-picker" class="d-none animate__animated animate__bounceIn">
                                                <i class="fas fa-map-marker-alt text-danger"
                                                    style="font-size: 24px; position: absolute; transform: translate(-50%, -100%);"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="button" class="btn btn-primary btn-lg" id="btn-next-step">
                                Selanjutnya: Inspeksi <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Inspeksi Awal -->
                    <div class="wizard-content" id="step-2-content" style="display: none;">
                        <div class="card-body">
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Penting:</strong> Harap lakukan pengecekan kondisi fisik APAR sebelum disimpan
                                ke sistem.
                            </div>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Foto Unit APAR (Wajib)</label>
                                    <input type="file" name="unit_photo" class="form-control" accept="image/*" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Catatan Umum</label>
                                    <textarea name="general_notes" class="form-control" rows="1"
                                        placeholder="Opsional..."></textarea>
                                </div>
                            </div>

                            <div class="table-responsive mt-4">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 5%">No</th>
                                            <th>Item Pemeriksaan</th>
                                            <th style="width: 15%">Hasil</th>
                                            <th style="width: 30%">Foto Evidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $items = [
                                            ['key' => 'exp_date', 'name' => 'Exp. Date (Maksimal 4 Tahun)', 'req' => true],
                                            ['key' => 'pressure', 'name' => 'Pressure (Zona Hijau)', 'req' => true],
                                            ['key' => 'weight_co2', 'name' => 'Weight CO2', 'req' => true],
                                            ['key' => 'tube', 'name' => 'Tube', 'req' => false],
                                            ['key' => 'hose', 'name' => 'Hose', 'req' => false],
                                            ['key' => 'bracket', 'name' => 'Bracket (Tidak Rusak)', 'req' => false],
                                            ['key' => 'wi', 'name' => 'WI (Ada & Terbaca)', 'req' => false],
                                            ['key' => 'form_kejadian', 'name' => 'Form Kejadian (Ada)', 'req' => false],
                                            ['key' => 'sign_box', 'name' => 'SIGN Kotak (Ada)', 'req' => false],
                                            ['key' => 'sign_triangle', 'name' => 'SIGN Segitiga (Ada)', 'req' => false],
                                            ['key' => 'marking_tiger', 'name' => 'Marking Tiger (Ada)', 'req' => false],
                                            ['key' => 'marking_beam', 'name' => 'Marking Beam (Ada)', 'req' => false],
                                            ['key' => 'sr_apar', 'name' => '5R APAR (Bersih)', 'req' => false],
                                            ['key' => 'kocok_apar', 'name' => 'Kocok APAR (Lancar)', 'req' => false],
                                            ['key' => 'label', 'name' => 'Label (Ada/Terbaca)', 'req' => false],
                                        ];
                                        foreach ($items as $i => $item): ?>
                                            <tr>
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td>
                                                    <?= $item['name'] ?>
                                                    <?= $item['req'] ? '<span class="text-danger">*</span>' : '' ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <input type="radio" class="btn-check" name="<?= $item['key'] ?>_ok"
                                                            id="<?= $item['key'] ?>_ok" value="1" checked>
                                                        <label class="btn btn-outline-success"
                                                            for="<?= $item['key'] ?>_ok">OK</label>
                                                        <input type="radio" class="btn-check" name="<?= $item['key'] ?>_ok"
                                                            id="<?= $item['key'] ?>_abnormal" value="0">
                                                        <label class="btn btn-outline-danger"
                                                            for="<?= $item['key'] ?>_abnormal">Abnormal</label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($item['req']): ?>
                                                        <input type="file" name="<?= $item['key'] ?>_foto"
                                                            class="form-control form-control-sm" accept="image/*"
                                                            required>
                                                    <?php else: ?>
                                                        <div class="text-center text-muted small">-</div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary btn-lg" id="btn-prev-step">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-success btn-lg" id="btn-submit-all">
                                <i class="fas fa-save me-2"></i> Simpan Alat & Inspeksi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card-wizard .wizard-step {
        text-align: center;
        flex: 1;
        opacity: 0.4;
        transition: all 0.3s;
    }

    .card-wizard .wizard-step.active {
        opacity: 1;
    }

    .card-wizard .step-icon {
        width: 40px;
        height: 40px;
        line-height: 40px;
        background: #f0f0f0;
        border-radius: 50%;
        margin: 0 auto 10px;
        font-size: 18px;
    }

    .card-wizard .active .step-icon {
        background: #1572e8;
        color: white;
    }

    .card-wizard .wizard-step p {
        margin-bottom: 0;
        font-weight: 600;
        font-size: 14px;
    }

    #marker-picker {
        position: absolute;
        pointer-events: none;
    }
</style>

<script>
    $(document).ready(function () {
        let allUsers = [];

        // 1. Fetch Users & PICs
        $.get('actions/user/ac_get_users_pic.php', function (res) {
            if (res.success) {
                allUsers = res.data;

                // Check for area parameter in URL
                const urlParams = new URLSearchParams(window.location.search);
                const areaParam = urlParams.get('area');
                if (areaParam) {
                    $('#input-area').val(areaParam).trigger('change');
                } else {
                    renderUserDropdown();
                }
            }
        });

        function renderUserDropdown(filterArea = '') {
            const select = $('#input-pic');
            select.empty().append('<option value="">-- Pilih PIC --</option>');

            let foundPic = null;

            allUsers.forEach(u => {
                let label = u.name;
                let isAreaPic = false;

                if (filterArea && u.apar_locations) {
                    const locations = u.apar_locations.split(',');
                    if (locations.includes(filterArea)) {
                        label += ' [PIC Area]';
                        isAreaPic = true;
                        if (!foundPic) foundPic = u.empid;
                    }
                }

                select.append(`<option value="${u.empid}" ${u.empid == foundPic ? 'selected' : ''}>${label}</option>`);
            });

            if (foundPic) {
                $('#pic-info').html(`<span class="text-success fw-bold"><i class="fas fa-info-circle"></i> PIC disarankan otomatis untuk area ${filterArea}.</span>`);
            } else {
                $('#pic-info').text('Pilih area untuk saran PIC.');
            }
        }

        // 2. Handle Area Change -> Code & PIC
        $('#input-area').on('change', function () {
            const area = $(this).val();
            renderUserDropdown(area);

            // Generate Code
            if (area === 'Office') {
                $('#input-code').val('').prop('readonly', false).attr('placeholder', 'Masukkan kode manual...');
            } else if (area) {
                $('#input-code').val('Generating...').prop('readonly', true);
                $.get('actions/apar/ac_get_next_code.php', { area: area }, function (res) {
                    if (res.success) $('#input-code').val(res.next_code);
                });
            } else {
                $('#input-code').val('').prop('readonly', true).attr('placeholder', 'Pilih area...');
            }
        });

        // 3. Map Picker Logic
        $('#map-picker-container').on('click', function (e) {
            const offset = $(this).offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;

            const xPercent = (x / $(this).width() * 100).toFixed(2);
            const yPercent = (y / $(this).height() * 100).toFixed(2);

            $('#x_coord').val(xPercent);
            $('#y_coord').val(yPercent);

            $('#marker-picker').removeClass('d-none').css({
                left: xPercent + '%',
                top: yPercent + '%'
            });
        });

        // 4. Wizard Navigation
        $('#btn-next-step').on('click', function () {
            // Simple Validation Step 1
            const required = ['area', 'code', 'location', 'type', 'weight', 'pic_empid', 'expired_date', 'x_coordinate', 'y_coordinate'];
            let valid = true;

            required.forEach(name => {
                const input = $(`[name="${name}"]`);
                if (!input.val()) {
                    input.addClass('is-invalid');
                    valid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            if (!valid) {
                swal("Incomplete", "Mohon isi semua data dan tentukan lokasi di peta.", "warning");
                return;
            }

            $('#step-1-content').hide();
            $('#step-2-content').fadeIn();
            $('#step-1-header').removeClass('active');
            $('#step-2-header').addClass('active');
            $('#wizard-progress').css('width', '100%');
        });

        $('#btn-prev-step').on('click', function () {
            $('#step-2-content').hide();
            $('#step-1-content').fadeIn();
            $('#step-2-header').removeClass('active');
            $('#step-1-header').addClass('active');
            $('#wizard-progress').css('width', '50%');
        });

        // 5. Submit Action
        $('#form-create-apar').on('submit', function (e) {
            e.preventDefault();

            swal({
                title: "Simpan Unit Baru?",
                text: "Data alat dan inspeksi awal akan didaftarkan ke sistem.",
                icon: "info",
                buttons: ["Batal", "Ya, Simpan"],
            }).then((save) => {
                if (save) {
                    const btn = $('#btn-submit-all');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');

                    const formData = new FormData(this);
                    formData.append('device_type', 'apar');

                    $.ajax({
                        url: 'actions/ac_store_new_equipment.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            if (res.success) {
                                swal("Berhasil!", "Unit APAR baru dan inspeksi awal berhasil disimpan.", "success")
                                    .then(() => {
                                        const area = $('#input-area').val().toLowerCase();
                                        window.location.href = '?page=apar-' + area;
                                    });
                            } else {
                                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Alat & Inspeksi');
                                swal("Gagal", res.message, "error");
                            }
                        },
                        error: function () {
                            btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Simpan Alat & Inspeksi');
                            swal("Error", "Terjadi kesalahan pada server.", "error");
                        }
                    });
                }
            });
        });
    });
</script>