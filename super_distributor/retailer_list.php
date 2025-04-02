<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_distributor_id = $_SESSION['user_id'];
$query_super_admin = "SELECT super_admin_id FROM super_distributor WHERE id = '$super_distributor_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

// Handle filters
$filter_name = isset($_GET['name']) ? $_GET['name'] : '';
$filter_email = isset($_GET['email']) ? $_GET['email'] : '';
$filter_mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_distributor = isset($_GET['distributor_id']) ? $_GET['distributor_id'] : '';


// Handle pagination and entries per page
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default 10 entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to page 1
$offset = ($page - 1) * $limit;

// Base query
$whereClauses = ["a.super_distributor_id = '$super_distributor_id'"];

// Apply filters if provided
if (!empty($filter_name)) {
    $whereClauses[] = "CONCAT(a.first_name, ' ', a.last_name) LIKE '%$filter_name%'";
}
if (!empty($filter_email)) {
    $whereClauses[] = "a.email LIKE '%$filter_email%'";
}
if (!empty($filter_mobile)) {
    $whereClauses[] = "a.phone LIKE '%$filter_mobile%'";
}
if ($filter_status !== '') {
    $whereClauses[] = "a.status = '$filter_status'";
}
if (!empty($filter_distributor)) {
    $whereClauses[] = "a.distributor_id = '$filter_distributor'";
}

// Combine conditions
$whereSql = count($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// Get total retailers count for pagination
$countQuery = "SELECT COUNT(*) AS total FROM admin a $whereSql";
$countResult = mysqli_query($conn, $countQuery);
$totalRetailers = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRetailers / $limit);

// Fetch filtered & paginated retailers
$query = "SELECT a.*, 
            sd.name AS super_distributor_name
          FROM admin a
          LEFT JOIN super_distributor sd ON a.super_distributor_id = sd.id
          $whereSql
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <!-- Filter Form -->
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label for="name" class="form-label">Retailer Name</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Retailer Name" value="<?= htmlspecialchars($filter_name) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="<?= htmlspecialchars($filter_email) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" id="mobile" name="mobile" class="form-control" placeholder="Mobile Number" value="<?= htmlspecialchars($filter_mobile) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="distributor_id" class="form-label">Select Distributor</label>
                                    <select id="distributor_id" name="distributor_id" class="form-select">
                                        <option value="">All Distributors</option>
                                        <?php
                                        $distributorQuery = "SELECT id, full_name FROM distributors WHERE super_distributor_id = '$super_distributor_id'";
                                        $distributorResult = mysqli_query($conn, $distributorQuery);
                                        while ($distributor = mysqli_fetch_assoc($distributorResult)) {
                                            $selected = ($distributor['id'] == $filter_distributor) ? 'selected' : '';
                                            echo "<option value='{$distributor['id']}' $selected>{$distributor['full_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
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
                            <h4 class="card-title mb-0">Retailer List</h4>
                            <div class="d-flex">
                                <select class="form-select ms-3" style="width: 80px;" id="entriesPerPage" onchange="changeEntriesPerPage()">
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Unique ID</th>
                                            <th>Retailer Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Password</th>
                                            <th>Company Name</th>
                                            <th>GST Number</th>
                                            <th>Keys</th>
                                            <th>Address</th>
                                            <th>Pincode</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sr_no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $retailer_id = $row['id'];
                                            $statusClass = ($row['status'] == 1) ? 'active-status' : 'inactive-status';
                                            $statusText = ($row['status'] == 1) ? 'Active' : 'Inactive';

                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_admin_id']}</td>
                                                    <td>{$row['first_name']} {$row['last_name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['phone']}</td>
                                                    <td>{$row['password']}</td>
                                                    <td>{$row['company_name']}</td>
                                                    <td>{$row['gstn_number']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $retailer_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td>{$row['address']}</td>
                                                    <td>{$row['pincode']}</td>
                                                    <td>
                                                        <button class='status-btn $statusClass' onclick='toggleStatus({$row['id']})' id='status-{$row['id']}'>
                                                            $statusText
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <a href='update_retailer.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                        <button class='icon-btn' onclick='viewRetailer({$row['id']})' title='View'>
                                                            <i class='ri-eye-line'></i>
                                                        </button>
                                                        <button class='icon-btn' onclick='openChangePasswordModal({$row['id']}, {$row['password']})' title='Change Password'>
                                                            <i class='ri-lock-line'></i>
                                                        </button>
                                                    </td>
                                                  </tr>";
                                            $sr_no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <nav>
                                <ul class="pagination justify-content-center mt-3">
                                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"><?= $i ?></a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <input type="hidden" id="retailerId" name="id">
                    
                    <div class="mb-3">
                        <label for="oldPassword" class="form-label">Old Password</label>
                        <input type="text" class="form-control" id="oldPassword" name="old_password" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let keyCountElements = document.querySelectorAll("[id^='key-count-']");
        let superAdminId = "<?php echo $super_admin_id; ?>"; // Get PHP variable

        keyCountElements.forEach((element) => {
            let mainAdminId = element.id.replace("key-count-", "");
            fetchKeyCount(mainAdminId, superAdminId); // Pass superAdminId here
        });
    });

    function fetchKeyCount(mainAdminId, superAdminId) {
        fetch(`db/get/fetch_key_count.php?role=retailer&role_id=${mainAdminId}&super_admin_id=${superAdminId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById(`key-count-${mainAdminId}`).innerText = data.key_count;
                } else {
                    document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
                    console.error("Error fetching key count:", data.message);
                }
            })
            .catch(error => {
                document.getElementById(`key-count-${mainAdminId}`).innerText = "Error";
                console.error('Error:', error);
            });
    }
    
    function resetFilters() {
        window.location.href = "?page=1&limit=<?= $limit ?>";
    }

    function changeEntriesPerPage() {
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = "?page=1&limit=" + limit;
    }

    function viewRetailer(id) {
        window.location.href = 'view_retailer.php?id=' + id;
    }

    function openChangePasswordModal(id, password) {
        document.getElementById('retailerId').value = id;
        document.getElementById('oldPassword').value = password;
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        $('#changePasswordModal').modal('show');
    }


    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_retailer_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message);
                    $('#changePasswordModal').modal('hide');
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    });

    function toggleStatus(id) {
        fetch('db/update/update_retailer_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    let button = document.getElementById('status-' + id);
                    if (data.new_status === 1) {
                        button.classList.remove('inactive-status');
                        button.classList.add('active-status');
                        button.textContent = "Active";
                    } else {
                        button.classList.remove('active-status');
                        button.classList.add('inactive-status');
                        button.textContent = "Inactive";
                    }
                } else {
                    alert("Failed to update status!");
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function deleteRetailer(id) {
        if (confirm("Are you sure you want to delete this retailer?")) {
            fetch('db/delete/delete_retailer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === "success") {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }
</script>

<?php include 'footer.php'; ?>