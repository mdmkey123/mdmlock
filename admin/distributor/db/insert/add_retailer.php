<?php
include '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $distributor_id = $_SESSION['user_id']; // Using distributor_id from session

    // Fetch super_admin_id, main_admin_id, and super_distributor_id from the distributors table
    $query_distributor = "SELECT super_distributor_id, main_admin_id, super_admin_id FROM distributors WHERE id = '$distributor_id' LIMIT 1";
    $result_distributor = mysqli_query($conn, $query_distributor);

    if (!$result_distributor || mysqli_num_rows($result_distributor) == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Distributor not found']);
        exit;
    }

    $distributor_data = mysqli_fetch_assoc($result_distributor);
    $super_distributor_id = $distributor_data['super_distributor_id'];
    $main_admin_id = $distributor_data['main_admin_id'];
    $super_admin_id = $distributor_data['super_admin_id'];

    // Retrieve form data
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $company_name = $_POST['company_name'];
    $gstn_number = $_POST['gstn_number'];
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $address = $_POST['address'];
    $pincode = $_POST['pincode'];
    // Check if passwords match
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
        exit;
    }

    // Check if email or phone already exists
    $query_check = "SELECT * FROM admin WHERE email = '$email' OR phone = '$phone' LIMIT 1";
    $result_check = mysqli_query($conn, $query_check);
    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email or Phone number already exists']);
        exit;
    }

    // Generate Unique Admin ID
    $date = date('Ymd');
    $query_count = "SELECT COUNT(*) AS total FROM admin WHERE DATE(created_at) = CURDATE()";
    $result_count = mysqli_query($conn, $query_count);
    $row_count = mysqli_fetch_assoc($result_count);
    $count = $row_count['total'] + 1;
    $unique_id = "ZYNTROR" . $date . str_pad($count, 3, '0', STR_PAD_LEFT);

    // Insert into admin (Retailer) table
    $query = "INSERT INTO admin 
                (first_name, last_name, email, phone, company_name, gstn_number, password,  address, pincode, super_admin_id, main_admin_id, super_distributor_id, distributor_id, created_at, status, unique_admin_id) 
              VALUES 
                ('$first_name', '$last_name', '$email', '$phone',   '$company_name', '$gstn_number', '$password',  '$address', '$pincode','$super_admin_id', '$main_admin_id', '$super_distributor_id', '$distributor_id', NOW(), 1, '$unique_id')";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => 'Retailer added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add retailer: ' . mysqli_error($conn)]);
    }
}
?>
