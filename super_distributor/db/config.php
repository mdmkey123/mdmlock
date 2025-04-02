<?php
session_start();
include 'master_config.php';

if (!isset($_SESSION['super_admin_id'])) {
    die("Super Admin ID is not set in the session.");
}

$super_admin_id = $_SESSION['super_admin_id'];

$sql = "SELECT username, password, db_name, db_username, db_password FROM super_admin WHERE id = $super_admin_id";
$result = mysqli_query($master_conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    $super_admin_username = $row['username'];
    $super_admin_password = $row['password'];
    $db_name = $row['db_name'];
    $db_username = $row['db_username'];
    $db_password = $row['db_password'];

    mysqli_close($master_conn);

    $conn = mysqli_connect("193.203.184.165", $db_username, $db_password, $db_name);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
} else {
    die("Super Admin not found in master database.");
}
?>
