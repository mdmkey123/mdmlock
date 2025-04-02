<?php
include '../config.php';
session_start();

$super_distributor_id = $_SESSION['user_id'];

$query = "SELECT cities.id, cities.city FROM cities 
          JOIN super_distributor ON super_distributor.state_id = cities.state_id
          WHERE super_distributor.id = '$super_distributor_id'";

$result = mysqli_query($conn, $query);

$cities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cities[] = $row;
}

echo json_encode($cities);
mysqli_close($conn);
?>
