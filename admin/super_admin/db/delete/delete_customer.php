<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = $_POST['id'];

    if (!empty($customer_id)) {
        $query = "DELETE FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => "success", "message" => "Customer deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete customer"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
