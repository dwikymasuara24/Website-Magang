<?php
$page_title = 'Manajemen User';
include 'includes/header.php';

// Cek apakah user adalah admin
if ($_SESSION['role'] != 'admin') {
    echo '<div class="alert alert-danger">Anda tidak memiliki akses ke halaman ini!</div>';
    include 'includes/footer.php';
    exit();
}

$success = '';
$error = '';

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Tidak bisa hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $error = 'Anda tidak dapat menghapus akun Anda sendiri!';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            log_activity($_SESSION['user_id'], 'delete_user', "Menghapus user ID: $id");
            $success = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user!';
        }
        $stmt->close();
    }
}

// Handle Update Status/Role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $role = clean_input($_POST['role']);
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    if ($stmt->execute()) {
        log_activity($_SESSION['user_id'], 'update_user', "Mengupdate role user ID: $user_id");
        $success = 'Role user berhasil diupdate!';
    } else {
        $error = 'Gagal mengupdate role user!';
    }
    $stmt->close();
}

// Handle Reset Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $user_id = intval($_POST['user_id']);
    $new_password = 'password'; // Default password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        log_activity($_SESSION['user_id'], 'reset_password', "Reset password user ID: $user_id");
        $success = 'Password berhasil direset menjadi: <strong>password</strong>';
    } else {
        $error = 'Gagal reset password!';
    }
    $stmt->close();
}

// Get all users
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? clean_input($_GET['role']) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(username LIKE '%$search%' OR nama_lengkap LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($filter_role)) {
    $where_clauses[] = "role = '$filter_role'";
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$query = "SELECT * FROM users $where_sql ORDER BY created_at DESC";
$result = $conn->query($query);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_admin = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin'")->fetch_assoc()['total'];
$total_surveyor = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='surveyor'")->fetch_assoc()['total'];
?>

<div class="page-header">
    <h1>Manajemen User</h1>
    <p class="breadcrumb">Home / Manajemen User</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $total_users; ?></h3>
            <p>Total User</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $total_admin; ?></h3>
            <p>Admin</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $total_surveyor; ?></h3>
            <p>Surveyor</p>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="users.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: end;">
            <div class="form-group" style="flex: 1; min-width: 250px; margin-bottom: 0;">
                <label>Cari User</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari username, nama, email..." 
                       value="<?php echo $search; ?>">
            </div>
            
            <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="">Semua Role</option>
                    <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="surveyor" <?php echo $filter_role == 'surveyor' ? 'selected' : ''; ?>>Surveyor</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-search"></i> Cari
            </button>
            
            <a href="users.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- User List -->
<div class="card">
    <div class="card-header">
        <h3>Daftar User</h3>
        <a href="register.php" class="btn btn-success btn-sm">
            <i class="fas fa-user-plus"></i> Tambah User Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terdaftar Sejak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php 
                        $no = 1;
                        while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge <?php echo $row['role'] == 'admin' ? 'badge-success' : 'badge-info'; ?>">
                                        <?php echo ucfirst($row['role']); ?> (Anda)
                                    </span>
                                <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <select name="role" class="badge <?php echo $row['role'] == 'admin' ? 'badge-success' : 'badge-info'; ?>" 
                                                style="border: none; cursor: pointer; padding: 6px 10px;" 
                                                onchange="this.form.submit()">
                                            <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="surveyor" <?php echo $row['role'] == 'surveyor' ? 'selected' : ''; ?>>Surveyor</option>
                                        </select>
                                        <input type="hidden" name="update_user" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="resetPassword(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')" 
                                                class="btn btn-warning btn-sm" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')" 
                                                class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Akun Anda</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999;">
                                Tidak ada data user
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="resetModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reset Password</h3>
                <button type="button" class="close" onclick="hideModal('resetModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    <p>Apakah Anda yakin ingin mereset password untuk user <strong id="reset_username"></strong>?</p>
                    <p style="color: #dc3545;">Password akan direset menjadi: <strong>password</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('resetModal')">Batal</button>
                    <button type="submit" name="reset_password" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetPassword(userId, username) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('reset_username').textContent = username;
    showModal('resetModal');
}

function confirmDelete(id, username) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?\n\nSemua data survey yang dibuat oleh user ini akan tetap tersimpan.')) {
        window.location.href = 'users.php?action=delete&id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>