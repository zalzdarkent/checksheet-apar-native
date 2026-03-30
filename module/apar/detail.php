<?php
include(__DIR__ . '/../../actions/ac_get_apar_detail.php');

if (!$apar) {
    echo "<div class='page-inner'><div class='alert alert-danger'>APAR tidak ditemukan atau ID tidak valid.</div></div>";
    return;
}

$statusClass = ($apar['status'] === 'OK' || $apar['status'] === 'Good') ? 'status-ok' : 'status-abnormal';
?>

<div class="page-inner">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-bold text-info mb-0">APAR Detail</h3>
        <button onclick="history.back()" class="btn btn-sm btn-light border shadow-sm">Back</button>
    </div>

    <style>
        .detail-card {
            background: #1a2035;
            border-radius: 15px;
            padding: 40px 20px;
            color: #fff;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 30px;
        }

        .apar-large-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(231, 76, 60, 0.3));
        }

        .apar-large-code {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        .btn-inspeksi {
            background: #0088cc;
            color: #fff;
            padding: 12px 40px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            margin-bottom: 25px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 136, 204, 0.3);
        }

        .btn-inspeksi:hover {
            background: #0077b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 136, 204, 0.4);
            color: #fff;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 20px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .status-ok { background: #27ae60; color: #fff; }
        .status-abnormal { background: #e74c3c; color: #fff; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-box .label {
            display: block;
            font-size: 0.85rem;
            color: #a0a0a0;
            margin-bottom: 8px;
        }

        .info-box .value {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 40px 0 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .empty-state {
            color: #888;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }

        .history-list {
            background: #1a2035;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .history-item {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ddd;
        }

        .history-item:last-child { border-bottom: none; }
        .history-date { font-weight: 600; color: #fff; }
        .history-user { font-size: 0.85rem; color: #888; }
    </style>

    <div class="detail-card">
        <div class="apar-large-icon">
            <i class="fas fa-fire-extinguisher"></i>
        </div>
        <div class="apar-large-code"><?php echo $apar['code']; ?></div>
        
        <a href="?page=apar-inspect&id=<?php echo $apar['id']; ?>" class="btn btn-inspeksi">
            <i class="fas fa-clipboard-check"></i> Mulai Inspeksi
        </a>
        <a href="print_qr.php?type=apar&ids=<?php echo $apar['id']; ?>" target="_blank" class="btn btn-inspeksi bg-warning text-dark border-0">
            <i class="fas fa-print"></i> Print QR
        </a>

        <div class="d-block">
            <div class="status-badge <?php echo $statusClass; ?>">
                <?php echo $apar['status']; ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <span class="label">Area</span>
                <span class="value"><?php echo $apar['area']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Location</span>
                <span class="value"><?php echo $apar['location']; ?></span>
            </div>
            <div class="info-box">
                <span class="label">Weight</span>
                <span class="value"><?php echo $apar['weight']; ?> Kg</span>
            </div>
            <div class="info-box">
                <span class="label">Expired Date</span>
                <span class="value"><?php echo $apar['expired_date_fmt']; ?></span>
            </div>
        </div>
    </div>

    <div class="section-title">Riwayat Pemeriksaan</div>
    <?php if (empty($apar['history'])): ?>
        <div class="empty-state">Belum ada data pemeriksaan.</div>
    <?php else: ?>
        <div class="history-list">
            <?php foreach ($apar['history'] as $h): ?>
                <div class="history-item">
                    <div>
                        <div class="history-date"><?php echo $h['inspection_date_fmt']; ?></div>
                        <div class="history-user">Oleh: <?php echo $h['inspector_name'] ?: 'Unknown'; ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success">Complete</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="section-title">Abnormal Case</div>
    <?php if (empty($apar['cases'])): ?>
        <div class="empty-state">Belum ada abnormal case.</div>
    <?php else: ?>
        <div class="history-list">
            <?php foreach ($apar['cases'] as $c): ?>
                <div class="history-item">
                    <div>
                        <div class="history-date"><?php echo $c['abnormal_case']; ?></div>
                        <div class="history-user">PIC: <?php echo $c['pic_id'] ?: '-'; ?> | Status: <?php echo $c['status']; ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge <?php echo $c['status'] === 'Fixed' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $c['status']; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
