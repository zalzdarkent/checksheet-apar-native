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
                            <!-- Left: Filter Area -->
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

                            <!-- Center: Instruction Banner -->
                            <div id="plotting-instruction" class="badge bg-info d-none animate__animated animate__fadeInDown px-4 py-2 shadow-sm" style="font-size: 13px; border-radius: 20px; z-index: 1060; min-width: 250px;">
                                <i class="fas fa-info-circle me-1"></i> <span id="instruction-text">Pilih Perangkat</span>
                            </div>

                            <!-- Right: Mode View & Action Buttons -->
                            <div class="d-flex gap-2 align-items-center">
                                <div id="plotting-actions" class="d-none animate__animated animate__fadeIn">
                                    <button id="btn-save-plot" class="btn btn-success btn-sm btn-round shadow-sm px-3 me-1">
                                        <i class="fas fa-check me-1"></i> Selesai
                                    </button>
                                    <button id="btn-cancel-plot" class="btn btn-danger btn-sm btn-round shadow-sm px-3">
                                        <i class="fas fa-undo me-1"></i> Batal
                                    </button>
                                </div>
                                <button id="btn-edit-marker" class="btn btn-warning btn-sm btn-round shadow-sm px-3">
                                    <i class="fas fa-edit me-1"></i> Edit Marker
                                </button>
                            </div>
                        </div>

                        <div class="position-relative">
                            <!-- Plotting Sidebar -->
                            <div id="plotting-sidebar" class="plotting-sidebar d-none">
                                <div class="card shadow-sm border-warning">
                                    <div class="card-header bg-warning text-dark py-2 d-flex justify-content-between align-items-center">
                                        <span class="fw-bold small"><i class="fas fa-plus-circle me-1"></i> Pilih Perangkat</span>
                                        <button type="button" class="btn-close btn-close-sm" id="close-plotting"></button>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                        <ul class="list-group list-group-flush" id="unplotted-list">
                                            <!-- Loaded via AJAX -->
                                            <li class="list-group-item text-center py-3 text-muted small">Loading...</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="map-wrapper shadow-sm rounded overflow-auto" style="border: 1px solid #eee; background: #fcfcfc;">
                                <div class="map-container-global position-relative mx-auto" style="min-height: 500px;">
                                        <img src="assets/img/ati-layout.jpeg" id="global-map-img" alt="Layout Map" style="width: 100%; display: block; cursor: default;">
                                        
                                        <!-- Temporary Marker -->
                                        <div id="temp-marker" class="map-marker marker-temp d-none animate__animated animate__pulse animate__infinite">
                                            <i class="fas fa-map-pin"></i>
                                        </div>

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
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 10;
        transition: all 0.2s;
    }

    .map-container-global .map-marker:hover {
        transform: translate(-50%, -50%) scale(1.15);
        z-index: 20;
    }

    .map-container-global .marker-ok {
        background: #28a745;
        color: #fff;
    }

    .map-container-global .marker-warning {
        background: #ffc107;
        color: #000;
    }

    .map-container-global .marker-danger {
        background: #dc3545;
        color: #fff;
    }

    .map-container-global .marker-staged {
        background: #e91e63;
        color: #fff;
        z-index: 50;
        font-size: 14px;
        border: 2px solid #fff;
    }

    .custom-map-tooltip-global {
        position: absolute;
        background: white;
        border-radius: 8px;
        padding: 10px 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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

    .plotting-sidebar {
        position: absolute;
        top: 10px;
        right: 15px;
        width: 280px;
        z-index: 1050;
    }

    .unplotted-item {
        cursor: pointer;
        transition: background 0.2s;
        border-left: 3px solid transparent;
    }

    .unplotted-item:hover {
        background: #fff9e6;
    }

    .unplotted-item.active {
        background: #fff3cd;
        border-left: 3px solid #ffc107;
        font-weight: bold;
    }

    .map-container-global.plotting-mode {
        cursor: crosshair !important;
    }

    .map-container-global.plotting-mode #global-map-img {
        cursor: crosshair !important;
    }
</style>

