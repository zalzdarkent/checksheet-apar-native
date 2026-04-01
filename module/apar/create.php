<!-- Modal Add APAR -->
<div class="modal fade" id="modal-add-apar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow border-0" style="border-radius: 12px;">
            <div class="modal-header pb-2" style="border-bottom: 1px solid #e9ecef;">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle text-primary me-2"></i> Tambah APAR Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-add-apar">
                <div class="modal-body py-3 px-4">
                    <div class="row g-3">
                        <!-- Area -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Area</label>
                            <select name="area" id="add-apar-area" class="form-select" required>
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
                            <input type="text" name="code" id="add-apar-code" class="form-control"
                                placeholder="Pilih area untuk generate kode" required readonly>
                            <small id="code-feedback" class="d-none mt-1 d-block"></small>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Location</label>
                            <input type="text" name="location" class="form-control"
                                placeholder="Contoh: Gedung A Lt. 1" required>
                        </div>

                        <!-- Weight -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Weight (Kg)</label>
                            <input type="text" name="weight" class="form-control"
                                placeholder="Contoh: 3" required>
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="Powder">Powder</option>
                                <option value="CO2">CO2</option>
                                <option value="Foam">Foam</option>
                            </select>
                        </div>

                        <!-- Expired Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expired Date</label>
                            <input type="date" name="expired_date" class="form-control" required>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="OK">OK</option>
                                <option value="Abnormal">Abnormal</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pt-2" style="border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-apar" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let checkTimeout;

    // Handle Area Change
    $('#add-apar-area').on('change', function() {
        const area = $(this).val();
        const codeInput = $('#add-apar-code');
        const feedback = $('#code-feedback');

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
                data: { area: area },
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

    // Real-time validation for Office
    $('#add-apar-code').on('input', function() {
        if ($('#add-apar-area').val() !== 'Office') return;

        const code = $(this).val();
        const feedback = $('#code-feedback');
        const input = $(this);

        clearTimeout(checkTimeout);
        if (code.length < 2) {
            feedback.addClass('d-none');
            input.removeClass('is-invalid is-valid');
            return;
        }

        checkTimeout = setTimeout(function() {
            $.ajax({
                url: 'actions/apar/ac_check_code_exists.php',
                type: 'GET',
                data: { code: code },
                dataType: 'json',
                success: function(response) {
                    feedback.removeClass('d-none');
                    if (response.exists) {
                        input.removeClass('is-valid').addClass('is-invalid');
                        feedback.removeClass('text-success').addClass('text-danger').html('<i class="fas fa-times-circle"></i> Kode sudah digunakan!');
                        $('#btn-submit-apar').prop('disabled', true);
                    } else {
                        input.removeClass('is-invalid').addClass('is-valid');
                        feedback.removeClass('text-danger').addClass('text-success').html('<i class="fas fa-check-circle"></i> Kode tersedia');
                        $('#btn-submit-apar').prop('disabled', false);
                    }
                }
            });
        }, 500);
    });

    // Handle Form Submit
    $('#form-add-apar').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-submit-apar');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: 'actions/ac_store_apar.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modal-add-apar'));
                    if (modal) modal.hide();
                    swal("Berhasil!", "Data APAR berhasil ditambahkan.", {
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
