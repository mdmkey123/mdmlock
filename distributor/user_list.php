<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$distributor_id = $_SESSION['user_id']; // Get distributor_id from session

// Pagination logic
$limit = 10; // Entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter logic
$search_name = isset($_POST['search_name']) ? $_POST['search_name'] : '';
$search_phone = isset($_POST['search_phone']) ? $_POST['search_phone'] : '';
$search_device = isset($_POST['search_device']) ? $_POST['search_device'] : '';
$search_price_min = isset($_POST['search_price_min']) ? $_POST['search_price_min'] : '';
$search_price_max = isset($_POST['search_price_max']) ? $_POST['search_price_max'] : '';
$search_emi_min = isset($_POST['search_emi_min']) ? $_POST['search_emi_min'] : '';
$search_emi_max = isset($_POST['search_emi_max']) ? $_POST['search_emi_max'] : '';
$search_emi_date_start = isset($_POST['search_emi_date_start']) ? $_POST['search_emi_date_start'] : '';
$search_emi_date_end = isset($_POST['search_emi_date_end']) ? $_POST['search_emi_date_end'] : '';
$search_retailer = isset($_POST['search_retailer']) ? $_POST['search_retailer'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$retailer_query = "SELECT id, CONCAT(first_name, ' ', last_name) AS retailer_name FROM admin WHERE distributor_id = '$distributor_id' AND status = 1";
$retailer_result = mysqli_query($conn, $retailer_query);

// Build the query with filters
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
            retailer.first_name AS retailer_first_name, retailer.last_name AS retailer_last_name
          FROM customers 
          LEFT JOIN devices ON customers.device_id = devices.id 
          LEFT JOIN emi_details ON devices.id = emi_details.device_id
          LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
          LEFT JOIN distributors AS distributor ON retailer.distributor_id = distributor.id
          WHERE retailer.distributor_id = '$distributor_id'";

if ($search_name) {
    $query .= " AND customers.name LIKE '%" . mysqli_real_escape_string($conn, $search_name) . "%'";
}
if ($search_phone) {
    $query .= " AND customers.phone LIKE '%" . mysqli_real_escape_string($conn, $search_phone) . "%'";
}
if ($search_device) {
    $query .= " AND (devices.brand LIKE '%" . mysqli_real_escape_string($conn, $search_device) . "%' 
                  OR devices.model LIKE '%" . mysqli_real_escape_string($conn, $search_device) . "%')";
}
if ($search_price_min) {
    $query .= " AND emi_details.product_price >= " . (float)$search_price_min;
}
if ($search_price_max) {
    $query .= " AND emi_details.product_price <= " . (float)$search_price_max;
}
if ($search_emi_min) {
    $query .= " AND emis.amount >= " . (float)$search_emi_min;
}
if ($search_emi_max) {
    $query .= " AND emis.amount <= " . (float)$search_emi_max;
}
if ($search_emi_date_start) {
    $query .= " AND emis.emi_date >= '" . mysqli_real_escape_string($conn, $search_emi_date_start) . "'";
}
if ($search_emi_date_end) {
    $query .= " AND emis.emi_date <= '" . mysqli_real_escape_string($conn, $search_emi_date_end) . "'";
}
if ($search_retailer) {
    $query .= " AND retailer.id = '$search_retailer'";
}
if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $query .= " AND customers.created_at BETWEEN '$filter_start_date' AND '$filter_end_date'";
} elseif (!empty($filter_start_date)) {
    $query .= " AND customers.created_at >= '$filter_start_date'";
} elseif (!empty($filter_end_date)) {
    $query .= " AND customers.created_at <= '$filter_end_date'";
}


// Add pagination
$query .= " ORDER BY customers.id DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Count total rows for pagination
$total_query = "SELECT COUNT(*) AS total FROM customers 
                LEFT JOIN devices ON customers.device_id = devices.id 
                LEFT JOIN emi_details ON devices.id = emi_details.device_id
                LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
                WHERE retailer.distributor_id = '$distributor_id'";

