<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

$main_admin_id = $_SESSION["user_id"];

$result = mysqli_query($conn, "SELECT super_admin_id FROM main_admin WHERE id = '$main_admin_id'");
$row = mysqli_fetch_assoc($result);
if (!$row) {
    echo json_encode(["status" => "error", "message" => "Main Admin not found."]);
    exit;
}
$super_admin_id = $row["super_admin_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $state_id = $_POST['state_id'];
    $status = 1;
    $created_at = date("Y-m-d H:i:s");

    $year = date("Y");
    $result = mysqli_query($conn, "SELECT unique_super_distributor_id FROM super_distributor ORDER BY id DESC LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    $new_number = ($row) ? str_pad((int)substr($row['unique_super_distributor_id'], -4) + 1, 4, '0', STR_PAD_LEFT) : "0001";
    $unique_id = "ZYNTROSD" . $year . $new_number;

    $check = mysqli_query($conn, "SELECT * FROM super_distributor WHERE email = '$email' OR username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(["status" => "error", "message" => "Email or Username already exists."]);
        exit;
    }

    $query = "INSERT INTO super_distributor (unique_super_distributor_id, super_admin_id, main_admin_id, name, email, mobile_number, username, password, state_id, status, created_at) 
              VALUES ('$unique_id', '$super_admin_id', '$main_admin_id', '$name', '$email', '$mobile', '$username', '$password', '$state_id', '$status', '$created_at')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(["status" => "success", "message" => "Super Distributor added successfully.", "unique_id" => $unique_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add Super Distributor."]);
    }
}
?>
