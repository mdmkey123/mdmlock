<?php
include '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $super_distributor_id = $_SESSION['user_id'];
    $retailer_id = mysqli_real_escape_string($conn, $_POST['id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $gstn_number = mysqli_real_escape_string($conn, $_POST['gstn_number']);
    
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);


    $checkQuery = "SELECT id FROM admin WHERE id = '$retailer_id' AND super_distributor_id = '$super_distributor_id'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) == 0) {
        echo json_encode(["status" => "error", "message" => "Retailer not found!"]);
        exit;
    }

    $duplicateCheckQuery = "SELECT id FROM admin WHERE (email = '$email' OR phone = '$phone') AND id != '$retailer_id'";
    $duplicateResult = mysqli_query($conn, $duplicateCheckQuery);

    if (mysqli_num_rows($duplicateResult) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Phone already in use!"]);
        exit;
    }

    $updateQuery = "UPDATE admin SET 
                    first_name = '$first_name', 
                    last_name = '$last_name', 
                    email = '$email', 
                    phone = '$phone',
                    company_name = '$company_name',
                    gstn_number = '$gstn_number',
                    address = '$address',
                    pincode = '$pincode'
                    WHERE id = '$retailer_id' AND super_distributor_id = '$super_distributor_id'";

    if (mysqli_query($conn, $updateQuery)) {
        echo json_encode(["status" => "success", "message" => "Retailer updated successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update retailer. Try again!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method!"]);
}
?>
