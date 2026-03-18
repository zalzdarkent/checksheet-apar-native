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
                                        <td><?php echo $user['name']; ?></td>
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
                                            <a href="#" class="btn btn-warning btn-sm text-white">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="#" class="btn btn-danger btn-sm text-white">
                                                <i class="fas fa-ban"></i> Nonaktif
                                            </a>
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