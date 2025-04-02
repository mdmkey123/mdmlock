<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$super_admin_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $main_admin_id = $_POST['main_admin_id'];
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $state_id = $_POST['state_id'];
    $status = 1;
    $created_at = date("Y-m-d H:i:s");

    $year = date("Y");

    $last_id_query = "SELECT unique_super_distributor_id FROM super_distributor ORDER BY id DESC LIMIT 1";
    $last_id_result = mysqli_query($conn, $last_id_query);
    $last_id_row = mysqli_fetch_assoc($last_id_result);

    if ($last_id_row) {
        $last_number = (int)substr($last_id_row['unique_super_distributor_id'], -4);
        $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $new_number = "0001";
    }

    $unique_super_distributor_id = "ZYNTROSD" . $year . $new_number;

    $check_query = "SELECT * FROM super_distributor WHERE email = '$email' OR username = '$username'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Username already exists."]);
        exit;
    }

    $query = "INSERT INTO super_distributor 
                (unique_super_distributor_id, super_admin_id, main_admin_id, name, email, mobile_number, username, password, state_id, status, created_at) 
              VALUES 
                ('$unique_super_distributor_id', '$super_admin_id', '$main_admin_id', '$name', '$email', '$mobile_number', '$username', '$password', '$state_id', '$status', '$created_at')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success", "message" => "Super Distributor added successfully.", "unique_id" => $unique_super_distributor_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add Super Distributor."]);
    }
}
?>
