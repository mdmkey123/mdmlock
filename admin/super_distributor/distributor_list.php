<?php
include 'header.php';
include 'topbar.php';
include 'sidebar.php';

$super_distributor_id = $_SESSION['user_id'];

$query_super_admin = "SELECT super_admin_id FROM super_distributor WHERE id = '$super_distributor_id'";
$result_super_admin = mysqli_query($conn, $query_super_admin);
$row_super_admin = mysqli_fetch_assoc($result_super_admin);
$super_admin_id = $row_super_admin['super_admin_id'] ?? null;

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$whereClauses = ["d.super_distributor_id = $super_distributor_id"];

if (!empty($_GET['name'])) {
    $name = mysqli_real_escape_string($conn, $_GET['name']);
    $whereClauses[] = "d.full_name LIKE '%$name%'";
}

if (!empty($_GET['email'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);
    $whereClauses[] = "d.email LIKE '%$email%'";
}

if (!empty($_GET['mobile'])) {
    $mobile = mysqli_real_escape_string($conn, $_GET['mobile']);
    $whereClauses[] = "d.mobile LIKE '%$mobile%'";
}


if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = (int)$_GET['status'];
    $whereClauses[] = "d.status = $status";
}

$whereSql = count($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$countQuery = "SELECT COUNT(*) AS total FROM distributors d $whereSql";
$countResult = mysqli_query($conn, $countQuery);
$totalDistributors = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalDistributors / $limit);

$query = "SELECT d.*, sd.name AS super_distributor_name
          FROM distributors d
          LEFT JOIN super_distributor sd ON d.super_distributor_id = sd.id
          $whereSql
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

$superDistributors = mysqli_query($conn, "SELECT id, name FROM super_distributor WHERE id = $super_distributor_id");
?>

<div class="content-page">
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label for="name" class="form-label">Distributor Name</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Distributor Name" value="<?= isset($_GET['name']) ? $_GET['name'] : '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="<?= isset($_GET['email']) ? $_GET['email'] : '' ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" id="mobile" name="mobile" class="form-control" placeholder="Mobile Number" value="<?= isset($_GET['mobile']) ? $_GET['mobile'] : '' ?>">
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
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

                        <!-- Filters -->

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
                                            $distributor_id = $row['id'];
                                            $statusText = $row['status'] ? 'Active' : 'Inactive';
                                            $statusClass = $row['status'] ? 'active-status' : 'inactive-status';

                                            echo "<tr>
                                                    <td>{$sr_no}</td>
                                                    <td>{$row['unique_distributor_id']}</td>
                                                    <td>{$row['full_name']}</td>
                                                    <td>{$row['email']}</td>
                                                    <td>{$row['mobile']}</td>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['password_hash']}</td>
                                                    ";
                                                    ?>
                                                    <td><i class='ri-key-fill'></i> <span id='key-count-<?= $distributor_id ?>'>Loading...</span></td>
                                                    <?php
                                                    echo "
                                                    <td><button class='status-btn $statusClass'>{$statusText}</button></td>
                                                    <td>
                                                        <a href='update_distributor.php?id={$row['id']}' class='icon-btn'><i class='ri-edit-box-fill'></i></a> 
                                                        <a href='view_distributor.php?id={$row['id']}' class='icon-btn'><i class='ri-eye-line'></i></a> 
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
                    <input type="hidden" id="distributorId" name="id">

                    <div class="mb-3">
                        <label for="oldPassword" class="form-label">Old Password</label>
                        <input type="text" class="form-control" id="oldPassword" name="old_password" value="********" readonly>
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
    
    function changeEntriesPerPage() {
        let limit = document.getElementById('entriesPerPage').value;
        let urlParams = new URLSearchParams(window.location.search);

        urlParams.set('limit', limit); // Set the new limit
        urlParams.set('page', 1); // Reset to page 1

        window.location.href = '?' + urlParams.toString(); // Reload page with new limit
    }

    function loadData(page = 1, limit = 10) {
        let params = new URLSearchParams({
            page: page,
            limit: limit
        });

        fetch('load_distributors.php?' + params.toString())
            .then(response => response.text())
            .then(data => {
                document.getElementById('distributorTableBody').innerHTML = data.table;
                document.getElementById('paginationLinks').innerHTML = data.pagination;
            })
            .catch(error => console.error('Error:', error));
    }

    function resetFilters() {
        let urlParams = new URLSearchParams(window.location.search);
        let limit = urlParams.get("limit") || 10;

        let newUrl = window.location.pathname + "?limit=" + limit;
        window.location.href = newUrl;
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