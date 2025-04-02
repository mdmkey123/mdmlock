<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$filter_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$filter_email = isset($_GET['email']) ? trim($_GET['email']) : '';
$filter_mobile = isset($_GET['mobile']) ? trim($_GET['mobile']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_admin = isset($_GET['admin']) ? $_GET['admin'] : '';
$filter_unique_id = isset($_GET['unique_id']) ? trim($_GET['unique_id']) : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Build the filter query
$filter_query = " WHERE sd.super_admin_id = $super_admin_id ";

if ($filter_name != '') {
    $filter_query .= " AND sd.name LIKE '%$filter_name%' ";
}
if ($filter_email != '') {
    $filter_query .= " AND sd.email LIKE '%$filter_email%' ";
}
if ($filter_mobile != '') {
    $filter_query .= " AND sd.mobile_number LIKE '%$filter_mobile%' ";
}
if ($filter_status != '') {
    $filter_query .= " AND sd.status = '$filter_status' ";
}
if ($filter_admin != '') {
    $filter_query .= " AND sd.main_admin_id = '$filter_admin' ";
}
if ($filter_unique_id != '') {
    $filter_query .= " AND sd.unique_super_distributor_id LIKE '%$filter_unique_id%' ";
}
if ($filter_start_date != '' && $filter_end_date != '') {
    $filter_query .= " AND sd.created_at BETWEEN '$filter_start_date' AND '$filter_end_date' ";
} elseif ($filter_start_date != '') {
    $filter_query .= " AND sd.created_at >= '$filter_start_date' ";
} elseif ($filter_end_date != '') {
    $filter_query .= " AND sd.created_at <= '$filter_end_date' ";
}

// Get Total Count
$total_query = "SELECT COUNT(*) as total FROM super_distributor sd $filter_query";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT sd.*,
             ma.name AS main_admin_name 
          FROM super_distributor sd
          LEFT JOIN main_admin ma ON sd.main_admin_id = ma.id
          $filter_query
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);
$sr_no = $offset + 1;

// Fetch Admins for the dropdown
$admin_query = "SELECT id, name FROM main_admin WHERE super_admin_id = $super_admin_id";
$admin_result = mysqli_query($conn, $admin_query);
?>


<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <!-- Filters -->
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="limit" value="<?= $limit ?>">

                                <div class="col-lg-3 col-md-6">
                                    <label for="unique_id" class="form-label">Unique ID</label>
                                    <input type="text" id="unique_id" name="unique_id" class="form-control" placeholder="Enter Unique ID" value="<?= $filter_unique_id ?>">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter Name" value="<?= $filter_name ?>">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" id="email" name="email" class="form-control" placeholder="Enter Email" value="<?= $filter_email ?>">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" id="mobile" name="mobile" class="form-control" placeholder="Enter Mobile Number" value="<?= $filter_mobile ?>">
                                </div>


                                <div class="col-lg-3 col-md-6">
                                    <label for="admin" class="form-label">Admin</label>
                                    <select id="admin" name="admin" class="form-select">
                                        <option value="">All Admins</option>
                                        <?php while ($admin = mysqli_fetch_assoc($admin_result)) : ?>
                                            <option value="<?= $admin['id'] ?>" <?= $filter_admin == $admin['id'] ? 'selected' : '' ?>>
                                                <?= $admin['name'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="start_date" class="form-label">Created From</label>
                                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $filter_start_date ?>">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <label for="end_date" class="form-label">Created To</label>
                                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $filter_end_date ?>">
                                </div>

                                <div class="col-lg-3 col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="super_distributor_list.php" class="btn btn-secondary">Reset</a>
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
                            <h4 class="card-title mb-0">Super Distributor List</h4>
                            <select id="entriesSelect" class="form-select" style="width: auto;">
                                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>

                        <div class="card-body">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Unique ID</th>
                                            <th>Admin</th>
                                            <th>Super Distributor</th>
                                            <th>Email</th>
                                            <th>Mobile Number</th>
                                            <th>Username</th>
                                            <th>Password</th>
                                            <th>Keys</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                            <?php
                                            $super_distributor_id = $row['id'];
                                            $statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
                                            $statusText = ucfirst($row['status']);
                                            ?>
                                            <tr>
                                                <td><?= $sr_no++ ?></td>
                                                <td><?= $row['unique_super_distributor_id'] ?></td>
                                                <td><?= $row['main_admin_name'] ?></td>
                                                <td><?= $row['name'] ?></td>
                                                <td><?= $row['email'] ?></td>
                                                <td><?= $row['mobile_number'] ?></td>
                                                <td><?= $row['username'] ?></td>
                                                <td><?= $row['password'] ?></td>
                                                <td><i class='ri-key-fill'></i> <span id='key-count-<?= $super_distributor_id ?>'>Loading...</span></td>
                                                <td>
                                                    <button class='status-btn <?= $statusClass ?>' onclick='toggleStatus(<?= $row['id'] ?>)' id='status-<?= $row['id'] ?>'>
                                                        <?= $statusText ?>
                                                    </button>
                                                </td>
                                                <td>
                                                    <a href='update_super_distributor.php?id=<?= $row['id'] ?>' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                    
                                                    <button class='icon-btn' onclick='openChangePasswordModal(<?= $row['id'] ?>, <?= $row['password']?>)' title='Change Password'>
                                                        <i class='ri-lock-line'></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="pagination-container">
                                <nav>
                                    <ul class="pagination justify-content-center mt-3">
                                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"><?= $i ?></a>
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
</div>

<!-- Change Password Modal -->
<div class="modal" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <input type="hidden" id="superDistributorId" name="id">

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
    
        keyCountElements.forEach((element) => {
            let mainAdminId = element.id.replace("key-count-", "");
            fetchKeyCount(mainAdminId);
        });
    });
    
    function fetchKeyCount(mainAdminId) {
        fetch(`db/get/fetch_key_count.php?role=super_distributor&role_id=${mainAdminId}`)
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
    
    function loadData(page) {
        let search = document.getElementById('searchInput').value;
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = `super_distributor_list.php?page=${page}&limit=${limit}&search=${search}`;
    }

    function resetSearch() {
        document.getElementById('searchInput').value = '';
        loadData(1);
    }

    function openChangePasswordModal(id, oldPassword) {
        document.getElementById('superDistributorId').value = id;
        document.getElementById('oldPassword').value = oldPassword; // Show actual old password
        document.getElementById('newPassword').value = ''; // Clear new password field
        document.getElementById('confirmPassword').value = ''; // Clear confirm password field
        $('#changePasswordModal').modal('show');
    }


    function resetFilters() {
        window.location.href = "super_distributor_list.php";
    }
    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_super_distributor_password.php', {
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
        fetch('db/update/update_super_distributor_status.php', {
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
                    button.classList.toggle('active-status', data.new_status === "active");
                    button.classList.toggle('inactive-status', data.new_status === "inactive");
                    button.textContent = data.new_status === "active" ? "Active" : "Inactive";
                } else {
                    alert("Failed to update status!");
                }
            });
    }

    function deleteSuperDistributor(id) {
        if (confirm("Are you sure you want to delete this super distributor?")) {
            fetch('db/delete/delete_super_distributor.php', {
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
                });
        }
    }
</script>

<?php include 'footer.php'; ?>