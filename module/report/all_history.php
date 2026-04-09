<?php
// module/report/all_history.php
?>

<div class="page-inner">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-history me-2 text-primary"></i>Log Temuan Abnormal</h3>
            <p class="text-muted small">Daftar seluruh riwayat temuan abnormal (NG) dari unit APAR & Hydrant.</p>
        </div>
        <button onclick="history.back()" class="btn btn-sm btn-light border shadow-sm">Back</button>
    </div>

    <!-- Custom Search -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                <div class="card-body p-2 d-flex align-items-center">
                    <i class="fas fa-search text-muted ms-2 me-2"></i>
                    <input type="text" id="custom-search" class="form-control form-control-sm border-0 shadow-none"
                        placeholder="Cari Kode, Area, atau Masalah...">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills nav-secondary mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-apar-tab" data-bs-toggle="pill" href="#pills-apar" role="tab" aria-controls="pills-apar" aria-selected="true">
                <i class="fas fa-fire-extinguisher me-2"></i>APAR History
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-hydrant-tab" data-bs-toggle="pill" href="#pills-hydrant" role="tab" aria-controls="pills-hydrant" aria-selected="false">
                <i class="fas fa-shield-alt me-2"></i>Hydrant History
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
        <!-- APAR Tab -->
        <div class="tab-pane fade show active" id="pills-apar" role="tabpanel" aria-labelledby="pills-apar-tab">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-apar-history" class="table table-hover w-100">
                            <thead class="bg-light text-dark fw-bold">
                                <tr>
                                    <th>Waktu Temuan</th>
                                    <th>Unit</th>
                                    <th>Area</th>
                                    <th>Masalah (Abnormal Case)</th>
                                    <th>Penanganan (Countermeasure)</th>
                                    <th>PIC</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Hydrant Tab -->
        <div class="tab-pane fade" id="pills-hydrant" role="tabpanel" aria-labelledby="pills-hydrant-tab">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-hydrant-history" class="table table-hover w-100">
                            <thead class="bg-light text-dark fw-bold">
                                <tr>
                                    <th>Waktu Temuan</th>
                                    <th>Unit</th>
                                    <th>Area</th>
                                    <th>Masalah (Abnormal Case)</th>
                                    <th>Penanganan (Countermeasure)</th>
                                    <th>PIC</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var commonColumns = [
            {
                "data": "created_at_fmt",
                "render": function (data) {
                    return '<span class="fw-bold small">' + data + '</span>';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var icon = row.type === 'apar' ? 'fa-fire-extinguisher text-danger' : 'fas fa-shield-alt text-info';
                    var detailPage = row.type === 'apar' ? 'apar-detail' : 'hydrant-detail';
                    return '<div class="d-flex align-items-center">' +
                        '<i class="fas ' + icon + ' me-2" style="width:15px"></i>' +
                        '<a href="?page=' + detailPage + '&id=' + row.unit_id + '" class="fw-bold text-primary"><u>' + row.code + '</u></a>' +
                        '</div>';
                }
            },
            { "data": "area" },
            {
                "data": "abnormal_case",
                "render": function (data) {
                    return '<span class="text-danger fw-bold">' + data + '</span>';
                }
            },
            {
                "data": "countermeasure",
                "render": function (data) {
                    return data ? '<span class="small">' + data + '</span>' : '<span class="text-muted italic">-</span>';
                }
            },
            { "data": "pic_name" },
            {
                "data": "status",
                "render": function (data) {
                    var bClass = 'bg-secondary';
                    if (data === 'Open') bClass = 'bg-danger';
                    else if (data === 'On Progress') bClass = 'bg-warning text-dark';
                    else if (data === 'Closed') bClass = 'bg-info';
                    else if (data === 'Verified') bClass = 'bg-success';
                    return '<span class="badge ' + bClass + '">' + data + '</span>';
                }
            },
            {
                "data": null,
                "className": "text-center",
                "render": function (data, type, row) {
                    return '<a href="?page=' + (row.type === 'apar' ? 'apar-detail' : 'hydrant-detail') + '&id=' + row.unit_id + '" class="btn btn-sm btn-icon btn-round btn-light border" title="Lihat Unit"><i class="fas fa-eye"></i></a>';
                }
            }
        ];

        var tableApar = $('#table-apar-history').DataTable({
            "ajax": {
                "url": "actions/report/ac_get_all_history.php",
                "dataSrc": function(json) {
                    return json.data.filter(function(item) { return item.type === 'apar'; });
                }
            },
            "columns": commonColumns,
            "order": [[0, "desc"]],
            "pageLength": 10,
            "dom": 'lrtip',
            "language": {
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ temuan",
                "paginate": { "next": "Lanjut", "previous": "Kembali" }
            }
        });

        var tableHydrant = $('#table-hydrant-history').DataTable({
            "ajax": {
                "url": "actions/report/ac_get_all_history.php",
                "dataSrc": function(json) {
                    return json.data.filter(function(item) { return item.type === 'hydrant'; });
                }
            },
            "columns": commonColumns,
            "order": [[0, "desc"]],
            "pageLength": 10,
            "dom": 'lrtip',
            "language": {
                "lengthMenu": "_MENU_",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ temuan",
                "paginate": { "next": "Lanjut", "previous": "Kembali" }
            }
        });

        // Custom search sync
        $('#custom-search').keyup(function () {
            tableApar.search($(this).val()).draw();
            tableHydrant.search($(this).val()).draw();
        });

        // Re-adjust columns on tab change to prevent layout issues
        $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });
    });
</script>
<style>
    .extra-small { font-size: 0.75rem; }
    .italic { font-style: italic; }
    .nav-pills.nav-secondary .nav-link.active {
        background: #1572e8 !important;
        box-shadow: 0 4px 10px rgba(21, 114, 232, 0.3);
    }
</style>