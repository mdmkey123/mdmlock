<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$distributor_id = $_SESSION['user_id'];

function getUserDetails($conn, $user_type, $user_id, $distributor_id)
{
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

    // Check if user belongs to the current distributor
    $query = "SELECT $name_column AS name, $unique_column AS unique_id FROM $table WHERE id = '$user_id' AND super_distributor_id = '$distributor_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    return $user ? $user : ['name' => 'Unknown', 'unique_id' => 'N/A'];
}

// Pagination setup
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$whereClauses = ["second_user_type = 'distributor'", "second_user_id = '$distributor_id'"];
$filters = [];

if (!empty($_GET['transaction_id'])) {
    $whereClauses[] = "transaction_id = '" . mysqli_real_escape_string($conn, $_GET['transaction_id']) . "'";
    $filters['transaction_id'] = $_GET['transaction_id'];
}

if (!empty($_GET['type'])) {
    $whereClauses[] = "type = '" . mysqli_real_escape_string($conn, $_GET['type']) . "'";
    $filters['type'] = $_GET['type'];
}

if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $whereClauses[] = "DATE(created_at) BETWEEN '" . mysqli_real_escape_string($conn, $_GET['from_date']) . "' AND '" . mysqli_real_escape_string($conn, $_GET['to_date']) . "'";
    $filters['from_date'] = $_GET['from_date'];
    $filters['to_date'] = $_GET['to_date'];
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Count total transactions
$total_query = "SELECT COUNT(*) AS total FROM transaction_history $whereSQL";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch paginated transactions
$query = "SELECT * FROM transaction_history $whereSQL ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Apply Filters</h4>
                        </div>
                        <div class="card-body">
                            <form method="GET">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label>Transaction ID</label>
                                        <input type="text" placeholder="Enter Transaction ID" name="transaction_id" class="form-control" value="<?= $filters['transaction_id'] ?? '' ?>">
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
                                        <label>From Date</label>
                                        <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label>To Date</label>
                                        <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                                        <a href="transfer_history.php" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Transfer History</h4>
                            <select id="entriesPerPage" class="form-select ms-3" style="width: 80px;" onchange="updateEntriesPerPage()">
                                <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Transaction ID</th>
                                            <th>Transaction Type</th>
                                            <th>Credited/Debited To</th>
                                            <th>User Details</th>
                                            <th>Amount</th>
                                            <th>Credited/Debited By</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $credited_user = getUserDetails($conn, $row['user_type'], $row['user_id'], $distributor_id);
                                            $sender_user = getUserDetails($conn, $row['second_user_type'], $row['second_user_id'], $distributor_id);
                                            $transaction_type = ucfirst($row['type']);
                                            $type_color = ($row['type'] === 'credit') ? 'success' : 'danger';

                                            echo "<tr>
                                                <td>{$count}</td>
                                                <td>{$row['transaction_id']}</td>
                                                <td><span class='badge bg-{$type_color}'>{$transaction_type}</span></td>
                                                <td>Retailer</td>
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
                            <nav>
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <?php
                                        for ($i = 1; $i <= $total_pages; $i++) {
                                            $active = ($i == $page) ? 'active' : '';
                                            echo "<li class='page-item $active'><a class='page-link' href='?page=$i&limit=$limit'>$i</a></li>";
                                        }
                                        ?>
                                    </ul>
                                </nav>

                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateEntriesPerPage() {
        let limit = document.getElementById("entriesPerPage").value;
        window.location.href = "?page=1&limit=" + limit;
    }
</script>

<?php include 'footer.php'; ?>