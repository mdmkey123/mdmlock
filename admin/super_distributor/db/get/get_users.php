<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$super_distributor_id = $_SESSION['user_id']; // Fetch main_admin_id from session
$admin_type = $_GET['admin_type'] ?? '';

$table_map = [
    'distributor' => 'distributors',
    'admin' => 'admin'
];

$unique_id_map = [
    'distributor' => 'unique_distributor_id', 
    'admin' => 'unique_admin_id'
];

if (!isset($table_map[$admin_type])) {
    echo json_encode([]);
    exit;
}

$table = $table_map[$admin_type];
$unique_id_column = $unique_id_map[$admin_type];

// Set correct name column
if ($admin_type === 'distributor') {
    $name_column = 'username';
} elseif ($admin_type === 'admin') {
    $name_column = "CONCAT(first_name, ' ', last_name) AS name";
} else {
    $name_column = 'name';
}

// Fetch users based on main_admin_id
$query = "SELECT id, $name_column, $unique_id_column AS unique_id FROM $table WHERE super_distributor_id = '$super_distributor_id' AND status = 1";
$result = mysqli_query($conn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode($users);
?>
