<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . str_replace("index.php", "", $_SERVER['PHP_SELF']);
?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Tambah Hydrant Baru</h3>
            <h6 class="op-7 mb-2">Registrasi unit Hydrant baru beserta inspeksi awal.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="?page=hydrant" class="btn btn-label-info btn-round me-2">Kembali ke List</a>
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

                <form id="form-create-hydrant" enctype="multipart/form-data">
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
                                            <label class="form-label fw-bold">Kode Hydrant</label>
                                            <input type="text" name="code" id="input-code" class="form-control"
                                                placeholder="Pilih area..." required readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold">Lokasi Spesifik</label>
                                            <input type="text" name="location" class="form-control"
                                                placeholder="Contoh: Belakang Gedung B" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Jenis Hydrant</label>
                                            <select name="type" id="input-type" class="form-select" required>
                                                <option value="">-- Pilih Jenis --</option>
                                                <option value="Indoor">Indoor</option>
                                                <option value="Outdoor">Outdoor</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">PIC Bertanggung Jawab</label>
                                            <select name="pic_empid" id="input-pic" class="form-select select2"
                                                required>
                                                <option value="">-- Loading Users... --</option>
                                            </select>
                                            <small class="text-muted" id="pic-info">Pilih area untuk saran PIC.</small>
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
                                <strong>Penting:</strong> Harap lATIkan pengecekan kondisi fisik Hydrant sebelum
                                disimpan ke sistem.
                            </div>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Foto Unit Hydrant (Wajib)</label>
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
                                            ['key' => 'body_hydrant', 'name' => 'Body Hydrant', 'type' => 'both'],
                                            ['key' => 'selang', 'name' => 'Selang', 'type' => 'both'],
                                            ['key' => 'couple_join', 'name' => 'Couple Join', 'type' => 'both'],
                                            ['key' => 'nozzle', 'name' => 'Nozzle', 'type' => 'both'],
                                            ['key' => 'check_sheet', 'name' => 'Check Sheet', 'type' => 'both'],
                                            ['key' => 'valve_kran', 'name' => 'Valve Kran', 'type' => 'indoor'],
                                            ['key' => 'lampu', 'name' => 'Lampu', 'type' => 'indoor'],
                                            ['key' => 'cover_lampu', 'name' => 'Cover Lampu', 'type' => 'indoor'],
                                            ['key' => 'kunci_pilar_hydrant', 'name' => 'Kunci Pilar Hydrant', 'type' => 'outdoor'],
                                            ['key' => 'pilar_hydrant', 'name' => 'Pilar Hydrant', 'type' => 'outdoor'],
                                            ['key' => 'marking', 'name' => 'Marking', 'type' => 'both'],
                                            ['key' => 'sign_larangan', 'name' => 'Sign Larangan', 'type' => 'both'],
                                            ['key' => 'nomor_hydrant', 'name' => 'Nomor Hydrant', 'type' => 'both'],
                                            ['key' => 'wi_hydrant', 'name' => 'WI Hydrant', 'type' => 'both'],
                                        ];
                                        foreach ($items as $i => $item): ?>
                                            <tr class="inspection-row" data-type="<?= $item['type'] ?>">
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td>
                                                    <?= $item['name'] ?>
                                                    <span class="row-required text-danger">*</span>
                                                    <div class="row-badge mt-1">
                                                        <?php if ($item['type'] === 'indoor'): ?>
                                                            <span class="badge bg-secondary" style="font-size: 0.7em;">Khusus
                                                                Indoor</span>
                                                        <?php elseif ($item['type'] === 'outdoor'): ?>
                                                            <span class="badge bg-secondary" style="font-size: 0.7em;">Khusus
                                                                Outdoor</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <input type="radio" class="btn-check" name="<?= $item['key'] ?>_ok"
                                                            id="<?= $item['key'] ?>_ok_<?= $i ?>" value="1" checked>
                                                        <label class="btn btn-outline-success"
                                                            for="<?= $item['key'] ?>_ok_<?= $i ?>">OK</label>
                                                        <input type="radio" class="btn-check" name="<?= $item['key'] ?>_ok"
                                                            id="<?= $item['key'] ?>_abnormal_<?= $i ?>" value="0">
                                                        <label class="btn btn-outline-danger"
                                                            for="<?= $item['key'] ?>_abnormal_<?= $i ?>">Abnormal</label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="file" name="<?= $item['key'] ?>_foto"
                                                        class="form-control form-control-sm inspection-file"
                                                        accept="image/*" required>
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

                if (filterArea && u.hydrant_locations) { // Use hydrant_locations
                    const locations = u.hydrant_locations.split(',');
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
                $.get('actions/apar/ac_get_next_code.php', { area: area, type: 'hydrant' }, function (res) {
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
            const required = ['area', 'code', 'location', 'type', 'pic_empid', 'x_coordinate', 'y_coordinate'];
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

            // Toggle Inspection Items based on Hydrant Type
            const hType = $('#input-type').val().toLowerCase(); // indoor or outdoor
            $('.inspection-row').each(function () {
                const rowType = $(this).data('type');
                const isRelevant = (rowType === 'both' || rowType === hType);

                if (isRelevant) {
                    $(this).removeClass('opacity-50 bg-light').find('input').prop('disabled', false);
                    $(this).find('.row-badge').hide();
                    $(this).find('.row-required').show();
                    $(this).find('.inspection-file').prop('required', true);
                } else {
                    $(this).addClass('opacity-50 bg-light').find('input').prop('disabled', true);
                    $(this).find('.row-badge').show();
                    $(this).find('.row-required').hide();
                    $(this).find('.inspection-file').prop('required', false);
                }
            });

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
        $('#form-create-hydrant').on('submit', function (e) {
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
                    formData.append('device_type', 'hydrant');

                    $.ajax({
                        url: 'actions/ac_store_new_equipment.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (res) {
                            if (res.success) {
                                swal("Berhasil!", "Unit Hydrant baru dan inspeksi awal berhasil disimpan.", "success")
                                    .then(() => {
                                        const area = $('#input-area').val().toLowerCase();
                                        window.location.href = '?page=hydrant-' + area;
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