<?php
include '../config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $query = "SELECT password_hash FROM distributors WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode(["status" => "success", "password" => $row['password_hash']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Password not found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
