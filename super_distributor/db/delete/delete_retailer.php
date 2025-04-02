<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $checkQuery = "SELECT id FROM admin WHERE id = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $deleteQuery = "DELETE FROM admin WHERE id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "i", $id);
        $success = mysqli_stmt_execute($deleteStmt);

        if ($success) {
            echo json_encode(["status" => "success", "message" => "Retailer deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete retailer"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Retailer not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

mysqli_close($conn);
?>
