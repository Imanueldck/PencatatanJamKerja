<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $editId = isset($_POST['edit_id']) ? $_POST['edit_id'] : '';
    
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    $diff = ($endTime - $startTime) / 3600;
    
    if ($diff > 0) {
        if ($editId) {
            $stmt = $conn->prepare("UPDATE work_log SET date=?, start=?, end=?, hours=? WHERE id=?");
            $stmt->bind_param("sssdi", $date, $start, $end, $diff, $editId);
        } else {
            $stmt = $conn->prepare("INSERT INTO work_log (date, start, end, hours) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $date, $start, $end, $diff);
        }
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM work_log WHERE id=?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

$editData = ["date" => "", "start" => "", "end" => "", "id" => ""];
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM work_log WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $editData = $result;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencatat Jam Kerja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; }
        .container { max-width: 800px; margin: 40px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .btn-custom { background-color: #007bff; color: white; border-radius: 5px; padding: 5px 10px; }
        .btn-custom:hover { background-color: #0056b3; }
        table { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Pencatat Jam Kerja</h2>
        <form method="post" class="mb-3">
            <input type="hidden" name="edit_id" value="<?php echo $editData['id']; ?>">
            <div class="mb-2">
                <label for="date" class="form-label">Tanggal:</label>
                <input type="date" class="form-control" name="date" required value="<?php echo $editData['date']; ?>">
            </div>
            <div class="mb-2">
                <label for="start" class="form-label">Jam Masuk:</label>
                <input type="time" class="form-control" name="start" required value="<?php echo $editData['start']; ?>">
            </div>
            <div class="mb-2">
                <label for="end" class="form-label">Jam Keluar:</label>
                <input type="time" class="form-control" name="end" required value="<?php echo $editData['end']; ?>">
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </form>
        
        <h3 class="text-center">Riwayat Jam Kerja</h3>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Total Jam</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM work_log ORDER BY date ASC");
                $totalHours = 0;
                $no = 1;
                while ($entry = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$entry['date']}</td>
                        <td>{$entry['start']}</td>
                        <td>{$entry['end']}</td>
                        <td>{$entry['hours']}</td>
                        <td>
                            <a class='btn btn-warning btn-sm' href='?edit={$entry['id']}'>Edit</a>
                            <a class='btn btn-danger btn-sm' href='?delete={$entry['id']}' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                        </td>
                    </tr>";
                    $totalHours += $entry['hours'];
                    $no++;
                }
                ?>
            </tbody>
        </table>
        <h3 class="text-center">Total Jam: <?php echo number_format($totalHours, 2); ?></h3>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
