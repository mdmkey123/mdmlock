<?php
include '../master_config.php';

session_start();

$super_distributor_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Get JSON input
$input_data = json_decode(file_get_contents("php://input"), true);
$super_admin_id = isset($input_data['super_admin_id']) ? intval($input_data['super_admin_id']) : 0;

if ($super_distributor_id === 0 || $super_admin_id === 0) {
    echo json_encode(['key_count' => 0]);
    exit;
}

$sql = "SELECT COUNT(*) as key_count 
        FROM enrollment_keys 
        WHERE (assigned_distributor IS NULL OR assigned_distributor = '') 
        AND (assigned_retailer IS NULL OR assigned_retailer = '') 
        AND (enrolled_device IS NULL OR enrolled_device = '') 
        AND assigned_super_distributor = $super_distributor_id 
        AND assigned_super_admin = $super_admin_id";

$result = $master_conn->query($sql);
$key_count = 0;

if ($result && $row = $result->fetch_assoc()) {
    $key_count = $row['key_count'];
}

$master_conn->close();

echo json_encode(['key_count' => $key_count]);

?>