<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $username = $username . "@superdistributor";

    $checkQuery = "SELECT id FROM super_distributor WHERE (email = '$email' OR mobile_number = '$mobile') AND id != '$id'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Mobile Number already exists!"]);
        exit;
    }

    $updateQuery = "UPDATE super_distributor SET 
                    name = '$full_name', 
                    email = '$email', 
                    mobile_number = '$mobile', 
                    username = '$username'
                    WHERE id = '$id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(["status" => "success", "message" => "Super Distributor updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update Super Distributor!"]);
    }
}
?>
