<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $super_admin_id = $_SESSION['user_id'];
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
        exit;
    }

    $check_query = "SELECT id FROM main_admin WHERE email = '$email' OR mobile_number = '$mobile'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Mobile Number already exists."]);
        exit;
    }

    $query = "SELECT unique_main_admin_id FROM main_admin ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $last_id = (int) substr($row['unique_main_admin_id'], -4);
        $new_id = 'ZYNTROMA' . str_pad($last_id + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_id = 'ZYNTROMA0001';
    }

    $insert_query = "INSERT INTO main_admin (unique_main_admin_id, super_admin_id, name, email, mobile_number, username, password, country_id, status, created_at) 
                     VALUES ('$new_id', '$super_admin_id', '$name', '$email', '$mobile', '$username', '$password', 105, 1, NOW())";

    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(["status" => "success", "message" => "Admin added successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add admin."]);
    }

    mysqli_close($conn);
}
