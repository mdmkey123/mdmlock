<?php
include '../config.php';

if (isset($_GET['super_distributor_id']) && !empty($_GET['super_distributor_id'])) {
    $super_distributor_id = mysqli_real_escape_string($conn, $_GET['super_distributor_id']);

    $query = "SELECT id, unique_distributor_id, full_name FROM distributors WHERE super_distributor_id = '$super_distributor_id' AND status = 1";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $distributors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $distributors[] = [
                    'id' => $row['id'],
                    'unique_distributor_id' => $row['unique_distributor_id'],
                    'name' => $row['full_name']
                ];
            }
            echo json_encode($distributors);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['error' => 'Super distributor ID is required']);
}
?>
