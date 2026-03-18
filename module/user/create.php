<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="?page=dashboard">E-Checksheet</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="?page=user-management">User Management</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Add User
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
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Add User</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger mb-3">
                            <?php echo $_SESSION['error']; ?>
                        </div>
                    <?php endif; ?>
                    <form id="formAddUser" action="actions/user/ac_user.php" method="POST" enctype="multipart/form-data">

                        <div class="row">
                            <!-- LEFT -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Name</label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="Masukkan nama lengkap" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label>NPK</label>
                                    <input type="text" class="form-control" name="npk" placeholder="Masukkan NPK"
                                        required>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Role</label>
                                    <select class="form-select" name="role" required>
                                        <option value="" disabled selected>Pilih</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Petugas">Petugas</option>
                                    </select>
                                </div>
                            </div>

                            <!-- RIGHT -->
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Password</label>
                                    <input type="password" class="form-control" name="password" id="passwordInput"
                                        placeholder="Masukkan password" required>
                                    <?php if (isset($_SESSION['error_password'])): ?>
                                        <span class="text-danger error-server"><?php echo $_SESSION['error_password']; ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Confirm Password</label>
                                    <input type="password"
                                        class="form-control <?php echo isset($_SESSION['error_password']) ? 'is-invalid' : ''; ?>"
                                        name="confirm_password" id="confirmPasswordInput" placeholder="Ulangi password" required>
                                    <span class="text-danger" id="passwordErrorMsg" style="display: none;">Password dan Confirm Password harus sama!</span>
                                    <?php if (isset($_SESSION['error_password'])): ?>
                                        <span class="text-danger error-server">
                                            <?php echo $_SESSION['error_password']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label>Foto Profile (Optional)</label>
                                    <input type="file" class="form-control" name="photo" id="photoInput"
                                        accept="image/*">
                                    <small class="text-muted">Upload foto untuk melihat preview</small>
                                </div>

                                <!-- Preview Avatar -->
                                <div class="text-center mt-3" id="previewContainer" style="display: none;">
                                    <img id="previewImage" class="rounded-circle shadow" width="120" height="120"
                                        style="object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save User
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Hapus session error setelah ditampilkan agar tidak menetap
unset($_SESSION['error_password']);
unset($_SESSION['error']);
?>

<script>
    document.getElementById('photoInput').addEventListener('change', function (event) {
        const file = event.target.files[0];
        const preview = document.getElementById('previewImage');
        const container = document.getElementById('previewContainer');

        if (file) {
            preview.src = URL.createObjectURL(file);
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    });

    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    const passwordErrorMsg = document.getElementById('passwordErrorMsg');
    const formAddUser = document.getElementById('formAddUser');

    function validatePassword() {
        const serverErrors = document.querySelectorAll('.error-server');
        if (serverErrors.length > 0) {
            serverErrors.forEach(el => el.style.display = 'none');
        }

        if (confirmPasswordInput.value === '') {
            passwordErrorMsg.style.display = 'none';
            confirmPasswordInput.classList.remove('is-invalid');
            return true;
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            passwordErrorMsg.style.display = 'block';
            confirmPasswordInput.classList.add('is-invalid');
            return false;
        } else {
            passwordErrorMsg.style.display = 'none';
            confirmPasswordInput.classList.remove('is-invalid');
            return true;
        }
    }

    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validatePassword);

    formAddUser.addEventListener('submit', function (e) {
        if (!validatePassword()) {
            e.preventDefault(); 
            confirmPasswordInput.focus();
        }
    });
</script>