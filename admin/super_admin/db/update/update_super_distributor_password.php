<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $new_password = $_POST['new_password'];

    $query = "UPDATE super_distributor SET password = ? WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "si", $new_password, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Password updated successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update password!"]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to prepare the query!"]);
    }
}
?>
