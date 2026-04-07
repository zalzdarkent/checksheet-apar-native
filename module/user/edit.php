<?php
include("actions/user/ac_user.php");

$id = $_GET['id'];
$user = get_user_by_id($id);

?>

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
                        Edit User
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit User Profile</h4>
                    <!-- <span class="badge badge-primary">ID: #<?php echo $user['id']; ?></span> -->
                </div>
                <form id="formEditUser" action="actions/user/ac_user.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo $_SESSION['error']; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Header Section Area -->
                        <div class="row align-items-center mb-5 g-4">
                            <div class="col-12 col-md-auto text-center text-md-start">
                                <div class="avatar-wrapper d-inline-block position-relative">
                                    <?php
                                    $photoPath = !empty($user['photo']) && file_exists('storage/users/' . $user['photo'])
                                        ? 'storage/users/' . $user['photo']
                                        : 'assets/img/placeholder-profile.jpg';
                                    ?>
                                    <img src="<?php echo $photoPath; ?>" alt="Profile" id="previewImage"
                                        class="rounded-circle border border-4 border-white shadow-lg profile-avatar-img">

                                    <label for="photoInput" class="btn-camera-upload shadow-sm">
                                        <i class="fas fa-pen text-white"></i>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-md ps-md-4 text-center text-md-start">
                                <h1 class="fw-bold mb-1 text-dark"><?php echo $user['name']; ?></h1>
                                <div
                                    class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2 align-items-center mt-2">
                                    <?php if (!empty($user['created_at'])): ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-calendar-alt me-1"></i> Registered since
                                            <b><?php echo $user['created_at'] instanceof DateTime ? $user['created_at']->format('d M Y') : date('d M Y', strtotime($user['created_at'])); ?></b>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo $user['name']; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="npk">NPK</label>
                                    <input type="text" class="form-control" id="npk" name="npk"
                                        value="<?php echo $user['npk']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Hak Akses / Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>
                                            Admin</option>
                                        <option value="User" <?php echo $user['role'] === 'User' ? 'selected' : ''; ?>>
                                            User</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password">Password Baru <small class="text-muted">(Kosongkan jika tidak
                                            diganti)</small></label>
                                    <input type="password" class="form-control" id="passwordInput" name="password"
                                        placeholder="Masukkan password baru">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirmPasswordInput"
                                        name="confirm_password" placeholder="Ulangi password baru">
                                    <span class="text-danger" id="passwordErrorMsg" style="display: none;">Password
                                        tidak cocok!</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0" style="display:none;">
                            <input type="file" class="form-control" id="photoInput" name="photo"
                                accept=".png, .jpg, .jpeg">
                        </div>
                        <div id="photoErrorMsg" class="text-danger mt-1" style="display:none;"></div>
                        <?php if (isset($_SESSION['error_photo'])): ?>
                            <div class="text-danger mt-1"><?php echo $_SESSION['error_photo']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2 bg-light border-top-0">
                        <a href="?page=user-management" class="btn btn-light btn-round px-4 text-muted">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary btn-round px-5 shadow-sm transform-hover">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-avatar-img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .btn-camera-upload {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 38px;
        height: 38px;
        background: #1572e8;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        transition: all 0.2s ease;
        z-index: 5;
    }

    .btn-camera-upload:hover {
        background: #1266d4;
        transform: scale(1.1);
    }

    .transform-hover {
        transition: all 0.2s ease-in-out;
    }

    .transform-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(21, 114, 232, 0.2) !important;
    }

    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 0px 30px rgba(0, 0, 0, 0.05);
    }

    .card-body {
        padding: 2.5rem !important;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }

        .profile-avatar-img {
            width: 120px;
            height: 120px;
        }
    }
</style>

<?php
unset($_SESSION['error']);
unset($_SESSION['error_password']);
unset($_SESSION['error_photo']);
?>

<script>
    const photoInput = document.getElementById('photoInput');
    const previewImage = document.getElementById('previewImage');
    const photoErrorMsg = document.getElementById('photoErrorMsg');

    photoInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        photoErrorMsg.style.display = 'none';

        if (file) {
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                photoErrorMsg.textContent = 'Format file harus berupa PNG, JPG, atau JPEG!';
                photoErrorMsg.style.display = 'block';
                photoInput.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                photoErrorMsg.textContent = 'Ukuran file terlalu besar! Maksimal 5 MB.';
                photoErrorMsg.style.display = 'block';
                photoInput.value = '';
                return;
            }

            previewImage.src = URL.createObjectURL(file);
        }
    });

    const passwordInput = document.getElementById('passwordInput');
    const confirmPasswordInput = document.getElementById('confirmPasswordInput');
    const passwordErrorMsg = document.getElementById('passwordErrorMsg');
    const formEditUser = document.getElementById('formEditUser');

    function validatePassword() {
        if (passwordInput.value === '' && confirmPasswordInput.value === '') {
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

    formEditUser.addEventListener('submit', function (e) {
        if (!validatePassword()) {
            e.preventDefault();
            confirmPasswordInput.focus();
        }
    });
</script>