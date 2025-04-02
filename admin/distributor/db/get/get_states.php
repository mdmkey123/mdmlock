<?php
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $query = "SELECT country_id FROM main_admin WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $country_id = $result['country_id'];

        $stateQuery = "SELECT id, name FROM states WHERE country_id = ?";
        $stmt = $conn->prepare($stateQuery);
        $stmt->bind_param("i", $country_id);
        $stmt->execute();
        $states = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode($states);
    } else {
        echo json_encode([]);
    }
}
?>
