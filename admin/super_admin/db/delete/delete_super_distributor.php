<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $query = "DELETE FROM super_distributor WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success", "message" => "Super Distributor deleted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete Super Distributor!"]);
    }
}
?>
