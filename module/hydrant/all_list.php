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

        .btn-action-group {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
        }

        .status-ok {
            background: #27ae60;
            color: #fff;
        }

        .status-abnormal {
            background: #e74c3c;
            color: #fff;
        }

        .breadcrumb-item a {
            color: #3498db;
        }

        .breadcrumb-item.active {
            color: #666;
        }

        /* Dark mode support if applicable */
        [data-background-color="dark"] .filter-bar {
            background: #1a2035;
            color: #fff;
        }

        [data-background-color="dark"] .filter-label {
            color: #fff;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: #3498db;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s;
            opacity: 0;
            visibility: hidden;
            border: none;
            outline: none;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #2980b9;
            transform: scale(1.1);
            color: #fff;
        }
    </style>

    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="?page=dashboard">E-Checksheet</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        All list Hydrant
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

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">Area:</label>
            <select id="filter-area" class="form-select form-control-sm" style="width: 150px;">
                <option value="All Areas">All Areas</option>
                <option value="Ace">Ace</option>
                <option value="Machining">Machining</option>
                <option value="Office">Office</option>
                <option value="Disa">Disa</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Search:</label>
            <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Code/Location..."
                style="width: 200px;">
        </div>
        <button id="btn-filter" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="btn-action-group">
            <button class="btn btn-primary btn-sm" id="btn-add-hydrant">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Hydrant Inventory List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="all-hydrant-table" class="display table table-striped table-hover table-slim w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Code</th>
                                    <th>Area</th>
                                    <th>Location</th>
                                    <th>Status Asset</th>
                                    <th>Status Insp</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<script>
    $(document).ready(function () {
        const table = $("#all-hydrant-table").DataTable({
            pageLength: 10,
            responsive: true,
            ajax: {
                url: 'actions/ac_all_hydrant.php',
                dataSrc: '',
                data: function (d) {
                    d.area = $('#filter-area').val();
                    d.q = $('#filter-search').val();
                }
            },
            createdRow: function (row, data, dataIndex) {
                if (data.is_active == 0) {
                    $(row).addClass('inactive');
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'code' },
                { data: 'area' },
                { data: 'location' },
                {
                    render: function (data, type, row) {
                        const badgeClass = (data === 'OK' || data === 'Good') ? 'status-ok' : 'status-abnormal';
                        return `<span class="status-badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'is_active',
                    render: function (data) {
                        const badgeClass = data == 1 ? 'badge-success' : 'badge-secondary';
                        const text = data == 1 ? 'Active' : 'Inactive';
                        return `<span class="badge ${badgeClass}">${text}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    render: function (data) {
                        return `
                            <a href="?page=hydrant-detail&id=${data}" class="btn btn-info btn-xs">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn btn-warning btn-xs btn-print-qr" data-id="${data}">
                                <i class="fas fa-print"></i> Print
                            </button>
                        `;
                    }
                }
            ],
            language: {
                emptyTable: "Data tidak ditemukan",
                processing: '<div class="spinner-border text-primary" role="status"></div>'
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });

        // Custom filter button trigger
        $('#btn-filter').on('click', function () {
            table.ajax.reload();
        });

        // Also trigger on area change
        $('#filter-area').on('change', function () {
            table.ajax.reload();
        });

        // Handle Search input Enter key
        $('#filter-search').on('keypress', function (e) {
            if (e.which == 13) {
                table.ajax.reload();
            }
        });

        // Print QR Code
        $('#all-hydrant-table').on('click', '.btn-print-qr', function () {
            const id = $(this).data('id');
            window.open('print_qr.php?type=hydrant&ids=' + id, '_blank');
        });

        // Back to Top Logic
        const backToTop = $('#back-to-top');
        $(window).scroll(function () {
            if ($(window).scrollTop() > 300) {
                backToTop.addClass('show');
            } else {
                backToTop.removeClass('show');
            }
        });

        backToTop.on('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Add button click handler
        $('#btn-add-hydrant').on('click', function () {
            var modal = new bootstrap.Modal(document.getElementById('modal-add-hydrant'));
            modal.show();
        });
    });
</script>

<!-- Modal Add Hydrant -->
<div class="modal fade" id="modal-add-hydrant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header pb-2" style="border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Hydrant Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-hydrant">
                <div class="modal-body py-3 px-4">
                    <div class="row g-3">
                        <!-- Area -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Area</label>
                            <select name="area" id="add-hydrant-area" class="form-select" required>
                                <option value="">-- Pilih Area --</option>
                                <option value="Ace">Ace</option>
                                <option value="Disa">Disa</option>
                                <option value="Machining">Machining</option>
                                <option value="Office">Office</option>
                            </select>
                        </div>

                        <!-- Code -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Code</label>
                            <input type="text" name="code" id="add-hydrant-code" class="form-control"
                                placeholder="Pilih area untuk generate kode" required readonly>
                            <small id="hydrant-code-feedback" class="d-none mt-1 d-block"></small>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control"
                                placeholder="Contoh: Gedung A Lt. 1" required>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Hydrant</label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Indoor">Indoor</option>
                                <option value="Outdoor">Outdoor</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="OK">Good</option>
                                <option value="Abnormal">Abnormal</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pt-2" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-hydrant" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let hydrantCheckTimeout;

    // Handle Area Change
    $('#add-hydrant-area').on('change', function() {
        const area = $(this).val();
        const codeInput = $('#add-hydrant-code');
        const feedback = $('#hydrant-code-feedback');

        feedback.addClass('d-none');
        codeInput.removeClass('is-invalid is-valid');

        if (area === 'Office') {
            codeInput.prop('readonly', false).val('').attr('placeholder', 'Masukkan kode manual...');
            codeInput.focus();
        } else if (area) {
            codeInput.prop('readonly', true).val('Generating...').attr('placeholder', '');

            $.ajax({
                url: 'actions/apar/ac_get_next_code.php',
                type: 'GET',
                data: { area: area, type: 'hydrant' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        codeInput.val(response.next_code);
                    } else {
                        codeInput.val('');
                        $.notify({ icon: 'fas fa-exclamation-triangle', message: 'Gagal generate kode: ' + response.message }, { type: 'danger' });
                    }
                }
            });
        } else {
            codeInput.prop('readonly', true).val('').attr('placeholder', 'Pilih area untuk generate kode');
        }
    });

    // Real-time validation for Office area
    $('#add-hydrant-code').on('input', function() {
        if ($('#add-hydrant-area').val() !== 'Office') return;

        const code = $(this).val();
        const feedback = $('#hydrant-code-feedback');
        const input = $(this);

        clearTimeout(hydrantCheckTimeout);
        if (code.length < 2) {
            feedback.addClass('d-none');
            input.removeClass('is-invalid is-valid');
            return;
        }

        hydrantCheckTimeout = setTimeout(function() {
            $.ajax({
                url: 'actions/apar/ac_check_code_exists.php',
                type: 'GET',
                data: { code: code, type: 'hydrant' },
                dataType: 'json',
                success: function(response) {
                    feedback.removeClass('d-none');
                    if (response.exists) {
                        input.removeClass('is-valid').addClass('is-invalid');
                        feedback.removeClass('text-success').addClass('text-danger').html('<i class="fas fa-times-circle"></i> Kode sudah digunakan!');
                        $('#btn-submit-hydrant').prop('disabled', true);
                    } else {
                        input.removeClass('is-invalid').addClass('is-valid');
                        feedback.removeClass('text-danger').addClass('text-success').html('<i class="fas fa-check-circle"></i> Kode tersedia');
                        $('#btn-submit-hydrant').prop('disabled', false);
                    }
                }
            });
        }, 500);
    });

    // Handle Form Submit
    $('#form-add-hydrant').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-submit-hydrant');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'actions/hydrant/ac_store_hydrant.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modal-add-hydrant'));
                    if (modal) modal.hide();
                    
                    // Reset form
                    $('#form-add-hydrant')[0].reset();
                    $('#add-hydrant-code').val('');
                    $('#hydrant-code-feedback').addClass('d-none');
                    
                    swal("Berhasil!", "Data Hydrant berhasil ditambahkan.", {
                        icon: "success",
                        buttons: { confirm: { className: 'btn btn-success' } }
                    }).then(() => { 
                        // Reload the DataTable instead of full page reload
                        $('#all-hydrant-table').DataTable().ajax.reload();
                    });
                } else {
                    btn.prop('disabled', false).html(originalText);
                    $.notify({ icon: 'fas fa-exclamation-triangle', message: 'Error: ' + response.message }, { type: 'danger' });
                }
            },
            error: function() {
                btn.prop('disabled', false).html(originalText);
                $.notify({ icon: 'fas fa-exclamation-triangle', message: 'Terjadi kesalahan sistem!' }, { type: 'danger' });
            }
        });
    });
});
</script>