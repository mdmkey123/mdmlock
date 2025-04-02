<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$distributor_id = $_SESSION['user_id'];

$query_distributor = "SELECT full_name, username, unique_distributor_id, super_admin_id 
                      FROM distributors 
                      WHERE id = '$distributor_id'";

$result_distributor = mysqli_query($conn, $query_distributor);
$row_distributor = mysqli_fetch_assoc($result_distributor);

$name = $row_distributor['full_name'] ?? 'N/A';
$username = $row_distributor['username'] ?? 'N/A';
$unique_distributor_id = $row_distributor['unique_distributor_id'] ?? 'N/A';
$super_admin_id = $row_distributor['super_admin_id'] ?? null;


$total_retailers = $conn->query("SELECT COUNT(*) AS total FROM admin WHERE distributor_id = '$distributor_id'")->fetch_assoc()['total'];
$active_retailers = $conn->query("SELECT COUNT(*) AS active FROM admin WHERE status = 1 AND distributor_id = '$distributor_id'")->fetch_assoc()['active'];

$total_credited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
    FROM transaction_history
    WHERE user_id = '$distributor_id' AND type = 'credit' AND user_type = 'distributor'")->fetch_assoc()['total_credited'];

$total_credited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
    FROM transaction_history
    WHERE second_user_id = '$distributor_id' AND type = 'debit' AND second_user_type = 'distributor'")->fetch_assoc()['total_credited'];

$total_credited = $total_credited_2 + $total_credited_1;

$total_debited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE second_user_id = '$distributor_id' AND type = 'credit' AND second_user_type = 'distributor'")->fetch_assoc()['total_debited'];

$total_debited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE user_id = '$distributor_id' AND type = 'debit' AND user_type = 'distributor'")->fetch_assoc()['total_debited'];

$total_debited = $total_debited_2 + $total_debited_1;

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
.table th, .table td { text-align: center; padding: 8px; }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <h4 class="mb-3">Distributor Information</h4>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card p-3">
                        <p class="admin-info"><strong>Name:</strong> <?php echo $name; ?></p>
                        <p class="admin-info"><strong>Username:</strong> <?php echo $username; ?></p>
                        <p class="admin-info"><strong>Unique ID:</strong> <?php echo $unique_distributor_id; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Overview -->
            <h4 class="mb-3">Distributor Dashboard</h4>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Retailers</h5>
                            <p class="card-text"><?php echo $total_retailers; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Active Retailers</h5>
                            <p class="card-text"><?php echo $active_retailers; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance -->
            <h4 class="mb-3 mt-4">Wallet</h4>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Keys Balance</h5>
                            <p class="card-text" id="keyCount"><i class="ri-key-fill"></i> <span class="loading">Loading...</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Credited and Debited Amounts -->
            <h4 class="mb-3 mt-4">Transaction Summary</h4>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Credited</h5>
                            <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_credited, 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Debited</h5>
                            <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_debited, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {


    fetch("db/get/get_key.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then((response) => response.json())
    .then((data) => {
        // Update the key count inside the <p> element
        document.getElementById("keyCount").innerHTML = `<i class="ri-key-fill"></i> ${data.key_count}`;
    })
    .catch((error) => console.error("Error fetching key count:", error));
});

</script>

<?php include 'footer.php'; ?>
