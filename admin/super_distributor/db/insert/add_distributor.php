<?php
include '../config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $super_distributor_id = $_SESSION['user_id'];

    $query_super_distributor = "SELECT super_admin_id, main_admin_id FROM super_distributor WHERE id = '$super_distributor_id'";
    $result_super_distributor = mysqli_query($conn, $query_super_distributor);
    
    if (!$result_super_distributor || mysqli_num_rows($result_super_distributor) == 0) {
        echo json_encode(["status" => "error", "message" => "Invalid Super Distributor"]);
        exit;
    }
    
    $row = mysqli_fetch_assoc($result_super_distributor);
    $super_admin_id = $row['super_admin_id'];
    $main_admin_id = $row['main_admin_id'];

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $city = $_POST['city'];
    $status = 1;
    $created_at = date("Y-m-d H:i:s");

    if ($password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }

    $check_query = "SELECT id FROM distributors WHERE email = '$email' OR username = '$username'";
    $result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Username already exists"]);
        exit;
    }

    $year = date("Y");
    $month = date("m");
    $fetch_last_id = "SELECT unique_distributor_id FROM distributors WHERE unique_distributor_id LIKE 'ZYNTROD{$year}{$month}%' ORDER BY id DESC LIMIT 1";
    $last_result = mysqli_query($conn, $fetch_last_id);
    $last_id = mysqli_fetch_assoc($last_result)['unique_distributor_id'];

    $new_number = $last_id ? str_pad((int)substr($last_id, -4) + 1, 4, "0", STR_PAD_LEFT) : "0001";
    $unique_distributor_id = "ZYNTROD{$year}{$month}{$new_number}";

    $query = "INSERT INTO distributors (unique_distributor_id, super_admin_id, main_admin_id, super_distributor_id, full_name, email, mobile, username, password_hash, city, wallet, status, created_at) 
              VALUES ('$unique_distributor_id', '$super_admin_id', '$main_admin_id', '$super_distributor_id', '$full_name', '$email', '$mobile', '$username', '$password', '$city', '0', '$status', '$created_at')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success", "message" => "Distributor added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding distributor"]);
    }

    mysqli_close($conn);
}
?>
