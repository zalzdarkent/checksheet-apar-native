<style>
    /* Custom Slim Datatable for ACE */
    table.dataTable.table-slim > thead > tr > th,
    table.dataTable.table-slim > tbody > tr > td {
        padding: 6px 10px !important;
        font-size: 13px !important;
        vertical-align: middle !important;
    }
    table.dataTable.table-slim > thead > tr > th {
        padding-right: 25px !important;
    }
    table.dataTable.table-slim > thead > tr > th::before,
    table.dataTable.table-slim > thead > tr > th::after {
        bottom: 6px !important;
        right: 5px !important;
    }
    table.dataTable.table-slim .btn {
        padding: 3px 8px !important;
        font-size: 12px !important;
        line-height: 1.5;
    }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_paginate {
        font-size: 13px !important;
        margin-bottom: 8px;
    }
    table.dataTable.table-slim th {
        background-color: #f8f9fa !important;
    }
</style>

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