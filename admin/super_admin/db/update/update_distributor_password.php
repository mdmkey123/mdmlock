<?php
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match!"]);
        exit;
    }

    $update_query = "UPDATE distributors SET password_hash = '$new_password' WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        echo json_encode(["status" => "success", "message" => "Password updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password. Try again!"]);
    }
}

mysqli_close($conn);
?>
