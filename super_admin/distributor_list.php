<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];
$limit = 10; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Initialize WHERE clause
$whereClause = "WHERE d.super_admin_id = $super_admin_id";

// Apply filters
if (!empty($_GET['super_distributor_id'])) {
    $super_distributor_id = mysqli_real_escape_string($conn, $_GET['super_distributor_id']);
    $whereClause .= " AND d.super_distributor_id = '$super_distributor_id'";
}

if (!empty($_GET['main_admin_id'])) {
    $main_admin_id = mysqli_real_escape_string($conn, $_GET['main_admin_id']);
    $whereClause .= " AND d.main_admin_id = '$main_admin_id'";
}

if (!empty($_GET['name'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $whereClause .= " AND d.full_name LIKE '%$name%'";
}

if (!empty($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $whereClause .= " AND d.email LIKE '%$email%'";
}

if (!empty($_GET['mobile'])) {
    $mobile = mysqli_real_escape_string($conn, $_GET['mobile']);
    $whereClause .= " AND d.mobile LIKE '%$mobile%'";
}

if (!empty($_GET['status'])) {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $whereClause .= " AND d.status = '$status'";
}

if (!empty($_GET['created_from']) && !empty($_GET['created_to'])) {
    $createdFrom = mysqli_real_escape_string($conn, $_GET['created_from']);
    $createdTo = mysqli_real_escape_string($conn, $_GET['created_to']);
    $whereClause .= " AND DATE(d.created_at) BETWEEN '$createdFrom' AND '$createdTo'";
} elseif (!empty($_GET['created_from'])) {
    $createdFrom = mysqli_real_escape_string($conn, $_GET['created_from']);
    $whereClause .= " AND DATE(d.created_at) >= '$createdFrom'";
} elseif (!empty($_GET['created_to'])) {
    $createdTo = mysqli_real_escape_string($conn, $_GET['created_to']);
    $whereClause .= " AND DATE(d.created_at) <= '$createdTo'";
}

// Fetch filtered distributors
$query = "SELECT d.*, ma.name AS main_admin_name, sd.name AS super_distributor_name
          FROM distributors d
          LEFT JOIN main_admin ma ON d.main_admin_id = ma.id
          LEFT JOIN super_distributor sd ON d.super_distributor_id = sd.id
          $whereClause
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Count total filtered records for pagination
$countQuery = "SELECT COUNT(*) AS total FROM distributors d $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalDistributors = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalDistributors / $limit);
?>
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">


                        <div class="card-body">
                            <!-- Filters Section -->
                            <form id="filterForm" method="GET">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="super_distributor_id" class="form-label">Super Distributor</label>
                                        <select class="form-select" id="super_distributor_id" name="super_distributor_id">
                                            <option value="">All</option>
                                            <?php
                                            $sdQuery = "SELECT id, name FROM super_distributor";
                                            $sdResult = mysqli_query($conn, $sdQuery);
                                            while ($sd = mysqli_fetch_assoc($sdResult)) {
                                                $selected = (isset($_GET['super_distributor_id']) && $_GET['super_distributor_id'] == $sd['id']) ? 'selected' : '';
                                                echo "<option value='{$sd['id']}' $selected>{$sd['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="main_admin_id" class="form-label">Main Admin</label>
                                        <select class="form-select" id="main_admin_id" name="main_admin_id">
                                            <option value="">All</option>
                                            <?php
                                            $maQuery = "SELECT id, name FROM main_admin";
                                            $maResult = mysqli_query($conn, $maQuery);
                                            while ($ma = mysqli_fetch_assoc($maResult)) {
                                                $selected = (isset($_GET['main_admin_id']) && $_GET['main_admin_id'] == $ma['id']) ? 'selected' : '';
                                                echo "<option value='{$ma['id']}' $selected>{$ma['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= isset($_GET['name']) ? $_GET['name'] : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" class="form-control" id="email" name="email" value="<?= isset($_GET['email']) ? $_GET['email'] : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="mobile" class="form-label">Mobile</label>
                                        <input type="text" class="form-control" id="mobile" name="mobile" value="<?= isset($_GET['mobile']) ? $_GET['mobile'] : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="created_from" class="form-label">Created From</label>
                                        <input type="date" class="form-control" id="created_from" name="created_from" value="<?= isset($_GET['created_from']) ? $_GET['created_from'] : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="created_to" class="form-label">Created To</label>
                                        <input type="date" class="form-control" id="created_to" name="created_to" value="<?= isset($_GET['created_to']) ? $_GET['created_to'] : '' ?>">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Apply</button>
                                        <a href="distributor_list.php" class="btn btn-secondary">Reset</a>
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
                            <h4 class="card-title mb-0">Distributor List</h4>
                            <div class="d-flex">
                                <select class="form-select ms-3" style="width: 80px;" id="entriesPerPage" onchange="changeEntriesPerPage()">
                                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filters Section -->
                        <div class="card-body">

                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Unique ID</th>
                                            <th>Super Distributor</th>
                                            <th>Admin</th>
                                            <th>Distributor</th>
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
                                        <?php
                                        $sr_no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $distributor_id = $row['id'];
                                            $statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_distributor_id']}</td>
                                                    <td>{$row['super_distributor_name']}</td>
                                                    <td>{$row['main_admin_name']}</td>
                                                    <td>{$row['full_name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['mobile']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['password_hash']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $distributor_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td><button class='status-btn $statusClass' onclick='toggleStatus({$row['id']})'>{$row['status']}</button></td>
                                                    <td>
                                                        <a href='update_distributor.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                        <button class='icon-btn' onclick='viewDistributor({$row['id']})'><i class='ri-eye-line'></i></button>
                                                        <button class='icon-btn' onclick='openChangePasswordModal({$row['id']}, {$row['password_hash']})' title='Change Password'>
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

                            <nav>
                                <ul class="pagination justify-content-center mt-3">
                                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>"> <?= $i ?> </a>
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
                    <input type="hidden" id="distributorId" name="id">
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
        fetch(`db/get/fetch_key_count.php?role=distributor&role_id=${mainAdminId}`)
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
    
    function submitForm() {
        document.getElementById('filterForm').submit();
    }

    function openChangePasswordModal(id, oldPassword) {
        document.getElementById('distributorId').value = id;
        document.getElementById('oldPassword').value = oldPassword; // Show actual old password
        document.getElementById('newPassword').value = ''; // Clear new password field
        document.getElementById('confirmPassword').value = ''; // Clear confirm password field
        $('#changePasswordModal').modal('show');
    }

    function changeEntriesPerPage() {
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = 'distributor_list.php?limit=' + limit + '&page=1';
    }

    function loadData(page = 1) {
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = 'distributor_list.php?page=' + page + '&limit=' + limit;
    }


    function viewDistributor(id) {
        window.location.href = 'view_distributor.php?id=' + id;
    }

    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_distributor_password.php', {
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
        fetch('db/update/update_distributor_status.php', {
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
                    button.classList.toggle('inactive-status', data.new_status !== "active");
                    button.textContent = data.new_status.charAt(0).toUpperCase() + data.new_status.slice(1);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function deleteDistributor(id) {
        if (confirm("Are you sure you want to delete this distributor?")) {
            fetch('db/delete/delete_distributor.php', {
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
                        loadData(); // Reload data after deletion
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }
</script>

<?php include 'footer.php'; ?>