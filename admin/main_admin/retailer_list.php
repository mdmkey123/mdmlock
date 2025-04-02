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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$super_distributor = isset($_GET['super_distributor']) ? (int)$_GET['super_distributor'] : '';
$distributor = isset($_GET['distributor']) ? (int)$_GET['distributor'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$offset = ($page - 1) * $limit;

$query = "SELECT a.*, 
                 sd.name AS super_distributor_name,
                 d.full_name AS distributor_name
          FROM admin a
          LEFT JOIN super_distributor sd ON a.super_distributor_id = sd.id
          LEFT JOIN distributors d ON a.distributor_id = d.id
          WHERE a.main_admin_id = $main_admin_id";

if (!empty($search)) {
    $query .= " AND (a.unique_admin_id LIKE '%$search%' 
                     OR a.first_name LIKE '%$search%' 
                     OR a.last_name LIKE '%$search%' 
                     OR a.email LIKE '%$search%' 
                     OR a.phone LIKE '%$search%' 
                     OR d.full_name LIKE '%$search%')";
}

if (!empty($super_distributor)) {
    $query .= " AND a.super_distributor_id = $super_distributor";
}

if (!empty($distributor)) {
    $query .= " AND a.distributor_id = $distributor";
}

if ($status !== '') {
    $query .= " AND a.status = " . ($status === 'active' ? 1 : 0);
}

if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND DATE(a.created_at) BETWEEN '$from_date' AND '$to_date'";
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
                <div class="card">
                    <div class="card-body">
                        <form method="GET">
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Retailer Name</label>
                                    <input type="text" name="search" class="form-control" placeholder="Enter Name" value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control" placeholder="Enter Mobile" value="<?= htmlspecialchars($_GET['mobile'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email</label>
                                    <input type="text" name="email" class="form-control" placeholder="Enter Email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Super Distributor</label>
                                    <select name="super_distributor" class="form-select">
                                        <option value="">All Super Distributors</option>
                                        <?php
                                        $sd_result = mysqli_query($conn, "SELECT * FROM super_distributor");
                                        while ($sd = mysqli_fetch_assoc($sd_result)) { ?>
                                            <option value="<?= $sd['id'] ?>" <?= ($super_distributor == $sd['id']) ? 'selected' : '' ?>><?= $sd['name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Distributor</label>
                                    <select name="distributor" class="form-select">
                                        <option value="">All Distributors</option>
                                        <?php
                                        $d_result = mysqli_query($conn, "SELECT * FROM distributors");
                                        while ($d = mysqli_fetch_assoc($d_result)) { ?>
                                            <option value="<?= $d['id'] ?>" <?= ($distributor == $d['id']) ? 'selected' : '' ?>><?= $d['full_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All</option>
                                        <option value="active" <?= ($status == 'active') ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($status == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="retailer_list.php" class="btn btn-secondary ms-2">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
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

                        <div class="table-container">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Unique ID</th>
                                        <th>Super Distributor</th>
                                        <th>Distributor</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Password</th>
                                        <th>Keys</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sr_no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $retailer_id = $row['id'];
                                        echo "<tr>
                                                <td>{$sr_no}</td>
                                                <td>{$row['unique_admin_id']}</td>
                                                <td>{$row['super_distributor_name']}</td>
                                                <td>{$row['distributor_name']}</td>
                                                <td>{$row['first_name']} {$row['last_name']}</td>
                                                <td>{$row['email']}</td>
                                                <td>{$row['phone']}</td>
                                                <td>{$row['password']}</td>";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $retailer_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                <td>" . ($row['status'] == 1 ? 'Active' : 'Inactive') . "</td>
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

                        <div class="pagination-container">
                            <nav>
                                <ul class="pagination justify-content-center">
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
    
    function loadData(page) {
        let search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
        let limit = document.getElementById('entriesPerPage').value;
        window.location.href = `retailer_list.php?page=${page}&limit=${limit}&search=${search}`;
    }

    function openChangePasswordModal(id, password) {
        document.getElementById('retailerId').value = id;
        document.getElementById('oldPassword').value = password; // Show old password (read-only)
        document.getElementById('newPassword').value = ''; // Clear new password field
        document.getElementById('confirmPassword').value = ''; // Clear confirm password field
        $('#changePasswordModal').modal('show');
    }


    function resetSearch() {
        if (document.getElementById('searchInput')) {
            document.getElementById('searchInput').value = '';
        }
        loadData(1);
    }

    function viewRetailer(id) {
        window.location.href = 'view_retailer.php?id=' + id;
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
                    button.classList.toggle('active-status', data.new_status === 1);
                    button.classList.toggle('inactive-status', data.new_status === 0);
                    button.textContent = data.new_status === 1 ? "Active" : "Inactive";
                } else {
                    alert("Failed to update status!");
                }
            });
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
                });
        }
    }
</script>

<?php include 'footer.php'; ?>