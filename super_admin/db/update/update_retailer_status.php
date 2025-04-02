<?php
include '../config.php';

if (isset($_POST['id'])) {
    $retailer_id = $_POST['id'];

    $query = "SELECT status FROM admin WHERE id = $retailer_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    $new_status = ($row['status'] == 1) ? 0 : 1;

    $update_query = "UPDATE admin SET status = $new_status WHERE id = $retailer_id";
    if (mysqli_query($conn, $update_query)) {
        echo json_encode([
            'status' => 'success',
            'new_status' => $new_status
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update status!'
        ]);
    }
}
?>
