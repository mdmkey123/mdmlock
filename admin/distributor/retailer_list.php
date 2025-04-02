<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$distributor_id = $_SESSION['user_id']; // Get distributor_id from session

$query_super_admin = "SELECT super_admin_id FROM distributor WHERE id = '$distributor_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

// Pagination settings
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

$offset = ($page - 1) * $limit;

// Base query
$query = "SELECT a.*, d.full_name AS distributor_company_name 
          FROM admin a
          LEFT JOIN distributors d ON a.distributor_id = d.id
          WHERE a.distributor_id = '$distributor_id'";

// Apply filters
if (!empty($name)) {
    $query .= " AND (a.first_name LIKE '%$name%' OR a.last_name LIKE '%$name%')";
}

if (!empty($email)) {
    $query .= " AND a.email LIKE '%$email%'";
}

if (!empty($phone)) {
    $query .= " AND a.phone LIKE '%$phone%'";
}

if ($status !== '') {
    $query .= " AND a.status = '$status'";
}

// Get total records
$total_records = mysqli_num_rows(mysqli_query($conn, $query));
$total_pages = ceil($total_records / $limit);

// Apply pagination
$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" class="row g-2">
                                <div class="col-md-3">
                                    <label for="name" class="form-label">Retailer Name</label>
                                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" class="form-control" placeholder="Enter Retailer Name">
                                </div>
                                <div class="col-md-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" name="email" id="email" value="<?= htmlspecialchars($email) ?>" class="form-control" placeholder="Enter Email">
                                </div>
                                <div class="col-md-3">
                                    <label for="phone" class="form-label">Mobile Number</label>
                                    <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" class="form-control" placeholder="Enter Mobile Number">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="retailer_list.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Retailer List</h4>
                            <div class="d-flex">
                                <select id="entriesPerPage" class="form-select ms-3" style="width: 80px;" onchange="loadData(1)">
                                    <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="table-container mt-3">
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
                                                    <td>{$row['gstn_number']}</td>
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
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-3">
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="retailer_list.php?page=<?= $i ?>&limit=<?= $limit ?>&name=<?= urlencode($name) ?>&email=<?= urlencode($email) ?>&phone=<?= urlencode($phone) ?>&status=<?= $status ?>">
                                                <?= $i ?>
                                            </a>
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
    function loadData(page) {
        let limit = document.getElementById('entriesPerPage').value;
        let name = document.querySelector('[name="name"]').value;
        let email = document.querySelector('[name="email"]').value;
        let phone = document.querySelector('[name="phone"]').value;
        let status = document.querySelector('[name="status"]').value;

        window.location.href = `retailer_list.php?page=${page}&limit=${limit}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}&status=${status}`;
    }

    function loadData(page) {
        let search = document.getElementById('searchInput').value;
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = `retailer_list.php?page=${page}&limit=${limit}&search=${search}`;
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