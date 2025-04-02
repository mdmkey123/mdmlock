<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_distributor_id = $_SESSION['user_id'];
$customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$device_name = isset($_GET['device_name']) ? $_GET['device_name'] : '';
$product_price_min = isset($_GET['product_price_min']) ? $_GET['product_price_min'] : '';
$product_price_max = isset($_GET['product_price_max']) ? $_GET['product_price_max'] : '';
$distributor_id = isset($_GET['distributor_id']) ? $_GET['distributor_id'] : '';
$retailer_id = isset($_GET['retailer_id']) ? $_GET['retailer_id'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query = "SELECT 
            customers.id AS customer_id, customers.name, customers.phone, customers.device_id,
            devices.brand, devices.model, devices.admin_id AS retailer_id,
            devices.status AS device_status,
            devices.locked AS device_locked,
            emi_details.product_price,
            (SELECT emis.amount 
             FROM emis 
             WHERE emis.emi_details_id = emi_details.id 
             ORDER BY emis.emi_date DESC LIMIT 1) AS latest_emi,
            (SELECT emis.emi_date 
             FROM emis 
             WHERE emis.emi_details_id = emi_details.id 
             ORDER BY emis.emi_date DESC LIMIT 1) AS emi_date,
            retailer.first_name AS retailer_first_name, retailer.last_name AS retailer_last_name, 
            retailer.distributor_id, retailer.super_distributor_id,
            distributor.full_name AS distributor_name,
            super_distributor.name AS super_distributor_name
           FROM customers 
           LEFT JOIN devices ON customers.device_id = devices.id 
           LEFT JOIN emi_details ON devices.id = emi_details.device_id
           LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
           LEFT JOIN distributors AS distributor ON retailer.distributor_id = distributor.id
           LEFT JOIN super_distributor ON retailer.super_distributor_id = super_distributor.id
           WHERE retailer.super_distributor_id = '$super_distributor_id'";

if ($customer_name != '') {
    $query .= " AND customers.name LIKE '%$customer_name%'";
}
if ($phone != '') {
    $query .= " AND customers.phone LIKE '%$phone%'";
}
if ($device_name != '') {
    $query .= " AND (devices.brand LIKE '%$device_name%' OR devices.model LIKE '%$device_name%')";
}
if ($product_price_min != '') {
    $query .= " AND emi_details.product_price >= '$product_price_min'";
}
if ($product_price_max != '') {
    $query .= " AND emi_details.product_price <= '$product_price_max'";
}
if ($distributor_id != '') {
    $query .= " AND retailer.distributor_id = '$distributor_id'";
}
if ($retailer_id != '') {
    $query .= " AND retailer.id = '$retailer_id'";
}
if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $query .= " AND customers.created_at BETWEEN '$filter_start_date' AND '$filter_end_date'";
} elseif (!empty($filter_start_date)) {
    $query .= " AND customers.created_at >= '$filter_start_date'";
} elseif (!empty($filter_end_date)) {
    $query .= " AND customers.created_at <= '$filter_end_date'";
}

// Pagination logic
$query .= " LIMIT $offset, $limit";

// Execute the query
$result = mysqli_query($conn, $query);

// Get the total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM customers 
                LEFT JOIN devices ON customers.device_id = devices.id 
                LEFT JOIN emi_details ON devices.id = emi_details.device_id
                LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
                LEFT JOIN distributors AS distributor ON retailer.distributor_id = distributor.id
                LEFT JOIN super_distributor ON retailer.super_distributor_id = super_distributor.id
                WHERE retailer.super_distributor_id = '$super_distributor_id'";

if ($customer_name != '') {
    $total_query .= " AND customers.name LIKE '%$customer_name%'";
}
if ($phone != '') {
    $total_query .= " AND customers.phone LIKE '%$phone%'";
}
if ($device_name != '') {
    $total_query .= " AND (devices.brand LIKE '%$device_name%' OR devices.model LIKE '%$device_name%')";
}
if ($product_price_min != '') {
    $total_query .= " AND emi_details.product_price >= '$product_price_min'";
}
if ($product_price_max != '') {
    $total_query .= " AND emi_details.product_price <= '$product_price_max'";
}
if ($distributor_id != '') {
    $total_query .= " AND retailer.distributor_id = '$distributor_id'";
}
if ($retailer_id != '') {
    $total_query .= " AND retailer.id = '$retailer_id'";
}

