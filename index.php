
<?php
require "koneksi.php";

function rupiah($angka) {
    return "Rp " . number_format((float)$angka, 0, ',', '.');
}

function e($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

$total_warga = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM warga"
))["total"];

$masuk = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(jumlah), 0) AS total
     FROM transaksi WHERE jenis = 'Masuk'"
))["total"];

$keluar = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(jumlah), 0) AS total
     FROM transaksi WHERE jenis = 'Keluar'"
))["total"];

$saldo = $masuk - $keluar;

$aktivitas = mysqli_query(
    $koneksi,
    "SELECT * FROM transaksi
     ORDER BY tanggal DESC, id DESC LIMIT 5"
);

$jumlah_iuran = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(nominal), 0) AS total FROM iuran"
))["total"];

$nama_bulan = date('F Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | WargaKu</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .welcome {
            background: linear-gradient(120deg, #7654b5, #a58bd6);
            color: white;
            padding: 28px;
            border-radius: 18px;
            margin: 22px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .welcome h2 {
            margin: 0 0 10px;
            font-size: 25px;
        }

        .welcome p {
            margin: 0;
            line-height: 1.6;
        }

        .welcome-icon {
            font-size: 65px;
        }

        .section-title {
            margin: 28px 0 15px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 1px solid #eee7f8;
            box-shadow: 0 5px 18px #5e43820b;
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-icon {
            width: 43px;
            height: 43px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 22px;
            background: #f0e8ff;
        }

        .stat-card p {
            color: #777084;
            margin-bottom: 8px;
        }

        .stat-card h2 {
            font-size: 23px;
            margin: 8px 0;
            overflow-wrap: anywhere;
        }

        .stat-note {
            color: #8a8297;
            font-size: 12px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .activity-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f0edf5;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #eee7fa;
            font-size: 19px;
        }

        .activity-info {
            flex: 1;
            min-width: 0;
        }

        .activity-info strong {
            display: block;
            overflow-wrap: anywhere;
        }

        .activity-info small {
            color: #8a8297;
        }

        .amount {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }

        .income {
            color: #27865c;
        }

        .expense {
            color: #cf5267;
        }

        .balance-box {
            background: #f5f0ff;
            border-radius: 13px;
            padding: 18px;
            margin-bottom: 18px;
        }

        .balance-box small {
            color: #74658d;
        }

        .balance-box h2 {
            color: #6544a1;
            margin: 8px 0;
            overflow-wrap: anywhere;
        }

        .quick-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6544a1;
            background: #faf8ff;
            text-decoration: none;
            padding: 14px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .quick-link:hover {
            background: #eee7fa;
        }

        .view-all {
            color: #7153a6;
            text-decoration: none;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 500px) {
            .welcome {
                padding: 20px;
            }

            .welcome h2 {
                font-size: 21px;
            }

            .welcome-icon {
                font-size: 38px;
            }

            .stat-card h2 {
                font-size: 19px;
            }

            .amount {
                font-size: 12px;
            }
        }

        @media print {
            .sidebar {
                display: none;
            }

            .main {
                margin: 0;
            }
        }
    </style>
</head>

<body>

<?php require "menu.php"; ?>

<div class="main">

    <p style="color:#857b96;margin-bottom:5px">
        DASHBOARD / BERANDA
    </p>

    <h1 style="margin-top:5px">Dashboard</h1>

    <div class="welcome">
        <div>
            <h2>Halo, Bendahara! 👋</h2>
            <p>
                Selamat datang di WargaKu.<br>
                Kelola kas warga dengan lebih mudah dan teratur.
            </p>
            <p style="margin-top:15px;font-size:13px">
                📅 <?= e(date('d-m-Y')) ?>
            </p>
        </div>

        <div class="welcome-icon">🏘️</div>
    </div>

    <h2 class="section-title">Ringkasan Keuangan</h2>

    <div class="cards">

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-note">Jumlah warga</span>
                <div class="stat-icon">👥</div>
            </div>
            <h2><?= (int)$total_warga ?> orang</h2>
            <span class="stat-note">Terdaftar di sistem</span>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-note">Kas masuk</span>
                <div class="stat-icon">📥</div>
            </div>
            <h2 class="income"><?= rupiah($masuk) ?></h2>
            <span class="stat-note">Total seluruh pemasukan</span>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-note">Kas keluar</span>
                <div class="stat-icon">📤</div>
            </div>
            <h2 class="expense"><?= rupiah($keluar) ?></h2>
            <span class="stat-note">Total seluruh pengeluaran</span>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <span class="stat-note">Saldo kas</span>
                <div class="stat-icon">💰</div>
            </div>
            <h2><?= rupiah($saldo) ?></h2>
            <span class="stat-note">Sisa uang kas saat ini</span>
        </div>

    </div>

    <div class="dashboard-grid">

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px">
                <h3>Aktivitas Kas Terbaru</h3>
                <a class="view-all" href="laporan.php">Lihat laporan →</a>
            </div>

            <?php if (mysqli_num_rows($aktivitas) > 0): ?>

                <?php while ($a = mysqli_fetch_assoc($aktivitas)): ?>

                    <div class="activity-row">
                        <div class="activity-icon">
                            <?= $a['jenis'] === 'Masuk' ? '📥' : '📤' ?>
                        </div>

                        <div class="activity-info">
                            <strong><?= e($a['kategori']) ?></strong>
                            <small>
                                <?= e(date('d-m-Y', strtotime($a['tanggal']))) ?>
                                · <?= e($a['jenis']) ?>
                            </small>
                        </div>

                        <div class="amount <?= $a['jenis'] === 'Masuk' ? 'income' : 'expense' ?>">
                            <?= $a['jenis'] === 'Masuk' ? '+' : '-' ?>
                            <?= rupiah($a['jumlah']) ?>
                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p style="color:#8a8297;padding:20px 0">
                    Belum ada transaksi kas. Tambahkan transaksi pertamamu.
                </p>

            <?php endif; ?>

            <a href="transaksi.php" class="quick-link">
                <span>＋ Tambah transaksi kas</span>
                <span>→</span>
            </a>
        </div>

        <div class="card">
            <h3>Menu Cepat</h3>
            <p style="color:#857b96;font-size:14px">
                Pilih menu untuk mengelola kas warga.
            </p>

            <a href="warga.php" class="quick-link">
                <span>👥 &nbsp; Kelola Data Warga</span>
                <span>→</span>
            </a>

            <a href="iuran.php" class="quick-link">
                <span>🧾 &nbsp; Kelola Jenis Iuran</span>
                <span>→</span>
            </a>

            <a href="transaksi.php" class="quick-link">
                <span>💵 &nbsp; Catat Kas Masuk/Keluar</span>
                <span>→</span>
            </a>

            <a href="laporan.php" class="quick-link">
                <span>📊 &nbsp; Buka Laporan Kas</span>
                <span>→</span>
            </a>

            <div class="balance-box" style="margin-top:20px">
                <small>Saldo kas tersedia</small>
                <h2><?= rupiah($saldo) ?></h2>
                <small>Kas masuk dikurangi kas keluar</small>
            </div>
        </div>

    </div>

    <p style="text-align:center;color:#9a92a8;font-size:12px;margin-top:30px">
        WargaKu © <?= date('Y') ?> · Sistem Informasi Kas Warga
    </p>

</div>

</body>
</html>
