<?php
include("actions/user/ac_user.php");

$getAllUsers = get_all_users();

?>

<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="?page=dashboard">E-Checksheet</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        User Management
                    </li>
                </ol>
            </nav>
    </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">User Management</h4>
                    <a href="?page=add-user" class="btn btn-info btn-sm">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="user-management" class="display table table-striped table-hover table-slim">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NPK</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($getAllUsers as $user) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <?php 
                                                    $photoPath = !empty($user['photo']) && file_exists('storage/users/' . $user['photo']) 
                                                                ? 'storage/users/' . $user['photo'] 
                                                                : 'assets/img/placeholder-profile.jpg';
                                                    ?>
                                                    <img src="<?php echo $photoPath; ?>" alt="Profile" class="avatar-img rounded-circle" style="object-fit:cover;">
                                                </div>
                                                <div>
                                                    <?php echo $user['name']; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $user['npk']; ?></td>
                                        <td><?php echo $user['role']; ?></td>
                                        <td>
                                            <?php if ($user['is_active'] == 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?page=edit-user&id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm text-white">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php if ($user['is_active'] == 1): ?>
                                                <button class="btn btn-danger btn-sm text-white" onclick="set_status(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>', 'nonaktifkan')">
                                                    <i class="fas fa-ban"></i> Nonaktif
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-sm text-white" onclick="set_status(<?php echo $user['id']; ?>, '<?php echo $user['name']; ?>', 'aktifkan')">
                                                    <i class="fas fa-check-circle"></i> Aktifkan
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Javascript logic datatables -->
<script>
    $(document).ready(function () {
        $("#user-management").DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 }
            ],
            language: {
                emptyTable: "Data tidak ditemukan"
            }
        });
    });

    function set_status(id, name, action) {
        let title = action === 'nonaktifkan' ? "Nonaktifkan User?" : "Aktifkan User?";
        let text = action === 'nonaktifkan' ? "Apakah anda yakin ingin menonaktifkan " + name + "?" : "Apakah anda yakin ingin mengaktifkan " + name + "?";
        let icon = action === 'nonaktifkan' ? "warning" : "info";
        let confirmBtnColor = action === 'nonaktifkan' ? "#d33" : "#28a745";

        swal({
            title: title,
            text: text,
            icon: icon,
            buttons: {
                cancel: {
                    text: "Batal",
                    value: null,
                    visible: true,
                    className: "btn btn-danger"
                },
                confirm: {
                    text: "Ya, lanjutkan!",
                    value: true,
                    visible: true,
                    className: action === 'nonaktifkan' ? "btn btn-warning" : "btn btn-success"
                }
            }
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: "actions/user/ac_user.php",
                    type: "POST",
                    data: {
                        action: "set_status",
                        id: id
                    },
                    success: function (response) {
                        // Response success akan direload dan notify muncul dari session
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        swal("Ups!", "Terjadi kesalahan: " + error, "error");
                    }
                });
            }
        });
    }
</script>

<!-- notify alert -->
<?php if (isset($_SESSION['success'])): ?>
    <script>
        $.notify({
            message: "<?php echo $_SESSION['success']; ?>"
        }, {
            type: 'success',
            placement: {
                from: "top",
                align: "right"
            },
            delay: 3000
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        $.notify({
            message: "<?php echo $_SESSION['error']; ?>"
        }, {
            type: 'danger',
            placement: {
                from: "top",
                align: "right"
            },
            delay: 3000
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>