<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';
include 'db/master_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$super_admin_id = $_SESSION['user_id'];

$query_super_admin = "SELECT name, username, unique_super_admin FROM super_admin WHERE id = '$super_admin_id'";
$result_super_admin = mysqli_query($master_conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$name = $row_super_admin['name'] ?? 'N/A';
$username = $row_super_admin['username'] ?? 'N/A';
$unique_super_admin = $row_super_admin['unique_super_admin'] ?? 'N/A';

// Fetch Distributors Count
$total_distributors = $conn->query("SELECT COUNT(*) AS total FROM distributors")->fetch_assoc()['total'];
$active_distributors = $conn->query("SELECT COUNT(*) AS active FROM distributors WHERE status = 1")->fetch_assoc()['active'];

// Fetch Retailers Count
$total_retailers = $conn->query("SELECT COUNT(*) AS total FROM admin")->fetch_assoc()['total'];
$active_retailers = $conn->query("SELECT COUNT(*) AS active FROM admin WHERE status = 1")->fetch_assoc()['active'];

// Fetch Customers Count
$total_customers = $conn->query("SELECT COUNT(*) AS total FROM customers")->fetch_assoc()['total'];

// Fetch Devices Count
$total_devices = $conn->query("SELECT COUNT(*) AS total FROM devices")->fetch_assoc()['total'];

$total_credited_all = $master_conn->query("SELECT SUM(number_of_keys) AS total_credited
    FROM transaction_history
    WHERE user_id = '$super_admin_id' AND type = 'credit' AND user_type = 'super_admin'")->fetch_assoc()['total_credited'];

function getMasterTransactionData($master_conn, $super_admin_id, $time_condition) {
    // Total Credited Keys in Master Database
    $total_credited_master = $master_conn->query("SELECT SUM(number_of_keys) AS total_credited
        FROM transaction_history
        WHERE user_id = '$super_admin_id' 
        AND type = 'credit' 
        AND user_type = 'super_admin'
        AND $time_condition")->fetch_assoc()['total_credited'] ?? 0;

    return $total_credited_master;
}

// Time Conditions
$conditions = [
    'day'   => "DATE(created_at) = CURDATE()",
    'week'  => "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)",
    'month' => "DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
];

// Fetch data for each time period
$total_credited_master_day   = getMasterTransactionData($master_conn, $super_admin_id, $conditions['day']);
$total_credited_master_week  = getMasterTransactionData($master_conn, $super_admin_id, $conditions['week']);
$total_credited_master_month = getMasterTransactionData($master_conn, $super_admin_id, $conditions['month']);

$total_debited_all = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE second_user_id = '$super_admin_id' AND type = 'credit' AND second_user_type = 'super_admin'")->fetch_assoc()['total_debited'];

$total_reverted_all = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE second_user_id = '$super_admin_id' AND type = 'debit' AND second_user_type = 'super_admin'")->fetch_assoc()['total_debited'];
    
function getTransactionData($conn, $super_admin_id, $time_condition) {
    // Credited (Debited) Keys
    $credited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
        FROM transaction_history
        WHERE user_id = '$super_admin_id' 
        AND type = 'credit' 
        AND user_type = 'super_admin'
        AND $time_condition")->fetch_assoc()['total_credited'] ?? 0;

    $credited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
        FROM transaction_history
        WHERE second_user_id = '$super_admin_id' 
        AND type = 'credit' 
        AND second_user_type = 'super_admin'
        AND $time_condition")->fetch_assoc()['total_credited'] ?? 0;

    $total_credited = $credited_2 + $credited_1;

    // Debited (Reverted) Keys
    $debited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
        FROM transaction_history
        WHERE second_user_id = '$super_admin_id' 
        AND type = 'debit' 
        AND second_user_type = 'super_admin'
        AND $time_condition")->fetch_assoc()['total_debited'] ?? 0;

    $debited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
        FROM transaction_history
        WHERE user_id = '$super_admin_id' 
        AND type = 'debit' 
        AND user_type = 'super_admin'
        AND $time_condition")->fetch_assoc()['total_debited'] ?? 0;

    $total_debited = $debited_2 + $debited_1;

    return [
        'credited' => $total_credited,
        'debited' => $total_debited
    ];
}

// Time Conditions for different periods
$conditions = [
    'day'   => "DATE(created_at) = CURDATE()",
    'week'  => "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)",
    'month' => "DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
];

// Fetch data for each time period
$data = [];
foreach ($conditions as $key => $condition) {
    $data[$key] = getTransactionData($conn, $super_admin_id, $condition);
}

// Assign variables for frontend
$credit_day   = $data['day']['credited'];
$credit_week  = $data['week']['credited'];
$credit_month = $data['month']['credited'];

$debit_day   = $data['day']['debited'];
$debit_week  = $data['week']['debited'];
$debit_month = $data['month']['debited'];


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
            
            <h4 class="mb-3">Super Admin Information</h4>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card p-3">
                        <p class="admin-info"><strong>Name:</strong> <?php echo $name; ?></p>
                        <p class="admin-info"><strong>Username:</strong> <?php echo $username; ?></p>
                        <p class="admin-info"><strong>Unique ID:</strong> <?php echo $unique_super_admin; ?></p>
                    </div>
                </div>
            </div>
            <!-- Overall Summary -->
            <h4 class="mb-3">Overall Summary</h4>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Distributors</h5>
                            <p class="card-text"><?php echo $total_distributors; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Active Distributors</h5>
                            <p class="card-text"><?php echo $active_distributors; ?></p>
                        </div>
                    </div>
                </div> 
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
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers</h5>
                            <p class="card-text"><?php echo $total_customers; ?></p>
                        </div>
                    </div>
                </div> 
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Devices</h5>
                            <p class="card-text"><?php echo $total_devices; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
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

            <h4 class="mb-3 mt-4">Key Credit Summary</h4>
<div class="row">
    <!-- Total Debited Today -->
     <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Credited Today</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_credited_master_day, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Credited This Week</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_credited_master_week, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Credited This Month</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_credited_master_month, 2); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Credited</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_credited_all, 2); ?></p>
            </div>
        </div>
    </div>
    
    
    <h4 class="mb-3 mt-4">Key Debit Summary</h4>
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Debited Today</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($credit_day, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Debited This Week -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Debited This Week</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($credit_week, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Debited This Month -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Debited This Month</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($credit_month, 2); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Debited</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_debited_all, 2); ?></p>
            </div>
        </div>
    </div>
    
    <h4 class="mb-3 mt-4">Key Revert Summary</h4>

    <!-- Total Reverted Today -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Reverted Today</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($debit_day, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Reverted This Week -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Reverted This Week</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($debit_week, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Total Reverted This Month -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Reverted This Month</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($debit_month, 2); ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Reverted</h5>
                <p class="card-text"><i class="ri-key-fill"></i> <?php echo number_format($total_reverted_all, 2); ?></p>
            </div>
        </div>
    </div>
</div>


        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const superAdminId = <?php echo $super_admin_id; ?>;

    fetch("db/get/get_key.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ super_admin_id: superAdminId }),
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
