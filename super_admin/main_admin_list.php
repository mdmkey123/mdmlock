<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_admin_id = $_SESSION['user_id'];

$name = isset($_GET['name']) ? mysqli_real_escape_string($conn, $_GET['name']) : '';
$email = isset($_GET['email']) ? mysqli_real_escape_string($conn, $_GET['email']) : '';
$phone = isset($_GET['phone']) ? mysqli_real_escape_string($conn, $_GET['phone']) : '';
$unique_id = isset($_GET['unique_id']) ? mysqli_real_escape_string($conn, $_GET['unique_id']) : '';
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($conn, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($conn, $_GET['end_date']) : '';

// Pagination setup
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Default to 10 per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to page 1
$offset = ($page - 1) * $limit; // Calculate the offset based on current page

$query = "SELECT ma.*
          FROM main_admin ma
          WHERE ma.super_admin_id = $super_admin_id";

if ($name) {
    $query .= " AND ma.name LIKE '%$name%'";
}
if ($email) {
    $query .= " AND ma.email LIKE '%$email%'";
}
if ($phone) {
    $query .= " AND ma.mobile_number LIKE '%$phone%'";
}
if ($unique_id) {
    $query .= " AND ma.unique_main_admin_id LIKE '%$unique_id%'";
}
// Add range filter for created date
if ($start_date && $end_date) {
    $query .= " AND DATE(ma.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $query .= " AND DATE(ma.created_at) >= '$start_date'";
} elseif ($end_date) {
    $query .= " AND DATE(ma.created_at) <= '$end_date'";
}

// Add LIMIT and OFFSET for pagination
$query .= " LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Calculate the total number of pages
$totalQuery = "SELECT COUNT(*) as total FROM main_admin ma WHERE ma.super_admin_id = $super_admin_id";
if ($name) {
    $totalQuery .= " AND ma.name LIKE '%$name%'";
}
if ($email) {
    $totalQuery .= " AND ma.email LIKE '%$email%'";
}
if ($phone) {
    $totalQuery .= " AND ma.mobile_number LIKE '%$phone%'";
}
if ($unique_id) {
    $totalQuery .= " AND ma.unique_main_admin_id LIKE '%$unique_id%'";
}

if ($start_date && $end_date) {
    $totalQuery .= " AND DATE(ma.created_at) BETWEEN '$start_date' AND '$end_date'";
} elseif ($start_date) {
    $totalQuery .= " AND DATE(ma.created_at) >= '$start_date'";
} elseif ($end_date) {
    $totalQuery .= " AND DATE(ma.created_at) <= '$end_date'";
}

$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalRecords = $totalRow['total'];
$totalPages = ceil($totalRecords / $limit);
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form id="filterForm" class="d-flex flex-wrap">
                                <div class="row g-3">
                                    <!-- Name Filter -->
                                    <div class=" col-md-3">
                                        <label for="filterName" class="form-label">Name</label>
                                        <input type="text" id="filterName" name="name" class="form-control" placeholder="Name" value="<?= isset($_GET['name']) ? $_GET['name'] : '' ?>">
                                    </div>
                                    <!-- Email Filter -->
                                    <div class=" col-md-3">
                                        <label for="filterEmail" class="form-label">Email</label>
                                        <input type="text" id="filterEmail" name="email" class="form-control" placeholder="Email" value="<?= isset($_GET['email']) ? $_GET['email'] : '' ?>">
                                    </div>
                                    <!-- Phone Filter -->
                                    <div class="col-md-3">
                                        <label for="filterPhone" class="form-label">Phone</label>
                                        <input type="text" id="filterPhone" name="phone" class="form-control" placeholder="Phone" value="<?= isset($_GET['phone']) ? $_GET['phone'] : '' ?>">
                                    </div>
                                    <!-- Unique ID Filter -->
                                    <div class="col-md-3">
                                        <label for="filterUniqueId" class="form-label">Unique ID</label>
                                        <input type="text" id="filterUniqueId" name="unique_id" class="form-control" placeholder="Unique ID" value="<?= isset($_GET['unique_id']) ? $_GET['unique_id'] : '' ?>">
                                    </div>
                                    <!-- Created Date Range Filter -->
                                    <div class="col-md-3">
                                        <label for="filterStartDate" class="form-label">Start Date</label>
                                        <input type="date" id="filterStartDate" name="start_date" class="form-control" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="filterEndDate" class="form-label">End Date</label>
                                        <input type="date" id="filterEndDate" name="end_date" class="form-control" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>">
                                    </div>

                                    <div class="d-flex align-items-end col-md-3 ">
                                        <button type="submit" class="btn btn-primary mr-2 me-3">Filter</button>
                                        <button type="reset" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
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
                            <h4 class="card-title mb-0">Main Admin List</h4>
                            <div class="d-flex">
                                <select id="entriesPerPage" class="form-select d-inline-block w-auto">
                                    <option value="10" <?= ($limit == 10) ? 'selected' : '' ?>>10</option>
                                    <option value="25" <?= ($limit == 25) ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= ($limit == 50) ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= ($limit == 100) ? 'selected' : '' ?>>100</option>
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
                                            $main_admin_id = $row['id'];
                                            $key_query = "SELECT COUNT(*) as key_count FROM enrollment_keys WHERE assigned_super_admin = '$super_admin_id' AND assigned_admin = '$main_admin_id'";
                                            $key_result = mysqli_query($master_conn, $key_query);
                                            $key_count = 0;
                                            if ($key_result && $key_row = mysqli_fetch_assoc($key_result)) {
                                                $key_count = $key_row['key_count'];
                                            }
                                            $statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
                                            $statusText = ucfirst($row['status']);
                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_main_admin_id']}</td>
                                                    <td>{$row['name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['mobile_number']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['password']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $main_admin_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "<td>
                                                        <button class='status-btn $statusClass' onclick='toggleStatus({$row['id']})' id='status-{$row['id']}'>
                                                            $statusText
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <a href='update_main_admin.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                        
                                                        <button class='icon-btn' onclick='openChangePasswordModal({$row['id']})' title='Change Password'>
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

                            <div class="row">
                                <div class="col-lg-12">
                                    <nav>
                                        <ul class="pagination pagination justify-content-center mb-0">

                                            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                                    <a class="page-link" href="?limit=<?= $limit ?>&page=<?= $i ?>"><?= $i ?></a>
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
                    <input type="hidden" id="mainAdminId" name="id">
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
        fetch(`db/get/fetch_key_count.php?role=main_admin&role_id=${mainAdminId}`)
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

    function openChangePasswordModal(id) {
        document.getElementById('mainAdminId').value = id;
        $('#changePasswordModal').modal('show');
    }

    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_main_admin_password.php', {
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
        fetch('db/update/update_main_admin_status.php', {
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
                    if (data.new_status === "active") {
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

    document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        fetch('db/update/update_main_admin_password.php', {
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

    function deleteMainAdmin(id) {
        if (confirm("Are you sure you want to delete this main admin?")) {
            fetch('db/delete/delete_main_admin.php', {
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