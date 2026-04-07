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
                    <span class="badge bg-primary"><i class="fas fa-lock"></i> Tersentralisasi via DB HRD</span>
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
                                    <th>PIC APAR</th>
                                    <th>PIC Hydrant</th>
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
                                            <?php 
                                            if(!empty($user['pic_apar_location'])) {
                                                foreach(explode(',', $user['pic_apar_location']) as $l) {
                                                    echo '<span class="badge badge-danger mb-1" style="font-size: 0.7rem;">'.$l.'</span> ';
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if(!empty($user['pic_hydrant_location'])) {
                                                foreach(explode(',', $user['pic_hydrant_location']) as $l) {
                                                    echo '<span class="badge badge-primary mb-1" style="font-size: 0.7rem;">'.$l.'</span> ';
                                                }
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($user['is_active'] == 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm btn-set-pic" 
                                                data-id="<?php echo $user['id']; ?>" 
                                                data-name="<?php echo $user['name']; ?>"
                                                data-apar="<?php echo $user['pic_apar_location']; ?>"
                                                data-hydrant="<?php echo $user['pic_hydrant_location']; ?>">
                                                <i class="fas fa-user-shield"></i> Set PIC
                                            </button>
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

    // Handle Set PIC button click using event delegation (for DataTable paging)
    $(document).on('click', '.btn-set-pic', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const aparLocs = String($(this).data('apar') || "").split(',');
        const hydrantLocs = String($(this).data('hydrant') || "").split(',');

        $('#pic-user-id').val(id);
        $('#pic-user-name').text(name);

        // Reset and Set Checkboxes for APAR
        $('input[name="pic_apar_location[]"]').prop('checked', false);
        aparLocs.forEach(loc => {
            if(loc) $(`input[name="pic_apar_location[]"][value="${loc}"]`).prop('checked', true);
        });

        // Reset and Set Checkboxes for Hydrant
        $('input[name="pic_hydrant_location[]"]').prop('checked', false);
        hydrantLocs.forEach(loc => {
            if(loc) $(`input[name="pic_hydrant_location[]"][value="${loc}"]`).prop('checked', true);
        });

        $('#modal-set-pic').modal('show');
    });
</script>

<!-- Modal Set PIC -->
<div class="modal fade" id="modal-set-pic" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set PIC Lokasi: <span id="pic-user-name" class="fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-set-pic" method="POST" action="actions/user/ac_user.php">
                <input type="hidden" name="action" value="set_pic">
                <input type="hidden" name="id" id="pic-user-id">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2"><i class="fas fa-fire-extinguisher text-danger me-2"></i> PIC Lokasi APAR</label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded border">
                            <?php foreach(['Ace', 'Disa', 'Machining', 'Office'] as $loc): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pic_apar_location[]" value="<?= $loc ?>" id="apar_<?= $loc ?>">
                                <label class="form-check-label" for="apar_<?= $loc ?>"><?= $loc ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block mb-2"><i class="fas fa-faucet text-primary me-2"></i> PIC Lokasi Hydrant</label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded border">
                            <?php foreach(['Ace', 'Disa', 'Machining', 'Office'] as $loc): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pic_hydrant_location[]" value="<?= $loc ?>" id="hydrant_<?= $loc ?>">
                                <label class="form-check-label" for="hydrant_<?= $loc ?>"><?= $loc ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

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