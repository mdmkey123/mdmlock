<?php
include '../config.php';
session_start();

$admin_type = $_GET['admin_type'];
$super_admin_id = $_SESSION['user_id']; 

$table_map = [
    'main_admin' => 'main_admin',
    'super_distributor' => 'super_distributor',
    'distributor' => 'distributors',
    'admin' => 'admin'
];

$unique_id_map = [
    'main_admin' => 'unique_main_admin_id',
    'super_distributor' => 'unique_super_distributor_id',
    'distributor' => 'unique_distributor_id', 
    'admin' => 'unique_admin_id'
];

if (!isset($table_map[$admin_type])) {
    echo json_encode([]);
    exit;
}

$table = $table_map[$admin_type];
$unique_id_column = $unique_id_map[$admin_type];

if ($admin_type === 'distributor') {
    $name_column = 'full_name';
} elseif ($admin_type === 'admin') {
    $name_column = "CONCAT(first_name, ' ', last_name) AS name";
} else {
    $name_column = 'name';
}

// Fetch unique ID instead of username
$query = "SELECT id, $name_column, $unique_id_column AS unique_id FROM $table WHERE super_admin_id = '$super_admin_id' AND status = 1";

$result = mysqli_query($conn, $query);

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode($users);
?>
