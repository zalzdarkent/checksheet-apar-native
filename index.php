<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<?php
include("components/fragments/head.php");
?>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php
        include("components/ui/sidebar.php");
        ?>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <?php
                    include("components/ui/logo.php");
                    ?>
                    <!-- End Logo Header -->
                </div>
                <!-- Navbar Header -->
                <?php
                include("components/ui/navbar.php");
                ?>
                <!-- End Navbar -->
            </div>

            <!-- Script JS -->
            <?php
            include("components/fragments/script.php");
            ?>
            <!-- End script JS -->

            <div class="container">
                <!-- content -->
                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                $file = "";

                $routes = [
                    'dashboard' => 'module/md_dashboard.php',
                    'apar-ace' => 'module/ace/index.php',
                    'hydrant-ace' => 'module/hydrant/ace/index.php',
                    'user-management' => 'module/user/index.php',
                    'add-user' => 'module/user/create.php',
                    'edit-user' => 'module/user/edit.php'
                ];

                if (array_key_exists($page, $routes)) {
                    $file = $routes[$page];
                } else {
                    $file = "module/md_" . $page . ".php";
                }

                if (file_exists($file)) {
                    include($file);
                } else {
                    echo "<h1>Halaman <b>" . htmlspecialchars($page) . "</b> tidak ditemukan ($file)</h1>";
                }
                ?>
                <!-- End content -->
            </div>

            <!-- footer -->
            <?php
            include("components/ui/footer.php");
            ?>
            <!-- End footer -->
        </div>
    </div>

    <!-- Global Map View Modal -->
    <div class="modal fade" id="globalMapViewModal" tabindex="-1" aria-labelledby="globalMapViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="globalMapViewModalLabel">Global Monitoring Map</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav tabs -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="nav btn-group" id="globalMapTabs" role="tablist">
                            <button class="btn btn-outline-primary active btn-sm" id="global-map-tab" data-bs-toggle="tab" data-bs-target="#global-map-view" type="button" role="tab" aria-controls="global-map-view" aria-selected="true" style="padding: 5px 15px; font-size: 13px;">
                                <i class="fas fa-map-marked-alt me-1"></i> Map View
                            </button>
                            <button class="btn btn-outline-primary btn-sm" id="global-list-tab" data-bs-toggle="tab" data-bs-target="#global-list-view" type="button" role="tab" aria-controls="global-list-view" aria-selected="false" style="padding: 5px 15px; font-size: 13px;">
                                <i class="fas fa-list me-1"></i> List View
                            </button>
                        </div>
                    </div>

                    <!-- Tab panes -->
                    <div class="tab-content border-top pt-3" id="globalMapTabsContent">
                        <!-- Map View Pane -->
                        <div class="tab-pane fade show active" id="global-map-view" role="tabpanel" aria-labelledby="global-map-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="filterArea" class="mb-0 fw-bold text-muted small">FILTER AREA:</label>
                                    <select id="filterArea" class="form-select form-select-sm" style="width: 140px; border-radius: 20px;">
                                        <option value="all">Semua Area</option>
                                        <option value="office">Office</option>
                                        <option value="disa">DISA</option>
                                        <option value="machining">Machining</option>
                                        <option value="ace">ACE</option>
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-warning btn-sm btn-round shadow-sm px-3">
                                        <i class="fas fa-edit me-1"></i> Edit Marker
                                    </button>
                                </div>
                            </div>

                            <div class="map-wrapper shadow-sm rounded overflow-auto" style="border: 1px solid #eee; background: #fcfcfc;">
                                <div class="map-container-global position-relative mx-auto" style="min-height: 500px;">
                                    <img src="assets/img/ati-layout.jpeg" alt="Layout Map" style="width: 100%; display: block;">
                                    <!-- Tooltip container -->
                                    <div id="tooltip-global-map" class="custom-map-tooltip-global">
                                        <div class="tooltip-header d-flex justify-content-between">
                                            <span id="ttg-kode" class="fw-bold"></span>
                                            <span id="ttg-status" class="badge badge-sm"></span>
                                        </div>
                                        <div class="tooltip-body mt-2">
                                            <p class="mb-1"><b>Jenis:</b> <span id="ttg-jenis"></span></p>
                                            <p class="mb-1"><b>Lokasi:</b> <span id="ttg-lokasi"></span></p>
                                            <p class="mb-0"><b>Ket:</b> <span id="ttg-keterangan"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View Pane -->
                        <div class="tab-pane fade" id="global-list-view" role="tabpanel" aria-labelledby="global-list-tab">
                            <div class="table-responsive">
                                <table id="global-list-table" class="display table table-striped table-hover table-slim w-100">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Perangkat</th>
                                            <th>Jenis</th>
                                            <th>Lokasi</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-round px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .map-container-global .map-marker {
            position: absolute;
            width: 32px;
            height: 32px;
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            transform: translate(-50%, -50%);
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10;
            transition: all 0.2s;
        }

        .map-container-global .map-marker:hover {
            transform: translate(-50%, -50%) scale(1.15);
            z-index: 20;
        }

        .map-container-global .marker-ok { background: #28a745; color: #fff; }
        .map-container-global .marker-warning { background: #ffc107; color: #000; }
        .map-container-global .marker-danger { background: #dc3545; color: #fff; }

        .custom-map-tooltip-global {
            position: absolute;
            background: white;
            border-radius: 8px;
            padding: 10px 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            pointer-events: none;
            min-width: 200px;
            border-left: 5px solid #1572e8;
        }

        .custom-map-tooltip-global p {
            font-size: 11px;
            color: #666;
        }
    </style>

    <script>
    $(document).ready(function() {
        let globalDt;

        // Function to load all markers and table data
        function loadGlobalData() {
            $.ajax({
                url: 'actions/dashboard/ac_get_all_markers.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const container = $('.map-container-global');
                    container.find('.map-marker').remove();
                    
                    if (!globalDt) {
                        globalDt = $('#global-list-table').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true,
                            pageLength: 10,
                            language: { emptyTable: "Tidak ada data perangkat" }
                        });
                    }
                    globalDt.clear();

                    response.forEach(function(item) {
                        // Populate Map Markers
                        let markerClass = '';
                        let statusBadge = '';
                        if (item.status_badge === 'Proses') {
                            markerClass = 'marker-warning';
                            statusBadge = '<span class="badge bg-warning text-dark">Proses</span>';
                        } else if (item.status_badge === 'OK') {
                            markerClass = 'marker-ok';
                            statusBadge = '<span class="badge bg-success">OK</span>';
                        } else {
                            markerClass = 'marker-danger';
                            statusBadge = '<span class="badge bg-danger">Abnormal</span>';
                        }

                        const iconClass = item.device_type === 'apar' ? 'fa-fire-extinguisher' : 'fas fa-shield-alt';
                        
                        const markerHtml = `
                            <div class="map-marker ${markerClass}" 
                                 style="left: ${item.x_coordinate}%; top: ${item.y_coordinate}%;" 
                                 data-kode="${item.kode || '-'}" 
                                 data-status="${item.status_badge}" 
                                 data-jenis="${item.jenis || '-'}" 
                                 data-lokasi="${item.lokasi || '-'}" 
                                 data-area="${item.area ? item.area.toLowerCase() : ''}"
                                 data-keterangan="${item.device_type.toUpperCase()}">
                                <i class="fas ${iconClass}" style="font-size: 10px;"></i>
                            </div>`;
                        container.append(markerHtml);

                        // Populate DataTable
                        globalDt.row.add([
                            item.kode || '-',
                            item.device_type.toUpperCase(),
                            item.jenis || '-',
                            item.lokasi || '-',
                            item.area || '-',
                            statusBadge,
                            `<button class="btn btn-sm btn-primary" onclick="focusMarker('${item.kode}')"><i class="fas fa-search-location"></i></button>`
                        ]);
                    });
                    
                    globalDt.draw();
                    $('#filterArea').trigger('change');
                },
                error: function(err) {
                    console.error("Failed to load map data:", err);
                }
            });
        }

        // Load markers when modal is opened
        $('#globalMapViewModal').on('shown.bs.modal', function () {
            loadGlobalData();
        });

        // Custom Focus Function (Go to Map Tab and highlight)
        window.focusMarker = function(kode) {
            $('#global-map-tab').tab('show');
            // Optional: Add blink effect to marker
            const marker = $(`.map-marker[data-kode="${kode}"]`);
            marker.css({ 'box-shadow': '0 0 20px #1572e8', 'transform': 'translate(-50%, -50%) scale(1.5)' });
            setTimeout(() => {
                marker.css({ 'box-shadow': '', 'transform': '' });
            }, 3000);
        };

        // Logic for global map tooltip
        $(document).on('mouseenter', '.map-container-global .map-marker', function(e) {
            const marker = $(this);
            const tooltip = $('#tooltip-global-map');
            
            $('#ttg-kode').text(marker.data('kode'));
            $('#ttg-jenis').text(marker.data('jenis'));
            $('#ttg-lokasi').text(marker.data('lokasi'));
            $('#ttg-keterangan').text(marker.data('keterangan'));
            
            const status = marker.data('status');
            const badge = $('#ttg-status');
            badge.text(status).removeClass('bg-success bg-warning bg-danger text-dark');
            
            if(status === 'OK') badge.addClass('bg-success');
            else if(status === 'Proses') badge.addClass('bg-warning text-dark');
            else badge.addClass('bg-danger');
            
            tooltip.show();
        }).on('mouseleave', '.map-container-global .map-marker', function() {
            $('#tooltip-global-map').hide();
        }).on('mousemove', '.map-container-global .map-marker', function(e) {
            const tooltip = $('#tooltip-global-map');
            const container = $('.map-container-global');
            const offset = container.offset();
            
            const relX = e.pageX - offset.left + container.scrollLeft();
            const relY = e.pageY - offset.top + container.scrollTop();
            
            tooltip.css({
                left: (relX + 15) + 'px',
                top: (relY + 15) + 'px'
            });
        });

        // Handle area filtering
        $('#filterArea').on('change', function() {
            const area = $(this).val().toLowerCase();
            const markers = $('.map-container-global .map-marker');
            
            if(area === 'all') {
                markers.show();
                if (globalDt) globalDt.column(4).search('').draw();
            } else {
                markers.hide();
                markers.each(function() {
                    if ($(this).attr('data-area') === area) {
                        $(this).show();
                    }
                });
                // Filter datatable column matching Area (Index 4)
                if (globalDt) globalDt.column(4).search(area, true, false).draw();
            }
        });

        // Fix table header alignment when switching to list tab
        $('#global-list-tab').on('shown.bs.tab', function () {
            if (globalDt) globalDt.columns.adjust().draw();
        });
    });
    </script>
</body>

</html>