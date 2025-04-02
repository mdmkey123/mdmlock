<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$main_admin_id = $_SESSION['user_id'];
$query_super_admin = "SELECT name, username, unique_main_admin_id, super_admin_id FROM main_admin WHERE id = '$main_admin_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$name = $row_super_admin['name'] ?? 'N/A';
$username = $row_super_admin['username'] ?? 'N/A';
$unique_main_admin_id = $row_super_admin['unique_main_admin_id'] ?? 'N/A';
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

$total_retailers = $conn->query("SELECT COUNT(*) AS total FROM admin WHERE main_admin_id = '$main_admin_id'")->fetch_assoc()['total'];
$active_retailers = $conn->query("SELECT COUNT(*) AS active FROM admin WHERE status = 1 AND main_admin_id = '$main_admin_id'")->fetch_assoc()['active'];

$total_distributors = $conn->query("SELECT COUNT(*) AS total FROM distributors WHERE main_admin_id = '$main_admin_id'")->fetch_assoc()['total'];

$total_credited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
    FROM transaction_history
    WHERE user_id = '$main_admin_id' AND type = 'credit' AND user_type = 'main_admin'")->fetch_assoc()['total_credited'];
    
$total_credited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_credited
    FROM transaction_history
    WHERE second_user_id = '$main_admin_id' AND type = 'debit' AND second_user_type = 'main_admin'")->fetch_assoc()['total_credited'];

$total_credited = $total_credited_2 + $total_credited_1;

$total_debited_1 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE second_user_id = '$main_admin_id' AND type = 'credit' AND second_user_type = 'main_admin'")->fetch_assoc()['total_debited'];

$total_debited_2 = $conn->query("SELECT SUM(number_of_keys) AS total_debited
    FROM transaction_history
    WHERE user_id = '$main_admin_id' AND type = 'debit' AND user_type = 'main_admin'")->fetch_assoc()['total_debited'];
    
$total_debited = $total_debited_2 + $total_debited_1;

$total_super_distributors = $conn->query("SELECT COUNT(*) AS total FROM super_distributor WHERE main_admin_id = '$main_admin_id'")->fetch_assoc()['total'];
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
.admin-info { font-size: 16px; font-weight: bold;  }
</style>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            
            <h4 class="mb-3">Admin Information</h4>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card p-3">
                        <p class="admin-info"><strong>Name:</strong> <?php echo $name; ?></p>
                        <p class="admin-info"><strong>Username:</strong> <?php echo $username; ?></p>
                        <p class="admin-info"><strong>Unique ID:</strong> <?php echo $unique_main_admin_id; ?></p>
                    </div>
                </div>
            </div>
            
            
            <!-- Dashboard Overview -->
            <h4 class="mb-3 mt-4">Main Admin Dashboard</h4>
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
                            <h5 class="card-title">Total Super Distributors</h5>
                            <p class="card-text"><?php echo $total_super_distributors; ?></p>
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

            <h4 class="mb-3 mt-4">Transaction Key</h4>
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
    const superAdminId = <?php echo $super_admin_id; ?>;

    fetch("db/get/get_keys.php", {
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
