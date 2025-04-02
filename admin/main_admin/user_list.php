<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$main_admin_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) AS total FROM customers 
                LEFT JOIN admin AS retailer ON customers.device_id = retailer.id
                WHERE retailer.main_admin_id = '$main_admin_id'";

$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

$filter_customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
$filter_phone = isset($_GET['phone']) ? $_GET['phone'] : '';
$filter_device = isset($_GET['device']) ? $_GET['device'] : '';
$filter_price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$filter_price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
$filter_emi_min = isset($_GET['emi_min']) ? $_GET['emi_min'] : '';
$filter_emi_max = isset($_GET['emi_max']) ? $_GET['emi_max'] : '';
$filter_emi_date = isset($_GET['emi_date']) ? $_GET['emi_date'] : '';
$filter_retailer = isset($_GET['retailer']) ? $_GET['retailer'] : '';
$filter_distributor = isset($_GET['distributor']) ? $_GET['distributor'] : '';
$filter_super_distributor = isset($_GET['super_distributor']) ? $_GET['super_distributor'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';


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
            retailer.distributor_id, retailer.super_distributor_id, retailer.main_admin_id,

            distributor.full_name AS distributor_name,
            super_distributor.name AS super_distributor_name,
            main_admin.name AS main_admin_name
          
          FROM customers 
          LEFT JOIN devices ON customers.device_id = devices.id 
          LEFT JOIN emi_details ON devices.id = emi_details.device_id
          LEFT JOIN admin AS retailer ON devices.admin_id = retailer.id
          LEFT JOIN distributors AS distributor ON retailer.distributor_id = distributor.id
          LEFT JOIN super_distributor ON retailer.super_distributor_id = super_distributor.id
          LEFT JOIN main_admin ON retailer.main_admin_id = main_admin.id
          
          WHERE retailer.main_admin_id = '$main_admin_id'";

if (!empty($filter_customer_name)) $query .= " AND customers.name LIKE '%$filter_customer_name%'";
if (!empty($filter_phone)) $query .= " AND customers.phone LIKE '%$filter_phone%'";
if (!empty($filter_device)) $query .= " AND (devices.brand LIKE '%$filter_device%' OR devices.model LIKE '%$filter_device%')";
if (!empty($filter_price_min)) $query .= " AND emi_details.product_price >= $filter_price_min";
if (!empty($filter_price_max)) $query .= " AND emi_details.product_price <= $filter_price_max";
if (!empty($filter_emi_min)) $query .= " AND latest_emi >= $filter_emi_min";
if (!empty($filter_emi_max)) $query .= " AND latest_emi <= $filter_emi_max";
if (!empty($filter_emi_date)) $query .= " AND emi_date = '$filter_emi_date'";
if (!empty($filter_retailer)) $query .= " AND retailer.id = '$filter_retailer'";
if (!empty($filter_distributor)) $query .= " AND distributor.id = '$filter_distributor'";
if (!empty($filter_super_distributor)) $query .= " AND super_distributor.id = '$filter_super_distributor'";
if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $query .= " AND customers.created_at BETWEEN '$filter_start_date' AND '$filter_end_date'";
} elseif (!empty($filter_start_date)) {
    $query .= " AND customers.created_at >= '$filter_start_date'";
} elseif (!empty($filter_end_date)) {
    $query .= " AND customers.created_at <= '$filter_end_date'";
}
$query .= " ORDER BY customers.id DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <form method="GET" action="">
                                <div class="row g-3">
                                    <!-- First Row -->
                                    <div class="col-md-3">
                                        <label class="form-label">Customer Name</label>
                                        <input type="text" name="customer_name" class="form-control" placeholder="Enter Name" value="<?= $filter_customer_name ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" placeholder="Enter Phone" value="<?= $filter_phone ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Device Name</label>
                                        <input type="text" name="device" class="form-control" placeholder="Enter Device" value="<?= $filter_device ?>">
                                    </div>

                                    <!-- Second Row -->
                                    <div class="col-md-3">
                                        <label class="form-label">Min Price</label>
                                        <input type="number" name="price_min" class="form-control" placeholder="Min Price" value="<?= $filter_price_min ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Max Price</label>
                                        <input type="number" name="price_max" class="form-control" placeholder="Max Price" value="<?= $filter_price_max ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" value="<?= $filter_start_date ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control" value="<?= $filter_end_date ?>">
                                    </div>

                                    <!-- Third Row - Dropdowns -->
                                    <div class="col-md-3">
                                        <label class="form-label">Retailer</label>
                                        <select name="retailer" class="form-control">
                                            <option value="">Select Retailer</option>
                                            <?php
                                            $retailer_query = mysqli_query($conn, "SELECT id, first_name, last_name FROM admin WHERE main_admin_id = '$main_admin_id'");
                                            while ($retailer = mysqli_fetch_assoc($retailer_query)) {
                                                $selected = ($retailer['id'] == $filter_retailer) ? "selected" : "";
                                                echo "<option value='{$retailer['id']}' $selected>{$retailer['first_name']} {$retailer['last_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Distributor</label>
                                        <select name="distributor" class="form-control">
                                            <option value="">Select Distributor</option>
                                            <?php
                                            $distributor_query = mysqli_query($conn, "SELECT id, full_name FROM distributors WHERE super_distributor_id IN (SELECT id FROM super_distributor WHERE main_admin_id = '$main_admin_id')");
                                            while ($distributor = mysqli_fetch_assoc($distributor_query)) {
                                                $selected = ($distributor['id'] == $filter_distributor) ? "selected" : "";
                                                echo "<option value='{$distributor['id']}' $selected>{$distributor['full_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Super Distributor</label>
                                        <select name="super_distributor" class="form-control">
                                            <option value="">Select Super Distributor</option>
                                            <?php
                                            $super_distributor_query = mysqli_query($conn, "SELECT id, name FROM super_distributor WHERE main_admin_id = '$main_admin_id'");
                                            while ($super_distributor = mysqli_fetch_assoc($super_distributor_query)) {
                                                $selected = ($super_distributor['id'] == $filter_super_distributor) ? "selected" : "";
                                                echo "<option value='{$super_distributor['id']}' $selected>{$super_distributor['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">User List</h4>
                            <div class="d-flex">
                                <select id="entriesPerPage" class="form-select ms-3" style="width: 80px;"
                                    onchange="changeLimit()">
                                    <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                        </div>
                        <div id="alert-container" style="display: none;" class="alert"></div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="custom-table" id="userTable">
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
                                            <th>Super Distributor</th>
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
                                                        <td>₹" . number_format($row['product_price'], 2) . "</td>
                                                        <td>₹" . number_format($row['latest_emi'], 2) . "</td>
                                                        <td>" . date("d-m-Y", strtotime($row['emi_date'])) . "</td>
                                                        <td>{$device_status}</td>
                                                        <td>{$lock_status}</td>
                                                        <td>{$retailer_name}</td>
                                                        <td>" . htmlspecialchars($row['distributor_name']) . "</td>
                                                        <td>" . htmlspecialchars($row['super_distributor_name']) . "</td>
                                                        <td>
                                                            <a href='view_customer.php?id={$row['customer_id']}' class='icon-btn'><i class='ri-eye-line'></i></a>
                                                        </td>
                                                    </tr>";
                                                $sr_no++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='12' class='text-center'>No customers found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>



                            </div>

                            <!-- Pagination -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-3">
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="user_list.php?page=<?= $i ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
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
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".download-pdf-btn").forEach(button => {
            button.addEventListener("click", function() {
                let customerId = this.getAttribute("data-id");

                // Send AJAX request to fetch customer details by ID
                fetch('db/get/get_customer_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: customerId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const customer = data.customer;

                            const {
                                jsPDF
                            } = window.jspdf;
                            const doc = new jsPDF();

                            // Title and Content
                            doc.setFont("helvetica", "normal");
                            doc.setFontSize(16);
                            doc.text('Customer Details', 10, 10);

                            // Adjust starting Y-coordinate for customer details
                            let yPosition = 20;

                            // Adding customer details to the PDF
                            doc.setFontSize(12);
                            doc.text('ID: ' + customerId, 10, yPosition);
                            yPosition += 10;

                            doc.text('Name: ' + customer.name, 10, yPosition);
                            yPosition += 10;

                            doc.text('Phone: ' + customer.phone, 10, yPosition);
                            yPosition += 10;

                            doc.text('Product Price: ₹' + (customer.product_price || 'N/A'), 10, yPosition);
                            yPosition += 10;

                            doc.text('Latest EMI: ₹' + (customer.latest_emi || 'N/A'), 10, yPosition);
                            yPosition += 10;

                            doc.text('EMI Date: ' + (customer.emi_date || 'N/A'), 10, yPosition);
                            yPosition += 10;

                            // Save the PDF
                            doc.save('customer_details_' + customerId + '.pdf');
                        } else {
                            alert('Error fetching customer details!');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });


    function resetFilters() {
        window.location.href = "user_list.php";
    }

    function changeLimit() {
        let newLimit = document.getElementById("entriesPerPage").value;
        window.location.href = "user_list.php?page=1&limit=" + newLimit;
    }

    function filterTable() {
        let value = document.getElementById("searchInput").value.toLowerCase();
        document.querySelectorAll("#userTable tbody tr").forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
        });
    }

    function resetSearch() {
        document.getElementById("searchInput").value = "";
        filterTable();
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
                    }).then(() => location.reload());
                }
            });
        });
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php include 'footer.php'; ?>