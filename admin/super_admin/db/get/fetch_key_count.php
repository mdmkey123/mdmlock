<?php
header('Content-Type: application/json');
include '../master_config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['role'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access or missing role"]);
    exit;
}

$super_admin_id = $_SESSION['user_id'];
$role = $_GET['role']; // Role: main_admin, super_distributor, distributor, retailer
$role_id = isset($_GET['role_id']) ? $_GET['role_id'] : '';

if (empty($role_id)) {
    echo json_encode(["status" => "error", "message" => "role_id is required"]);
    exit;
}

// Define the correct column and NULL conditions based on hierarchy
$conditions = [
    "main_admin" => "
                     assigned_admin = '$role_id'
                     AND assigned_super_distributor IS NULL 
                     AND assigned_distributor IS NULL 
                     AND assigned_retailer IS NULL
                     AND (enrolled_device IS NULL OR enrolled_device = '')",

    "super_distributor" => "assigned_super_distributor = '$role_id' 
                            AND assigned_distributor IS NULL 
                            AND assigned_retailer IS NULL
                            AND (enrolled_device IS NULL OR enrolled_device = '')",

    "distributor" => "assigned_distributor = '$role_id' 
                      AND assigned_retailer IS NULL
                      AND (enrolled_device IS NULL OR enrolled_device = '')",

    "retailer" => "assigned_retailer = '$role_id'
    AND (enrolled_device IS NULL OR enrolled_device = '')"
];

// Check if the role exists in conditions
if (!isset($conditions[$role])) {
    echo json_encode(["status" => "error", "message" => "Invalid role"]);
    exit;
}

// Fetch key count for the requested role
$query = "SELECT COUNT(*) AS key_count FROM enrollment_keys WHERE assigned_super_admin = '$super_admin_id' AND {$conditions[$role]}";
$result = mysqli_query($master_conn, $query);

if (!$result) {
    echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($master_conn)]);
    exit;
}

$row = mysqli_fetch_assoc($result);
$key_count = $row['key_count'];

echo json_encode(["status" => "success", "key_count" => $key_count]);
?>
