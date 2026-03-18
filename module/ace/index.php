<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">APAR Management - ACE</h3>
            <!-- <h6 class="op-7 mb-2">Sistem Monitoring APAR & Hydrant</h6> -->
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
                    <h4 class="card-title">APAR Management - ACE</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="apar-ace-table" class="display table table-striped table-hover table-slim">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode APAR</th>
                                    <th>Lokasi</th>
                                    <th>Jenis APAR</th>
                                    <th>Tanggal Pemasangan</th>
                                    <th>Tanggal Kadaluarsa</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>APAR-001</td>
                                    <td>ACE</td>
                                    <td>ABC</td>
                                    <td>2022-01-01</td>
                                    <td>2023-01-01</td>
                                    <td>OK</td>
                                    <td>
                                        <a href="#" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <a href="#" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
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
        $("#apar-ace-table").DataTable({
            pageLength: 10,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on Action column (last column)
            ],
            language: {
                emptyTable: "Data tidak ditemukan"
            }
        });
    });
</script>