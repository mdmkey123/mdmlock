<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
        exit;
    }

    $update_query = "UPDATE main_admin SET password = '$new_password' WHERE id = $admin_id";
    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
    }
}
?>
