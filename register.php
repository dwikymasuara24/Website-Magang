<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    
    // Validasi input
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($email)) {
        $error = 'Semua field harus diisi!';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username sudah digunakan! Silakan pilih username lain.';
        } else {
            // Cek apakah email sudah ada
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email sudah terdaftar! Silakan gunakan email lain.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user baru
                $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username, $hashed_password, $nama_lengkap, $email, $role);
                
                if ($stmt->execute()) {
                    $new_user_id = $conn->insert_id;
                    
                    // Log aktivitas
                    log_activity($new_user_id, 'register', 'User baru berhasil mendaftar');
                    
                    $success = 'Registrasi berhasil! Anda akan diarahkan ke halaman login...';
                    
                    // Redirect ke login setelah 2 detik
                    header("refresh:2;url=index.php");
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Survey Pegadaian</title>
    <link rel="stylesheet" href="aseets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-toggle {
            position: relative;
        }
        .password-toggle input {
            padding-right: 45px;
        }
        .password-toggle .toggle-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
        }
        .password-toggle .toggle-icon:hover {
            color: #28a745;
        }
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        .password-strength .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        .password-strength .strength-bar-fill {
            height: 100%;
            transition: all 0.3s;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box" style="max-width: 500px;">
            <div class="login-header">
                <img src="p.png" alt="Logo Pegadaian" class="login-logo">
                <h1>PEGADAIAN MIKRO</h1>
                <p>Registrasi Akun Baru</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="nama_lengkap">
                        <i class="fas fa-user"></i> Nama Lengkap *
                    </label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                           placeholder="Masukkan nama lengkap" required autofocus
                           value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user-circle"></i> Username *
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Minimal 4 karakter" required minlength="4"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <small style="color: #666; font-size: 12px;">Username akan digunakan untuk login</small>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="contoh@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i> Role / Jabatan *
                    </label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="surveyor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'surveyor') ? 'selected' : ''; ?>>
                            Surveyor
                        </option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>
                            Admin
                        </option>
                    </select>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimal 6 karakter" required minlength="6">
                    <i class="fas fa-eye toggle-icon" onclick="togglePassword('password', this)"></i>
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Konfirmasi Password *
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Ulangi password" required minlength="6">
                    <i class="fas fa-eye toggle-icon" onclick="togglePassword('confirm_password', this)"></i>
                    <span id="passwordMatch" style="font-size: 12px; display: none;"></span>
                </div>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
                
                <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #eee;">
                    <p style="color: #666;">
                        Sudah punya akun? 
                        <a href="index.php" style="color: #28a745; font-weight: 600; text-decoration: none;">
                            Login di sini
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Check password strength
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            strengthBar.className = 'strength-bar-fill';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Lemah';
                strengthText.style.color = '#dc3545';
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Sedang';
                strengthText.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Kuat';
                strengthText.style.color = '#28a745';
            }
        });
        
        // Check password match
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        
        function checkPasswordMatch() {
            if (confirmPassword.value.length === 0) {
                passwordMatch.style.display = 'none';
                return;
            }
            
            passwordMatch.style.display = 'block';
            
            if (passwordInput.value === confirmPassword.value) {
                passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i> Password cocok';
                passwordMatch.style.color = '#28a745';
            } else {
                passwordMatch.innerHTML = '<i class="fas fa-times-circle"></i> Password tidak cocok';
                passwordMatch.style.color = '#dc3545';
            }
        }
        
        confirmPassword.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                confirmPassword.focus();
                return false;
            }
            
            if (passwordInput.value.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                passwordInput.focus();
                return false;
            }
        });
        
        // Username validation (alphanumeric only)
        document.getElementById('username').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
        });
    </script>
</body>
</html>