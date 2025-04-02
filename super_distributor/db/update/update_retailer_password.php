<?php
include '../config.php';

if (isset($_POST['id'], $_POST['new_password'], $_POST['confirm_password'])) {
    $retailer_id = $_POST['id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        echo '{"status": "error", "message": "Passwords do not match!"}';
        exit;
    }

    $query = "UPDATE admin SET password = '$new_password' WHERE id = $retailer_id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo '{"status": "success", "message": "Password updated successfully!"}';
    } else {
        echo '{"status": "error", "message": "Failed to update password!"}';
    }
} else {
    echo '{"status": "error", "message": "Required fields are missing!"}';
}
?>
