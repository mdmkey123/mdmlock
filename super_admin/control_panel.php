<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$super_admin_id = $_SESSION['user_id'];


if(isset($_GET['id'])){
    $id= $_GET['id'];
}

$controlsQuery= mysqli_query($conn,"SELECT * FROM device_controls WHERE device_id='$id'");
$queryRes= $controlsQuery->fetch_assoc();
$selectedControlsIndexes= $queryRes['feature_indexes'];

$featuresQuery= mysqli_query($conn,"SELECT * FROM control_panel_features");


// Fetch Credited & Debited Transactions
function getTransactionTotal($conn, $type, $interval) {
    $stmt = $conn->prepare("SELECT SUM(number_of_keys) AS total FROM transaction_history 
                             WHERE type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL $interval)");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

$credit_day = getTransactionTotal($conn, 'credit', '1 DAY');
$credit_week = getTransactionTotal($conn, 'credit', '7 DAY');
$credit_month = getTransactionTotal($conn, 'credit', '1 MONTH');

$debit_day = getTransactionTotal($conn, 'debit', '1 DAY');
$debit_week = getTransactionTotal($conn, 'debit', '7 DAY');
$debit_month = getTransactionTotal($conn, 'debit', '1 MONTH');

?>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    animation: fadeInUp 0.5s ease-out;
}

.card:hover {
    transform: scale(1.05);
    box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.15);
}

.card-title { font-size: 18px; color: #333; text-align: left; }
.card-text { font-size: 22px; color: #000; text-align: right; }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- Overall Summary -->
            <h4 class="mb-3">Control Panel</h4>
            <div class="row">
                <?php
                while($row= $featuresQuery->fetch_assoc()){
                    $featureIndex= $row['indexes'];
                    if(in_array($featureIndex,explode(",",$selectedControlsIndexes))){
                        $color= "#8DBAFE";
                        $textColor= "#FFFFFF";
                    }else{
                        $color= "#FFFFFF";
                        $textColor= "#000000";
                    }
                    ?>
                        <div class="col-lg-3">
                            <div class="card" style="background-color: <?php echo $color?>; "
                            onclick="toggleStatus('<?php echo $id; ?>', '<?php echo $row['id']; ?>')">
                                <div class="card-body">
                                    <h5 class="card-title" style="color: <?php echo $textColor?>;"><?php echo $row['title']?></h5>
                                </div>
                            </div>
                        </div><?php
                }
                ?>
            </div>

            <!-- Transaction Summary -->
            <!--<h4 class="mb-3 mt-4">Others</h4>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Send EMI Alert</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Device Location</h5>
                        </div>
                    </div>
                </div>
            </div>-->

        </div>
    </div>
</div>
<script>
    function toggleStatus(deviceId,featureId,) {
        // alert(deviceId + " "+ featureId);
        fetch('db/update/control_panel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    device_id: deviceId,
                    feature_id: featureId,  // Example of additional parameter// Another example
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    location.reload();
                    // alert(data.message);
                } else {
                    alert("Failed to update status!");
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>

<?php include 'footer.php'; ?>
