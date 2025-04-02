<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];


    $query = "SELECT status FROM distributors WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $new_status = ($row['status'] == 'active') ? 'inactive' : 'active';

    $update_query = "UPDATE distributors SET status = '$new_status' WHERE id = '$id'";
    if (mysqli_query($conn, $update_query)) {
        echo json_encode(["status" => "success", "new_status" => $new_status]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}
?>
