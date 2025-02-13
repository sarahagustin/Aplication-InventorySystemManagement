<?php
require 'function.php';
require 'cek.php';

// Periksa koneksi
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Cek apakah pengguna adalah admin
if ($_SESSION['rl'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Proses pengesahan dan penolakan
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($_GET['action'] == 'approve') {
        // Approve the request
        $query = "SELECT * FROM approval_requests WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);

        // Insert into barang_keluar with status Approved
        $insertQuery = "INSERT INTO barang_keluar (assetno, usedbyempid, useddate, usedremark, givenbyempid, is_approved) VALUES (
            '".$data['assetno']."', 
            '".$data['usedbyempid']."', 
            '".$data['useddate']."', 
            '".$data['usedremark']."', 
            '".$data['givenbyempid']."', 
            'Approved')";
        
        if (mysqli_query($conn, $insertQuery)) {
            // Set notification for successful approval
            $_SESSION['notification'] = 'Data berhasil disetujui.';
        } else {
            $_SESSION['notification'] = 'Error: ' . mysqli_error($conn);
        }

        // Delete the request
        mysqli_query($conn, "DELETE FROM approval_requests WHERE id = '$id'");
        header('Location: keluar.php');
        exit();
    } elseif ($_GET['action'] == 'reject') {
        // Reject the request
        mysqli_query($conn, "DELETE FROM approval_requests WHERE id = '$id'");
        // Set notification for rejection
        $_SESSION['notification'] = 'Data berhasil ditolak.';
        header('Location: keluar.php');
        exit();
    }
}

// Fetch approval requests
$requests = mysqli_query($conn, "SELECT * FROM approval_requests");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Approval Requests</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Approval Requests</h1>
        
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="alert alert-info">
                <?php 
                    echo ($_SESSION['notification']);
                    unset($_SESSION['notification']); // Clear the notification after displaying
                ?>
            </div>
        <?php endif; ?>

        <a href="keluar.php" class="btn btn-primary mb-3">Back</a>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Asset No</th>
                    <th>Used By</th>
                    <th>Used Date</th>
                    <th>Remark</th>
                    <th>Givenbyempid</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($requests)) : ?>
                    <tr>
                        <td><?php echo ($row['assetno']); ?></td>
                        <td><?php echo ($row['usedbyempid']); ?></td>
                        <td><?php echo ($row['useddate']); ?></td>
                        <td><?php echo ($row['usedremark']); ?></td>
                        <td><?php echo ($row['givenbyempid']); ?></td>
                        <td>
                            <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>