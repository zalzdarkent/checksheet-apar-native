<div class="page-inner">
    <style>
        .filter-bar {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 0;
            white-space: nowrap;
        }
        .form-control-sm {
            height: 35px;
        }
        .btn-action-group {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
        }
        .status-ok { background: #27ae60; color: #fff; }
        .status-abnormal { background: #e74c3c; color: #fff; }
        
        .breadcrumb-item a { color: #3498db; }
        .breadcrumb-item.active { color: #666; }
        
        /* Dark mode support if applicable */
        [data-background-color="dark"] .filter-bar {
            background: #1a2035;
            color: #fff;
        }
        [data-background-color="dark"] .filter-label {
            color: #fff;
        }
    </style>

    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="?page=dashboard">E-Checksheet</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        All list APAR
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

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">Area:</label>
            <select id="filter-area" class="form-select form-control-sm" style="width: 150px;">
                <option value="All Areas">All Areas</option>
                <option value="Ace">Ace</option>
                <option value="Machining">Machining</option>
                <option value="Office">Office</option>
                <option value="Disa">Disa</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Search:</label>
            <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Code/Location..." style="width: 200px;">
        </div>
        <button id="btn-filter" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> Filter
        </button>

        <div class="btn-action-group">
            <button class="btn btn-warning btn-sm text-white">
                <i class="fas fa-qrcode"></i> Scan
            </button>
            <button class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">APAR Inventory List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="report-table" class="display table table-striped table-hover table-slim w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Code</th>
                                    <th>Area</th>
                                    <th>Location</th>
                                    <th>Weight</th>
                                    <th>Exp Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const table = $("#report-table").DataTable({
            pageLength: 10,
            responsive: true,
            ajax: {
                url: 'actions/apar/ac_all_apar.php',
                dataSrc: '',
                data: function(d) {
                    d.area = $('#filter-area').val();
                    d.q = $('#filter-search').val();
                    d.ajax = 1;
                }
            },
            columns: [
                { 
                    data: null, 
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'code' },
                { data: 'area' },
                { data: 'location' },
                { 
                    data: 'weight',
                    render: function(data) {
                        return data ? data + ' Kg' : '-';
                    }
                },
                { data: 'expired_date' },
                { 
                    data: 'status',
                    render: function(data) {
                        const badgeClass = data === 'OK' ? 'status-ok' : 'status-abnormal';
                        return `<span class="status-badge ${badgeClass}">${data}</span>`;
                    }
                },
                { 
                    data: 'id',
                    orderable: false,
                    render: function(data) {
                        return `
                            <button class="btn btn-info btn-xs btn-view" data-id="${data}">
                                <i class="fas fa-eye"></i> View
                            </button>
                        `;
                    }
                }
            ],
            language: {
                emptyTable: "Data tidak ditemukan",
                processing: '<div class="spinner-border text-primary" role="status"></div>'
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });

        // Custom filter button trigger
        $('#btn-filter').on('click', function() {
            table.ajax.reload();
        });

        // Also trigger on area change
        $('#filter-area').on('change', function() {
            table.ajax.reload();
        });

        // Handle Search input Enter key
        $('#filter-search').on('keypress', function(e) {
            if(e.which == 13) {
                table.ajax.reload();
            }
        });

        // View action (placeholder)
        $('#report-table').on('click', '.btn-view', function() {
            const id = $(this).data('id');
            alert('Viewing details for APAR ID: ' + id);
        });
    });
</script>