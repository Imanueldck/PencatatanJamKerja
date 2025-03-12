<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $deskripsi = $_POST['deskripsi'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Edit data
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE pengeluaran SET tanggal=?, jumlah_rupiah=?, deskripsi=? WHERE id=?");
        $stmt->bind_param("sisi", $tanggal, $jumlah, $deskripsi, $id);
    } else {
        // Tambah data baru
        $stmt = $conn->prepare("INSERT INTO pengeluaran (tanggal, jumlah_rupiah, deskripsi) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $tanggal, $jumlah, $deskripsi);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: pengeluaran.php");
    exit();
}

if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM pengeluaran WHERE id=?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: pengeluaran.php");
    exit();
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM pengeluaran WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatat Pengeluaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="pengeluaran.css">
</head>
<body>
    <!-- ✅ Navbar yang lebih baik -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">Catatan keuangan</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link " href="index.php">Jam Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pengeluaran.php">Pengeluaran</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>  

    <div class="container pengeluaran">
        <h2 class="text-center">Pencatat Pengeluaran</h2>
        <form method="post" class="mb-3">
            <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
            <div class="mb-2">
                <label for="tanggal" class="form-label">Tanggal:</label>
                <input type="date" class="form-control" name="tanggal" value="<?php echo $editData['tanggal'] ?? ''; ?>" required>
            </div>
            <div class="mb-2">
                <label for="jumlah" class="form-label">Jumlah (Rp):</label>
                <input type="number" class="form-control" name="jumlah" value="<?php echo $editData['jumlah_rupiah'] ?? ''; ?>" required>
            </div>
            <div class="mb-2">
                <label for="deskripsi" class="form-label">Deskripsi:</label>
                <textarea class="form-control" name="deskripsi" rows="3" required><?php echo $editData['deskripsi'] ?? ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <?php echo isset($editData) ? 'Update' : 'Simpan'; ?>
            </button>
        </form>

        <h3 class="text-center">Riwayat Pengeluaran</h3>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jumlah (Rp)</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM pengeluaran ORDER BY tanggal ASC");
                $totalPengeluaran = 0;
                $no = 1;
                while ($entry = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$entry['tanggal']}</td>
                        <td>".number_format($entry['jumlah_rupiah'], 0, ',', '.')."</td>
                        <td>{$entry['deskripsi']}</td>
                        <td>
                         <a class='btn btn-warning btn-sm' href='?edit={$entry['id']}'>Edit</a>
                            <a class='btn btn-danger btn-sm' href='?delete={$entry['id']}' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                        </td>
                    </tr>";
                    $totalPengeluaran += $entry['jumlah_rupiah'];
                    $no++;
                }
                ?>
            </tbody>
        </table>
        <h3 class="text-center">Total Pengeluaran: Rp <?php echo number_format($totalPengeluaran, 0, ',', '.'); ?></h3>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