$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div id="alert-container" style="display: none;" class="alert"></div>
                        <div class="card-body">
                            <div class="filter-container">
                                <form method="GET" action="">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="customer_name" class="form-label">Customer Name</label>
                                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Customer Name" value="<?= isset($_GET['customer_name']) ? htmlspecialchars($_GET['customer_name']) : '' ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" id="phone" name="phone" class="form-control" placeholder="Phone" value="<?= isset($_GET['phone']) ? htmlspecialchars($_GET['phone']) : '' ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="device_name" class="form-label">Device Name</label>
                                            <input type="text" id="device_name" name="device_name" class="form-control" placeholder="Device Name" value="<?= isset($_GET['device_name']) ? htmlspecialchars($_GET['device_name']) : '' ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_price_min" class="form-label">Min Price</label>
                                            <input type="number" id="product_price_min" name="product_price_min" class="form-control" placeholder="Min Price" value="<?= isset($_GET['product_price_min']) ? $_GET['product_price_min'] : '' ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_price_max" class="form-label">Max Price</label>
                                            <input type="number" id="product_price_max" name="product_price_max" class="form-control" placeholder="Max Price" value="<?= isset($_GET['product_price_max']) ? $_GET['product_price_max'] : '' ?>">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Start Date</label>
                                            <input type="date" name="start_date" class="form-control" value="<?= $filter_start_date ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">End Date</label>
                                            <input type="date" name="end_date" class="form-control" value="<?= $filter_end_date ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="distributor_id" class="form-label">Distributor</label>
                                            <select name="distributor_id" id="distributor_id" class="form-control">
                                                <option value="">Select Distributor</option>
                                                <?php
                                                // Fetch the distributors for the select list
                                                $distributor_query = "SELECT id, full_name FROM distributors WHERE super_distributor_id = '$super_distributor_id' ORDER BY full_name";
                                                $distributor_result = mysqli_query($conn, $distributor_query);
                                                while ($distributor = mysqli_fetch_assoc($distributor_result)) {
                                                    $selected = isset($_GET['distributor_id']) && $_GET['distributor_id'] == $distributor['id'] ? 'selected' : '';
                                                    echo "<option value='{$distributor['id']}' $selected>{$distributor['full_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="retailer_id" class="form-label">Retailer</label>
                                            <select name="retailer_id" id="retailer_id" class="form-control">
                                                <option value="">Select Retailer</option>
                                                <?php
                                                // Fetch the retailers for the select list
                                                $retailer_query = "SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM admin WHERE super_distributor_id = '$super_distributor_id' ORDER BY full_name";
                                                $retailer_result = mysqli_query($conn, $retailer_query);
                                                while ($retailer = mysqli_fetch_assoc($retailer_result)) {
                                                    $selected = isset($_GET['retailer_id']) && $_GET['retailer_id'] == $retailer['id'] ? 'selected' : '';
                                                    echo "<option value='{$retailer['id']}' $selected>{$retailer['full_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                                            <button type="button" class="btn btn-secondary" id="resetFilterBtn">Reset</button>

                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User List</h4>
                        </div>
                        <div id="alert-container" style="display: none;" class="alert"></div>
                        <div class="card-body">


                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Customer Name</th>
                                            <th>Phone</th>
                                            <th>Device Name</th>
                                            <th>Product Price</th>
                                            <th>Latest EMI</th>
                                            <th>EMI Date</th>
                                            <th>Device Status</th>
                                            <th>Lock Status</th>
                                            <th>Retailer</th>
                                            <th>Distributor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            $sr_no = 1;
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $device_name = !empty($row['brand']) && !empty($row['model'])
                                                    ? htmlspecialchars($row['brand'] . ' ' . $row['model'])
                                                    : 'N/A';

                                                $device_status = "<span class='status " . strtolower($row['device_status']) . "'>" . ucfirst($row['device_status']) . "</span>";


                                                // Lock Status (Locked/Unlocked)
                                                $lock_status = ($row['device_locked'] == 1)
                                                    ? "<span class='lock locked'>Locked</span>"
                                                    : "<span class='lock unlocked'>Unlocked</span>";


                                                echo "<tr>";
                                                echo "<td>{$sr_no}</td>";
                                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                                echo "<td>{$device_name}</td>";
                                                echo "<td>" . htmlspecialchars($row['product_price']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['latest_emi']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['emi_date']) . "</td>";
                                                echo"<td>" .$device_status. "</td>";
                                                echo"<td>".$lock_status."</td>";
                                                echo "<td>" . htmlspecialchars($row['retailer_first_name']) . ' ' . htmlspecialchars($row['retailer_last_name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['distributor_name']) . "</td>";
                                                echo "<td><a href='view_customer.php?id={$row['customer_id']}' class='icon-btn'><i class='ri-eye-line'></i></a>
                                                        </td>";
                                                echo "</tr>";

                                                $sr_no++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='10'>No records found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-3">
                                        <?php
                                        if ($page > 1) {
                                            echo "<li><a href='?page=" . ($page - 1) . "'>&laquo;</a></li>";
                                        }
                                        for ($i = 1; $i <= $total_pages; $i++) {
                                            $active = ($i == $page) ? 'active' : '';
                                            echo "<li class='$active'><a href='?page=$i'>$i</a></li>";
                                        }
                                        if ($page < $total_pages) {
                                            echo "<li><a href='?page=" . ($page + 1) . "'>&raquo;</a></li>";
                                        }
                                        ?>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("resetFilterBtn").addEventListener("click", function() {
        // Clear the filter fields
        document.getElementById("customer_name").value = "";
        document.getElementById("phone").value = "";
        document.getElementById("device_name").value = "";
        document.getElementById("product_price_min").value = "";
        document.getElementById("product_price_max").value = "";
        document.getElementById("distributor_id").value = "";
        document.getElementById("retailer_id").value = "";

        // Submit the form to reset the filters
        window.location.href = window.location.pathname; // Redirect to the same page without filters
    });

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function() {
                let customerId = this.getAttribute("data-id");
                if (confirm("Are you sure you want to delete this customer?")) {
                    fetch("db/delete/delete_customer.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "id=" + customerId
                    }).then(() => location.reload());
                }
            });
        });
    });
</script>


<?php include 'footer.php'; ?>