<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Get current status
    $query = "SELECT status FROM super_distributor WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $new_status = ($row['status'] === 'active') ? 'inactive' : 'active';

    $query = "UPDATE super_distributor SET status = '$new_status' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success", "new_status" => $new_status]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update status!"]);
    }
}
?>
