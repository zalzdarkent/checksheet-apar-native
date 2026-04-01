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
                    swal("Berhasil!", "Data Hydrant berhasil ditambahkan.", {
                        icon: "success",
                        buttons: { confirm: { className: 'btn btn-success' } }
                    }).then(() => { location.reload(); });
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
