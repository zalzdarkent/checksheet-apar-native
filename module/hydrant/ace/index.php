<?php
$area = 'Ace';
include(__DIR__ . '/../../../actions/ace/ac_get_data_hydrant.php');
?>

<div class="page-inner">
    <style>
        .apar-card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .apar-card {
            background: #1a2035;
            border-radius: 12px;
            padding: 15px;
            position: relative;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .apar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            border-color: #3498db;
        }

        .apar-card.selected {
            border: 2px solid #3498db;
            background: #1d2b4a;
        }

        .card-checkbox {
            position: absolute;
            top: 15px;
            left: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            z-index: 10;
            accent-color: #3498db;
        }

        .delete-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(231, 76, 60, 0.8);
            color: #fff;
            border: none;
            border-radius: 6px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }

        .delete-btn:hover {
            background: #e74c3c;
            transform: scale(1.1);
        }

        .apar-qr-placeholder {
            background: #fff;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 140px;
            aspect-ratio: 1/1;
        }

        .qr-img {
            width: 100%;
            height: auto;
            image-rendering: pixelated;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }

        .status-ok {
            background: #27ae60;
            color: #fff;
        }

        .status-abnormal {
            background: #e74c3c;
            color: #fff;
        }

        .apar-info {
            text-align: center;
            width: 100%;
            margin-bottom: 15px;
        }

        .apar-code {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: #fff;
        }

        .apar-location {
            color: #a0a0a0;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .apar-details {
            font-size: 0.8rem;
            color: #ddd;
            line-height: 1.4;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 10px;
            margin-bottom: 15px;
        }

        .card-footer-actions {
            display: flex;
            gap: 10px;
            width: 100%;
            margin-top: auto;
        }

        .btn-action {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-action:hover {
            transform: scale(1.05);
        }

        .action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            background: rgba(26, 32, 53, 0.6);
            padding: 15px 20px;
            border-radius: 8px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .select-all-container {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff !important;
            font-weight: 600;
        }

        .select-all-container label {
            color: #ffffff !important;
            cursor: pointer;
            margin-bottom: 0;
        }

        #item-count {
            color: #ffffff !important;
            font-weight: 500;
            opacity: 0.8;
        }

        #selected-actions-btn {
            display: none;
        }

        .btn-group-selected {
            display: flex;
            gap: 10px;
        }

        .search-container {
            flex: 1;
            max-width: 400px;
            margin: 0 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 15px 8px 40px;
            color: #fff;
            transition: all 0.3s;
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.8);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Responsive optimizations */
        @media (max-width: 576px) {
            .apar-card-container {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
                gap: 15px;
            }

            .action-bar {
                padding: 10px 15px;
            }
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: #3498db;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s;
            opacity: 0;
            visibility: hidden;
            border: none;
            outline: none;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #2980b9;
            transform: scale(1.1);
            color: #fff;
        }
    </style>

    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Hydrant Management - ACE</h3>
        </div>
        <div class="ms-md-auto py-2 py-md-0 d-flex gap-2">
            <div class="dropdown" id="selected-actions-btn">
                <button class="btn btn-warning btn-round dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Selected Actions
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="print-qr-selected"><i class="fas fa-qrcode"></i> Print QR
                            Code</a></li>
                    <li><a class="dropdown-item text-danger" href="#" id="delete-selected"><i class="fas fa-trash"></i>
                            Delete Selected</a></li>
                </ul>
            </div>
            <button class="btn btn-primary btn-round">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
    </div>

    <div class="action-bar">
        <div class="select-all-container">
            <input type="checkbox" id="select-all-checkbox" style="width: 18px; height: 18px;">
            <label for="select-all-checkbox" class="mb-0">Select All</label>
        </div>
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-input" class="search-input" placeholder="Search code, location, type...">
        </div>
        <div id="item-count" class="text-white small">Showing <?php echo count($hydrant_data); ?> Items</div>
    </div>

    <div id="hydrant-container" class="apar-card-container">
        <?php if (empty($hydrant_data)): ?>
            <div id="no-data-msg" class="text-center w-100 py-5"
                style="grid-column: 1 / -1; color: #a0a0a0; font-size: 1.2rem;">Data tidak ditemukan</div>
        <?php else: ?>
            <?php foreach ($hydrant_data as $item): ?>
                <?php $statusClass = ($item['status'] === 'OK' || $item['status'] === 'Good') ? 'status-ok' : 'status-abnormal'; ?>
                <div class="apar-card" data-id="<?php echo $item['id']; ?>">
                    <input type="checkbox" class="card-checkbox item-checkbox">
                    <button class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>

                    <div class="apar-qr-placeholder">
                        <img src="actions/ac_generate_qrcode.php?data=<?php echo $item['code']; ?>"
                            alt="QR Code" class="qr-img">
                    </div>

                    <div class="apar-info">
                        <div class="status-badge <?php echo $statusClass; ?>"><?php echo $item['status']; ?></div>
                        <div class="apar-code"><?php echo $item['code']; ?></div>
                        <div class="apar-location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $item['location']; ?>
                        </div>
                        <div class="apar-details text-left">
                            <strong>Type:</strong> <?php echo $item['type'] ?: 'N/A'; ?><br>
                            <strong>Last Insp:</strong> <?php echo $item['last_inspection']; ?>
                        </div>
                    </div>

                    <div class="card-footer-actions">
                        <a href="?page=hydrant-detail&id=<?php echo $item['id']; ?>" class="btn btn-info btn-action" title="View Details"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-warning btn-action" title="Print QR"><i class="fas fa-print"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div id="load-more-sentinel" style="height: 20px;"></div>
    <div id="loading-spinner" class="text-center py-3" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<script>
    $(document).ready(function () {
        let currentPage = 1;
        let isLoading = false;
        let hasMore = <?php echo count($hydrant_data) >= 12 ? 'true' : 'false'; ?>;
        let searchQuery = "";
        let searchTimeout;

        function toggleSelectedActions() {
            const selectedCount = $('.item-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#selected-actions-btn').fadeIn();
            } else {
                $('#selected-actions-btn').fadeOut();
            }
        }

        // Use event delegation for cards and checkboxes
        $('#apar-container').on('change', '.item-checkbox', function () {
            const card = $(this).closest('.apar-card');
            if ($(this).is(':checked')) {
                card.addClass('selected');
            } else {
                card.removeClass('selected');
                $('#select-all-checkbox').prop('checked', false);
            }
            toggleSelectedActions();
        });

        $('#apar-container').on('click', '.apar-card', function (e) {
            if ($(e.target).closest('.item-checkbox, .delete-btn, .btn-action').length === 0) {
                const checkbox = $(this).find('.item-checkbox');
                checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
            }
        });

        $('#select-all-checkbox').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.item-checkbox').prop('checked', isChecked).trigger('change');
        });

        $('#print-qr-selected').on('click', function (e) {
            e.preventDefault();
            const selectedIds = $('.item-checkbox:checked').map(function () {
                return $(this).closest('.apar-card').data('id');
            }).get();
            alert('Printing QR Code for IDs: ' + selectedIds.join(', '));
        });

        $('#search-input').on('input', function () {
            clearTimeout(searchTimeout);
            searchQuery = $(this).val();
            searchTimeout = setTimeout(function () {
                currentPage = 0; // Will be incremented to 1 in loadMore
                hasMore = true;
                $('#hydrant-container').empty();
                loadMore();
            }, 300);
        });

        function createCardHtml(item) {
            const statusClass = (item.status === 'OK' || item.status === 'Good') ? 'status-ok' : 'status-abnormal';
            return `
                <div class="apar-card" data-id="${item.id}">
                    <input type="checkbox" class="card-checkbox item-checkbox">
                    <button class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
                    
                    <div class="apar-qr-placeholder">
                        <img src="actions/ac_generate_qrcode.php?data=${item.code}" alt="QR Code" class="qr-img">
                    </div>

                    <div class="apar-info">
                        <div class="status-badge ${statusClass}">${item.status}</div>
                        <div class="apar-code">${item.code}</div>
                        <div class="apar-location">
                            <i class="fas fa-map-marker-alt"></i> ${item.location}
                        </div>
                        <div class="apar-details text-left">
                            <strong>Type:</strong> ${item.type || 'N/A'}<br>
                            <strong>Last Insp:</strong> ${item.last_inspection}
                        </div>
                    </div>

                    <div class="card-footer-actions">
                        <a href="?page=hydrant-detail&id=${item.id}" class="btn btn-info btn-action" title="View Details"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-warning btn-action" title="Print QR"><i class="fas fa-print"></i></button>
                    </div>
                </div>
            `;
        }

        function loadMore() {
            if (isLoading || !hasMore) return;

            isLoading = true;
            $('#loading-spinner').show();
            currentPage++;

            $.ajax({
                url: 'actions/ace/ac_get_data_hydrant.php',
                type: 'GET',
                data: { p: currentPage, limit: 12, q: searchQuery },
                dataType: 'json',
                success: function (data) {
                    if (data && data.length > 0) {
                        $('#no-data-msg').remove();
                        data.forEach(item => {
                            $('#hydrant-container').append(createCardHtml(item));
                        });

                        // Update "Showing X Items" count
                        const currentCount = $('.apar-card').length;
                        $('#item-count').text(`Showing ${currentCount} Items`);

                        if (data.length < 12) {
                            hasMore = false;
                        }
                    } else {
                        hasMore = false;
                        if (currentPage === 1) {
                            $('#apar-container').html('<div id="no-data-msg" class="text-center w-100 py-5" style="grid-column: 1 / -1; color: #a0a0a0; font-size: 1.2rem;">Data tidak ditemukan</div>');
                            $('#item-count').text(`Showing 0 Items`);
                        }
                    }

                    if ($('#select-all-checkbox').is(':checked')) {
                        $('.item-checkbox').prop('checked', true).trigger('change');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading more data:', error);
                },
                complete: function () {
                    isLoading = false;
                    $('#loading-spinner').hide();
                }
            });
        }

        // Infinite Scroll with Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadMore();
            }
        }, { threshold: 0.1 });

        observer.observe(document.getElementById('load-more-sentinel'));

        // Back to Top Logic
        const backToTop = $('#back-to-top');
        $(window).scroll(function () {
            if ($(window).scrollTop() > 300) {
                backToTop.addClass('show');
            } else {
                backToTop.removeClass('show');
            }
        });

        backToTop.on('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>