<?php
include '../config.php';

$id = $_POST['id'];
$name = $_POST['full_name'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];
$username = $_POST['username'] . "@admin";

$query = "UPDATE main_admin SET name='$name', email='$email', mobile_number='$mobile', username='$username', updated_at=NOW() WHERE id='$id'";

if (mysqli_query($conn, $query)) {
    echo json_encode(["status" => "success", "message" => "Admin updated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update admin."]);
}
?>
