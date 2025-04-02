<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$main_admin_id = $_SESSION['user_id'];
$query_super_admin = "SELECT super_admin_id FROM main_admin WHERE id = '$main_admin_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_email = isset($_GET['search_email']) ? trim($_GET['search_email']) : '';
$search_mobile = isset($_GET['search_mobile']) ? trim($_GET['search_mobile']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$wallet_min = isset($_GET['wallet_min']) ? (int)$_GET['wallet_min'] : '';
$wallet_max = isset($_GET['wallet_max']) ? (int)$_GET['wallet_max'] : '';
$unique_id = isset($_GET['unique_id']) ? trim($_GET['unique_id']) : '';
$state_id = isset($_GET['state_id']) ? (int)$_GET['state_id'] : '';
$created_from = isset($_GET['created_from']) ? trim($_GET['created_from']) : '';
$created_to = isset($_GET['created_to']) ? trim($_GET['created_to']) : '';

$offset = ($page - 1) * $limit;

$query = "SELECT * FROM super_distributor WHERE main_admin_id = $main_admin_id";

// Applying Filters
if (!empty($search_name)) {
    $query .= " AND name LIKE '%$search_name%'";
}
if (!empty($search_email)) {
    $query .= " AND email LIKE '%$search_email%'";
}
if (!empty($search_mobile)) {
    $query .= " AND mobile_number LIKE '%$search_mobile%'";
}
if ($status !== '') {
    $query .= " AND status = '$status'";
}
if ($wallet_min !== '' && $wallet_max !== '') {
    $query .= " AND wallet BETWEEN $wallet_min AND $wallet_max";
}
if (!empty($unique_id)) {
    $query .= " AND unique_super_distributor_id LIKE '%$unique_id%'";
}
if (!empty($created_from) && !empty($created_to)) {
    $query .= " AND created_at BETWEEN '$created_from 00:00:00' AND '$created_to 23:59:59'";
} elseif (!empty($created_from)) {
    $query .= " AND created_at >= '$created_from 00:00:00'";
} elseif (!empty($created_to)) {
    $query .= " AND created_at <= '$created_to 23:59:59'";
}


$total_records = mysqli_num_rows(mysqli_query($conn, $query));
$total_pages = ceil($total_records / $limit);

$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" id="filterForm" class="mb-3">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6">
                                        <label for="unique_id" class="form-label">Unique ID</label>
                                        <input type="text" id="unique_id" name="unique_id" class="form-control" placeholder="Enter Unique ID" value="<?= $unique_id ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label for="search_name" class="form-label">Name</label>
                                        <input type="text" id="search_name" name="search_name" class="form-control" placeholder="Enter name" value="<?= $search_name ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label for="search_email" class="form-label">Email</label>
                                        <input type="email" id="search_email" name="search_email" class="form-control" placeholder="Enter email" value="<?= $search_email ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label for="search_mobile" class="form-label">Mobile</label>
                                        <input type="text" id="search_mobile" name="search_mobile" class="form-control" placeholder="Enter mobile" value="<?= $search_mobile ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label for="created_from" class="form-label">Created From</label>
                                        <input type="date" id="created_from" name="created_from" class="form-control" value="<?= $created_from ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label for="created_to" class="form-label">Created To</label>
                                        <input type="date" id="created_to" name="created_to" class="form-control" value="<?= $created_to ?>">
                                    </div>
                                    <div class="col-lg-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-3">Apply Filters</button>
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
                            <h4 class="card-title mb-0">Super Distributor List</h4>
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
                            <!-- Filters -->


                            <!-- Table -->
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Unique ID</th>
                                            <th>Name</th>
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
                                            $super_distributor_id = $row['id'];
                                            $statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
                                            $statusText = ucfirst($row['status']);

                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_super_distributor_id']}</td>
                                                    <td>{$row['name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['mobile_number']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['password']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $super_distributor_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td>
                                                        <button class='status-btn $statusClass' onclick='toggleStatus({$row['id']})' id='status-{$row['id']}'>
                                                            $statusText
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <a href='update_super_distributor.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
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
                                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&limit=<?= $limit ?>&search_name=<?= $search_name ?>&search_email=<?= $search_email ?>&search_mobile=<?= $search_mobile ?>&status=<?= $status ?>&wallet_min=<?= $wallet_min ?>&wallet_max=<?= $wallet_max ?>"><?= $i ?></a>
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
        let superAdminId = "<?php echo $super_admin_id; ?>"; // Get PHP variable

        keyCountElements.forEach((element) => {
            let mainAdminId = element.id.replace("key-count-", "");
            fetchKeyCount(mainAdminId, superAdminId); // Pass superAdminId here
        });
    });

    function fetchKeyCount(mainAdminId, superAdminId) {
        fetch(`db/get/fetch_key_count.php?role=super_distributor&role_id=${mainAdminId}&super_admin_id=${superAdminId}`)
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