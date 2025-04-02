<?php
include '../config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "SELECT status FROM main_admin WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    $new_status = ($row['status'] === 'active') ? 'inactive' : 'active';

    $update_query = "UPDATE main_admin SET status = '$new_status' WHERE id = $id";

    if (mysqli_query($conn, $update_query)) {
        $response = [
            'status' => 'success',
            'new_status' => $new_status
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to update status!'
        ];
    }

    echo json_encode($response);
}
?>