<script>
    $(document).ready(function () {
        let globalDt;
        let isPlottingMode = false;
        let selectedDevice = null;
        let stagedMarkers = []; // Array to store multiple markers before saving

        function loadGlobalData() {
            $.ajax({
                url: 'actions/dashboard/ac_get_all_markers.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    const container = $('.map-container-global');
                    // Remove existing markers but keep staged ones
                    container.find('.map-marker:not(.marker-staged)').remove();

                    if (!globalDt) {
                        globalDt = $('#global-list-table').DataTable({
                            paging: true,
                            searching: true,
                            ordering: true,
                            responsive: true,
                            pageLength: 10,
                            language: {
                                emptyTable: "Tidak ada data perangkat"
                            }
                        });
                    }
                    globalDt.clear();

                    response.forEach(function (item) {
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
                error: function (err) {
                    console.error("Failed to load map data:", err);
                }
            });
        }

        function loadUnplottedDevices() {
            $.ajax({
                url: 'actions/dashboard/ac_get_unplotted_devices.php',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    const list = $('#unplotted-list');
                    list.empty();
                    
                    // Filter out already staged markers
                    const filtered = response.filter(d => !stagedMarkers.some(s => s.code === d.code));

                    if (filtered.length === 0) {
                        list.append('<li class="list-group-item text-center py-3 text-muted small">Semua perangkat sudah dipilih</li>');
                        return;
                    }

                    filtered.forEach(function (device) {
                        const icon = device.device_type === 'apar' ? 'fa-fire-extinguisher' : 'fa-shield-alt';
                        list.append(`
                            <li class="list-group-item unplotted-item small py-2" 
                                data-code="${device.code}" 
                                data-type="${device.device_type}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas ${icon} me-2 text-primary"></i>${device.code}</span>
                                    <span class="badge bg-light text-dark" style="font-size: 10px;">${device.area || '-'}</span>
                                </div>
                                <div class="text-muted" style="font-size: 10px;">${device.lokasi || '-'}</div>
                            </li>
                        `);
                    });
                }
            });
        }

        // UX: Update Instruction Banner
        function updateInstruction(text, type = 'info') {
            const banner = $('#plotting-instruction');
            const txt = $('#instruction-text');
            banner.removeClass('bg-info bg-warning bg-success').addClass('bg-' + type);
            txt.text(text);
            banner.removeClass('d-none');
        }

        // Toggle Plotting Mode
        $('#btn-edit-marker').on('click', function () {
            isPlottingMode = !isPlottingMode;
            if (isPlottingMode) {
                $(this).removeClass('btn-warning').addClass('btn-dark').html('<i class="fas fa-times me-1"></i> Mode View');
                $('#plotting-sidebar').removeClass('d-none');
                $('.map-container-global').addClass('plotting-mode');
                updateInstruction("Langkah 1: Pilih Perangkat", "info");
                loadUnplottedDevices();
            } else {
                if (stagedMarkers.length > 0) {
                    swal({
                        title: "Batalkan semua?",
                        text: "Ada perangkat yang belum disimpan ke database. Batalkan semua antrean?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willCancel) => {
                        if (willCancel) stopPlotting();
                        else isPlottingMode = true; // Stay in mode
                    });
                } else {
                    stopPlotting();
                }
            }
        });

        function stopPlotting() {
            isPlottingMode = false;
            selectedDevice = null;
            stagedMarkers = [];
            $('#btn-edit-marker').removeClass('btn-dark').addClass('btn-warning').html('<i class="fas fa-edit me-1"></i> Edit Marker');
            $('#plotting-sidebar, #plotting-info, #plotting-instruction, #plotting-actions').addClass('d-none');
            $('.map-marker.marker-staged').remove();
            $('.map-container-global').removeClass('plotting-mode');
            $('.unplotted-item').removeClass('active');
        }

        $('#close-plotting').on('click', function () {
            $('#btn-edit-marker').trigger('click');
        });

        // Step 1: Select device to plot
        $(document).on('click', '.unplotted-item', function () {
            $('.unplotted-item').removeClass('active');
            $(this).addClass('active');
            selectedDevice = {
                code: $(this).data('code'),
                type: $(this).data('type')
            };
            
            updateInstruction(`Langkah 2: Klik lokasi untuk ${selectedDevice.code}`, "warning");
        });

        // Step 2: Click on map to stage marker
        $('.map-container-global').on('click', function (e) {
            if (!isPlottingMode || !selectedDevice) return;

            const container = $(this);
            const offset = container.offset();
            const relX = e.pageX - offset.left;
            const relY = e.pageY - offset.top;

            const xPercent = (relX / container.width() * 100).toFixed(2);
            const yPercent = (relY / container.height() * 100).toFixed(2);
            
            // Add to stagedMarkers
            stagedMarkers.push({
                code: selectedDevice.code,
                device_type: selectedDevice.type,
                x: xPercent,
                y: yPercent
            });

            // Render staged marker
            const iconClass = selectedDevice.type === 'apar' ? 'fa-fire-extinguisher' : 'fa-shield-alt';
            const stagedHtml = `
                <div class="map-marker marker-staged animate__animated animate__pulse animate__infinite" 
                     id="staged-${selectedDevice.code}"
                     style="left: ${xPercent}%; top: ${yPercent}%; z-index: 100;">
                    <i class="fas ${iconClass}"></i>
                    <span style="position:absolute; top:-18px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.8); color:white; padding:1px 6px; border-radius:10px; font-size:9px; white-space:nowrap; border: 1px solid white;">
                        ${selectedDevice.code}
                    </span>
                </div>`;
            container.append(stagedHtml);

            // Reset selection for next device
            selectedDevice = null;
            $('.unplotted-item').removeClass('active');
            
            // Show Actions
            $('#plotting-actions').removeClass('d-none');
            updateInstruction(`Berhasil diantre! Pilih lagi atau klik Selesai (${stagedMarkers.length} siap simpan)`, "success");
            loadUnplottedDevices(); // Refresh list to hide staged ones

            $.notify({
                icon: 'fas fa-check',
                title: 'Berhasil Masuk Antrean',
                message: 'Silakan pilih perangkat lain atau klik Selesai.'
            }, { type: 'success', placement: { from: "top", align: "center" }, delay: 1000 });
        });

        // Button Batal (Clear all staged)
        $('#btn-cancel-plot').on('click', function() {
            swal({
                title: "Kosongkan antrean?",
                text: `Membatalkan ${stagedMarkers.length} perangkat yang sudah diplot sementara?`,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willClear) => {
                if (willClear) {
                    stagedMarkers = [];
                    $('.map-marker.marker-staged').remove();
                    $('#plotting-actions').addClass('d-none');
                    updateInstruction("Langkah 1: Pilih Perangkat", "info");
                    loadUnplottedDevices();
                }
            });
        });

        // Button Selesai (Batch Save)
        $('#btn-save-plot').on('click', function() {
            if (stagedMarkers.length === 0) return;

            swal({
                title: "Simpan Semua Lokasi?",
                text: `Anda akan menyimpan koordinat untuk ${stagedMarkers.length} perangkat sekaligus.`,
                icon: "info",
                buttons: ["Nanti dulu", "Ya, Simpan Semua!"],
            }).then((willSave) => {
                if (willSave) {
                    saveBatchMarkers(0);
                }
            });
        });

        function saveBatchMarkers(index) {
            if (index >= stagedMarkers.length) {
                swal("Berhasil!", `Semua (${stagedMarkers.length}) lokasi perangkat telah diperbarui di database.`, "success");
                stopPlotting();
                loadGlobalData();
                return;
            }

            const data = stagedMarkers[index];
            $.ajax({
                url: 'actions/dashboard/ac_update_marker_pos.php',
                type: 'POST',
                data: {
                    code: data.code,
                    device_type: data.device_type,
                    x_coordinate: data.x,
                    y_coordinate: data.y
                },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        saveBatchMarkers(index + 1);
                    } else {
                        swal("Gagal menyimpan " + data.code, res.message, "error");
                    }
                },
                error: function() {
                    swal("Error Server", "Gagal menghubungi server saat menyimpan " + data.code, "error");
                }
            });
        }

        // Load markers when modal is opened
        $('#globalMapViewModal').on('shown.bs.modal', function () {
            loadGlobalData();
        });

        // Custom Focus Function
        window.focusMarker = function (kode) {
            $('#global-map-tab').tab('show');
            const marker = $(`.map-marker[data-kode="${kode}"]`);
            marker.css({
                'box-shadow': '0 0 20px #1572e8',
                'transform': 'translate(-50%, -50%) scale(1.5)'
            });
            setTimeout(() => {
                marker.css({
                    'box-shadow': '',
                    'transform': ''
                });
            }, 3000);
        };

        // Logic for global map tooltip
        $(document).on('mouseenter', '.map-container-global .map-marker', function (e) {
            if (isPlottingMode) return; 
            const marker = $(this);
            const tooltip = $('#tooltip-global-map');

            $('#ttg-kode').text(marker.data('kode'));
            $('#ttg-jenis').text(marker.data('jenis'));
            $('#ttg-lokasi').text(marker.data('lokasi'));
            $('#ttg-keterangan').text(marker.data('keterangan'));

            const status = marker.data('status');
            const badge = $('#ttg-status');
            badge.text(status).removeClass('bg-success bg-warning bg-danger text-dark');

            if (status === 'OK') badge.addClass('bg-success');
            else if (status === 'Proses') badge.addClass('bg-warning text-dark');
            else badge.addClass('bg-danger');

            tooltip.show();
        }).on('mouseleave', '.map-container-global .map-marker', function () {
            $('#tooltip-global-map').hide();
        }).on('mousemove', '.map-container-global .map-marker', function (e) {
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
        $('#filterArea').on('change', function () {
            const area = $(this).val().toLowerCase();
            const markers = $('.map-container-global .map-marker');

            if (area === 'all') {
                markers.show();
                if (globalDt) globalDt.column(4).search('').draw();
            } else {
                markers.hide();
                markers.each(function () {
                    if ($(this).attr('data-area') === area) {
                        $(this).show();
                    }
                });
                if (globalDt) globalDt.column(4).search(area, true, false).draw();
            }
        });

        $('#global-list-tab').on('shown.bs.tab', function () {
            if (globalDt) globalDt.columns.adjust().draw();
        });
    });
</script>
