<?php
include '../config.php';

if (isset($_GET['country_id'])) {
    $country_id = $_GET['country_id'];
    $query = "SELECT id, name FROM states WHERE country_id = $country_id";
    $result = mysqli_query($conn, $query);
    
    $states = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $states[] = $row;
    }

    echo json_encode($states);
}
?>
