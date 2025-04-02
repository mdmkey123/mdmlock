<?php
include '../config.php';

if (isset($_POST['id']) && isset($_POST['wallet'])) {
    $id = $_POST['id'];
    $wallet = $_POST['wallet'];

    $id = mysqli_real_escape_string($conn, $id);
    $wallet = mysqli_real_escape_string($conn, $wallet);

    $query = "UPDATE distributors SET wallet = '$wallet' WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update wallet balance']);
    }
}
?>
