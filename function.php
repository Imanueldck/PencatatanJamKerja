<?php
include 'config.php';

function getTotalPengeluaran($conn) {
    $result = $conn->query("SELECT SUM(jumlah_rupiah) AS total FROM pengeluaran");
    $data = $result->fetch_assoc();
    return $data['total'] ?? 0;
}
?>
