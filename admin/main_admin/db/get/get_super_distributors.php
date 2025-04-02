<?php
include '../config.php';

if (isset($_GET['main_admin_id'])) {
    $main_admin_id = $_GET['main_admin_id'];
    $query = "SELECT id, unique_super_distributor_id, state_id, name FROM super_distributor WHERE main_admin_id = $main_admin_id";
    $result = mysqli_query($conn, $query);

    $distributors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $distributors[] = $row;
    }

    echo json_encode($distributors);
}
?>