$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">User List</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" class="filter-form">
                                <div class="row g-3">
                                    <div class="col-lg-3">
                                        <label for="search_name" class="form-label">Customer Name</label>
                                        <input type="text" name="search_name" id="search_name" class="form-control" placeholder="Customer Name" value="<?= htmlspecialchars($search_name) ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="search_phone" class="form-label">Phone</label>
                                        <input type="text" name="search_phone" id="search_phone" class="form-control" placeholder="Phone" value="<?= htmlspecialchars($search_phone) ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="search_device" class="form-label">Device</label>
                                        <input type="text" name="search_device" id="search_device" class="form-control" placeholder="Device" value="<?= htmlspecialchars($search_device) ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="search_price_min" class="form-label">Min Price</label>
                                        <input type="text" name="search_price_min" id="search_price_min" class="form-control" placeholder="Min Price" value="<?= htmlspecialchars($search_price_min) ?>">
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="search_price_max" class="form-label">Max Price</label>
                                        <input type="text" name="search_price_max" id="search_price_max" class="form-control" placeholder="Max Price" value="<?= htmlspecialchars($search_price_max) ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?= $filter_start_date ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?= $filter_end_date ?>">
                                    </div>

                                    <div class="col-lg-3">
                                        <label for="search_retailer" class="form-label">Retailer</label>
                                        <select name="search_retailer" id="search_retailer" class="form-control">
                                            <option value="">Select Retailer</option>
                                            <?php while ($retailer = mysqli_fetch_assoc($retailer_result)): ?>
                                                <option value="<?= $retailer['id'] ?>" <?= $retailer['id'] == $search_retailer ? 'selected' : '' ?>><?= htmlspecialchars($retailer['retailer_name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>


                                    <div class="col-lg-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                                        <button type="reset" class="btn btn-secondary ms-2" onclick="resetFilters()">Reset</button>
                                    </div>
                                </div>
                            </form>
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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (mysqli_num_rows($result) > 0) {
                                            $sr_no = $offset + 1;
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                $device_name = !empty($row['brand']) && !empty($row['model'])
                                                    ? htmlspecialchars($row['brand'] . ' ' . $row['model'])
                                                    : 'N/A';

                                                $retailer_name = !empty($row['retailer_first_name']) && !empty($row['retailer_last_name'])
                                                    ? htmlspecialchars($row['retailer_first_name'] . ' ' . $row['retailer_last_name'])
                                                    : 'N/A';

                                                $product_price = !empty($row['product_price'])
                                                    ? '₹' . number_format($row['product_price'], 2)
                                                    : 'N/A';

                                                $latest_emi = !empty($row['latest_emi'])
                                                    ? '₹' . number_format($row['latest_emi'], 2)
                                                    : 'N/A';

                                                $emi_date = !empty($row['emi_date'])
                                                    ? date("d-m-Y", strtotime($row['emi_date']))
                                                    : 'N/A';

                                                    $device_status = "<span class='status " . strtolower($row['device_status']) . "'>" . ucfirst($row['device_status']) . "</span>";


                                                    // Lock Status (Locked/Unlocked)
                                                    $lock_status = ($row['device_locked'] == 1)
                                                        ? "<span class='lock locked'>Locked</span>"
                                                        : "<span class='lock unlocked'>Unlocked</span>";
    

                                                echo "<tr>
                                                        <td>{$sr_no}</td>
                                                        <td>" . htmlspecialchars($row['name']) . "</td>
                                                        <td>" . htmlspecialchars($row['phone']) . "</td>
                                                        <td>{$device_name}</td>
                                                        <td>{$product_price}</td>
                                                        <td>{$latest_emi}</td>
                                                        <td>{$emi_date}</td>
                                                        <td>{$device_status}</td>
                                                        <td>{$lock_status}</td>
                                                        <td>{$retailer_name}</td>
                                                        <td><a href='view_customer.php?id={$row['customer_id']}' class='icon-btn'><i class='ri-eye-line'></i></a>
                                                        </td>
                                                    </tr>";
                                                $sr_no++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='9' class='text-center'>No customers found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-3">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item"><a href="?page=1" class="page-link">First</a></li>
                                        <li class="page-item"><a href="?page=<?= $page - 1 ?>" class="page-link">Prev</a></li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a></li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item"><a href="?page=<?= $page + 1 ?>" class="page-link">Next</a></li>
                                        <li class="page-item"><a href="?page=<?= $total_pages ?>" class="page-link">Last</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function resetFilters() {
        // Reset all form fields
        document.querySelector('.filter-form').reset();
        // Optional: Reload page to reset all filters
        window.location.href = window.location.pathname;
    }
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
                        })
                        .then(response => response.json())
                        .then(data => {
                            let alertContainer = document.getElementById("alert-container");
                            alertContainer.style.display = "block";
                            alertContainer.className = data.status === "success" ? "alert alert-success" : "alert alert-danger";
                            alertContainer.innerText = data.message;

                            if (data.status === "success") {
                                setTimeout(() => location.reload(), 1500);
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            });
        });
    });

    document.getElementById("downloadPhoneCSV").addEventListener("click", function() {
        const table = document.querySelector("table.custom-table");
        const rows = table.querySelectorAll("tr");
        let csvContent = "Phone\n"; // Header for CSV file

        rows.forEach(function(row, rowIndex) {
            const columns = row.querySelectorAll("td");
            // Only extract the second column (Phone column)
            if (columns.length > 1) {
                let phone = columns[2].textContent || columns[2].innerText; // Phone is in the 3rd column (index 2)
                phone = phone.trim().replace(/,/g, ""); // Clean phone number
                csvContent += `"${phone}"\n`;
            }
        });

        const hiddenElement = document.createElement("a");
        hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURI(csvContent);
        hiddenElement.target = "_blank";
        hiddenElement.download = "phone_numbers.csv";
        hiddenElement.click();
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php include 'footer.php'; ?>