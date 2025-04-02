<?php
include '../config.php';
include '../master_config.php';
session_start();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$admin_type = isset($_GET['admin_type']) ? $_GET['admin_type'] : '';

$distributor_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0; // Fetch distributor from session
$key_count = 0;

if ($admin_type && $user_id && $distributor_id) {
    switch ($admin_type) {
        case 'admin': // Fetch keys assigned to retailer under this distributor
            $query = "SELECT COUNT(*) as key_count 
                      FROM enrollment_keys 
                      WHERE assigned_distributor = '$distributor_id'
                      AND assigned_retailer = '$user_id'
                      AND assigned_super_admin = '$super_admin_id'";
            break;

        default:
            echo json_encode(['error' => 'Invalid admin type']);
            exit;
    }

    $result = mysqli_query($master_conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $key_count = $row['key_count'];
    }
}

echo json_encode(['key_count' => $key_count]);
?>
