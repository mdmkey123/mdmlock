<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$main_admin_id = $_SESSION['user_id'];
$query_super_admin = "SELECT super_admin_id FROM main_admin WHERE id = '$main_admin_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$name = isset($_POST['name']) ? $_POST['name'] : '';
$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$super_distributor = isset($_POST['super_distributor']) ? $_POST['super_distributor'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';

$offset = ($page - 1) * $limit;

$total_query = "SELECT COUNT(*) AS total FROM distributors WHERE main_admin_id = $main_admin_id";

if (!empty($name)) {
    $total_query .= " AND full_name LIKE '%$name%'";
}
if (!empty($mobile)) {
    $total_query .= " AND mobile LIKE '%$mobile%'";
}
if (!empty($email)) {
    $total_query .= " AND email LIKE '%$email%'";
}
if (!empty($super_distributor)) {
    $total_query .= " AND super_distributor_id = '$super_distributor'";
}
if (!empty($status)) {
    $total_query .= " AND status = '$status'";
}
if (!empty($from_date) && !empty($to_date)) {
    $total_query .= " AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'";
}

$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT d.*, 
                 sd.name AS super_distributor_name
          FROM distributors d
          LEFT JOIN super_distributor sd ON d.super_distributor_id = sd.id
          WHERE d.main_admin_id = $main_admin_id";

if (!empty($name)) {
    $query .= " AND d.full_name LIKE '%$name%'";
}
if (!empty($mobile)) {
    $query .= " AND d.mobile LIKE '%$mobile%'";
}
if (!empty($email)) {
    $query .= " AND d.email LIKE '%$email%'";
}
if (!empty($super_distributor)) {
    $query .= " AND d.super_distributor_id = '$super_distributor'";
}
if (!empty($status)) {
    $query .= " AND d.status = '$status'";
}
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND DATE(d.created_at) BETWEEN '$from_date' AND '$to_date'";
}

$query .= " LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$super_distributors = mysqli_query($conn, "SELECT id, name FROM super_distributor WHERE main_admin_id = $main_admin_id");
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">

                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter Name" value="<?= htmlspecialchars($name) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="mobile" class="form-label">Mobile Number</label>
                                        <input type="text" id="mobile" name="mobile" class="form-control" placeholder="Enter Mobile Number" value="<?= htmlspecialchars($mobile) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" id="email" name="email" class="form-control" placeholder="Enter Email Address" value="<?= htmlspecialchars($email) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="super_distributor" class="form-label">Super Distributor</label>
                                        <select id="super_distributor" name="super_distributor" class="form-select">
                                            <option value="">All Super Distributors</option>
                                            <?php while ($sd = mysqli_fetch_assoc($super_distributors)) { ?>
                                                <option value="<?= $sd['id'] ?>" <?= ($super_distributor == $sd['id']) ? 'selected' : '' ?>><?= $sd['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" id="from_date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" id="to_date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">Apply</button>
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
                            <h4 class="card-title mb-0">Distributor List</h4>

                        </div>
                        <div class="card-body">


                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Unique ID</th>
                                            <th>Super Distributor</th>
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
                                            $distributor_id = $row['id'];
                                            $statusClass = ($row['status'] == 'active') ? 'active-status' : 'inactive-status';
                                            $statusText = ucfirst($row['status']);

                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_distributor_id']}</td>
                                                    <td>{$row['super_distributor_name']}</td>
                                                    <td>{$row['full_name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['mobile']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['password_hash']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $distributor_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td>
                                                        <button class='status-btn $statusClass'>$statusText</button>
                                                    </td>
                                                    <td>
                                                        <a href='update_distributor.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a>
                                                        <button class='icon-btn' onclick='viewDistributor({$row['id']})' title='View'>
                                                            <i class='ri-eye-line'></i>
                                                        </button>
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

                            <div class="pagination-container">
                                <nav>
                                    <ul class="pagination justify-content-center">
                                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="page" value="<?= $i ?>">
                                                    <button type="submit" class="page-link"><?= $i ?></button>
                                                </form>
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
        let superAdminId = "<?php echo $super_admin_id; ?>"; // Get PHP variable

        keyCountElements.forEach((element) => {
            let mainAdminId = element.id.replace("key-count-", "");
            fetchKeyCount(mainAdminId, superAdminId); // Pass superAdminId here
        });
    });

    function fetchKeyCount(mainAdminId, superAdminId) {
        fetch(`db/get/fetch_key_count.php?role=distributor&role_id=${mainAdminId}&super_admin_id=${superAdminId}`)
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
        window.location.href = 'distributor_list.php';
    }

    function loadData(page) {
        let limit = document.getElementById("entriesPerPage").value;
        let search = document.getElementById("searchInput").value;
        window.location.href = `distributor_list.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}`;
    }

    function resetSearch() {
        window.location.href = 'distributor_list.php';
    }

    function viewDistributor(id) {
        window.location.href = 'view_distributor.php?id=' + id;
    }

    function openChangePasswordModal(id, oldPassword) {
        document.getElementById('distributorId').value = id;
        document.getElementById('oldPassword').value = oldPassword; // Show actual old password
        document.getElementById('newPassword').value = ''; // Clear new password field
        document.getElementById('confirmPassword').value = ''; // Clear confirm password field
        $('#changePasswordModal').modal('show');
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
            }).then(response => response.json())
            .then(data => {
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
            });
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
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    }
</script>

<?php include 'footer.php'; ?>