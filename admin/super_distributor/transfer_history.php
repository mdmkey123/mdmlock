<?php 
include 'header.php'; 
include 'topbar.php'; 
include 'sidebar.php'; 

$super_distributor_id = $_SESSION['user_id'];

if (!function_exists('getUserDetails')) {
    function getUserDetails($conn, $user_type, $user_id) {
        $table_map = [
            'distributor' => ['table' => 'distributors', 'name_column' => 'full_name', 'unique_column' => 'unique_distributor_id'],
            'admin' => ['table' => 'admin', 'name_column' => 'first_name', 'unique_column' => 'unique_admin_id']
        ];

        if (!isset($table_map[$user_type])) {
            return ['name' => 'Unknown', 'unique_id' => 'N/A'];
        }

        $table = $table_map[$user_type]['table'];
        $name_column = $table_map[$user_type]['name_column'];
        $unique_column = $table_map[$user_type]['unique_column'];

        $query = "SELECT $name_column AS name, $unique_column AS unique_id FROM $table WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        return $user ? $user : ['name' => 'Unknown', 'unique_id' => 'N/A'];
    }
}

$whereClauses = ["second_user_type = 'super_distributor'", "second_user_id = ?"];
$params = [$super_distributor_id];
$filters = [];

// Transaction ID Filter
if (!empty($_GET['transaction_id'])) {
    $whereClauses[] = "transaction_id = ?";
    $params[] = $_GET['transaction_id'];
    $filters['transaction_id'] = $_GET['transaction_id'];
}

// Transaction Type Filter (Credit/Debit)
if (!empty($_GET['type'])) {
    $whereClauses[] = "type = ?";
    $params[] = $_GET['type'];
    $filters['type'] = $_GET['type'];
}

// Date Range Filter
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $whereClauses[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $_GET['start_date'];
    $params[] = $_GET['end_date'];
    $filters['start_date'] = $_GET['start_date'];
    $filters['end_date'] = $_GET['end_date'];
}

$query = "SELECT * FROM transaction_history WHERE " . implode(" AND ", $whereClauses) . " ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <!-- Filters Section -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                    <div class="card-header">
                            <h4 class="card-title mb-0">Apply Filters</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Transaction ID</label>
                                        <input type="text" name="transaction_id" placeholder="Enter Transaction ID" class="form-control" value="<?= $filters['transaction_id'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>Type</label>
                                        <select name="type" class="form-control">
                                            <option value="">All</option>
                                            <option value="credit" <?= (isset($filters['type']) && $filters['type'] == 'credit') ? 'selected' : '' ?>>Credit</option>
                                            <option value="debit" <?= (isset($filters['type']) && $filters['type'] == 'debit') ? 'selected' : '' ?>>Debit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?= $filters['start_date'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?= $filters['end_date'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end mt-2">
                                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                                        <a href="transfer_history.php" class="btn btn-secondary ml-2">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer History Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Transfer History</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="custom-table" id="transfer-history">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Transaction ID</th>
                                            <th>Transaction Type</th>
                                            <th>Credited To/Debited From</th>
                                            <th>User Details</th>
                                            <th>Amount</th>
                                            <th>Credited/Debited By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $credited_user = getUserDetails($conn, $row['user_type'], $row['user_id']);
                                            $sender_user = getUserDetails($conn, $row['second_user_type'], $row['second_user_id']);
                                            $transaction_type = ucfirst($row['type']);
                                            $type_color = ($row['type'] === 'credit') ? 'success' : 'danger';

                                            echo "<tr>
                                                <td>{$count}</td>
                                                <td>{$row['transaction_id']}</td>
                                                <td><span class='badge bg-{$type_color}'>{$transaction_type}</span></td>
                                                <td>" . ucfirst(str_replace('_', ' ', $row['user_type'])) . "</td>
                                                <td>{$credited_user['name']} ({$credited_user['unique_id']})</td>
                                                <td><i class='ri-key-fill'></i> {$row['number_of_keys']}</td>
                                                <td>" . ucfirst(str_replace('_', ' ', $row['second_user_type'])) . "</td>
                                                <td>{$row['created_at']}</td>
                                            </tr>";
                                            $count++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let table = document.getElementById("transfer-history");
        if (table) {
            new DataTable(table, {
                responsive: true,
                paging: true,
                searching: true,
                ordering: true
            });
        }
    });
</script>

<?php include 'footer.php'; ?>  
