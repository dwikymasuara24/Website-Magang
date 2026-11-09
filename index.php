<?php
// CRITICAL FIX: Mulai output buffering untuk mencegah masalah header
ob_start();
// CRITICAL FIX: Mulai session
session_start();
// Ganti 'config.php' dengan path yang benar
require_once 'config/database.php';

// Jika user sudah login, lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    // Memastikan fungsi redirect_by_role() sudah ditambahkan ke config/database.php
    header("Location: " . redirect_by_role()); 
    exit();
}

// Mengambil koneksi database yang sudah didefinisikan di config/database.php
global $conn; 

$message = '';
$error = '';
$success = '';

// --- FUNGSI PHP DARI REGISTER.PHP LAMA ---

// Proses registrasi (Disesuaikan agar menggunakan output alert JS $message, dan menggunakan nama field dari form registrasi lama)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register_classic') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = clean_input($_POST['nama_lengkap']);
    $email = clean_input($_POST['email']);
    $role = clean_input($_POST['role']);
    
    // Validasi input
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($email) || empty($role)) {
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
        $stmt_check_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check_user->bind_param("s", $username);
        $stmt_check_user->execute();
        $result_user = $stmt_check_user->get_result();
        
        if ($result_user->num_rows > 0) {
            $error = 'Username sudah digunakan! Silakan pilih username lain.';
        } else {
            // Cek apakah email sudah ada
            $stmt_check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $result_email = $stmt_check_email->get_result();
            
            if ($result_email->num_rows > 0) {
                $error = 'Email sudah terdaftar! Silakan gunakan email lain.';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user baru
                $stmt_insert = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, role) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("sssss", $username, $hashed_password, $nama_lengkap, $email, $role);
                
                if ($stmt_insert->execute()) {
                    $new_user_id = $conn->insert_id;
                    
                    // Log aktivitas
                    log_activity($new_user_id, 'register', 'User baru berhasil mendaftar');
                    
                    // Menggunakan alert dan redirect hash untuk kembali ke halaman login
                    $message = "<script>alert('Registrasi berhasil! Silakan masuk.'); window.location.hash = '';</script>";
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar: ' . $conn->error;
                }
                $stmt_insert->close();
            }
            $stmt_check_email->close();
        }
        $stmt_check_user->close();
    }
    
    // Jika ada error, tampilkan alert JS
    if ($error) {
         $message = "<script>alert('Gagal Registrasi: " . $error . "'); window.location.hash = 'register';</script>";
    }
}

// --- FUNGSI LOGIN ---

// A. Handle Login Biasa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    // Perubahan: Menggunakan prepared statement untuk keamanan yang lebih baik
    $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role']; 

            // Log aktivitas login berhasil
            log_activity($user['id'], 'login', 'User berhasil login');
            
            // Pengalihan sekarang akan bekerja karena ob_start() dan session_start() sudah dipanggil
            header("Location: " . redirect_by_role());
            exit();
        } else {
            $message = "<script>alert('Password salah!');</script>";
        }
    } else {
        $message = "<script>alert('Username tidak ditemukan!');</script>";
    }
    $stmt->close();
}

// C. Handle Google Login (Dihapus/Dinonaktifkan agar tidak mengganggu fokus)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Sistem Survey Pegadaian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #a5d6a7 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
            padding: 20px;
        }

        :root {
            --pegadaian-green: #28a745;
            --pegadaian-dark: #1e7e34;
            --pegadaian-gold: #FDB913;
        }

        h1 {
            font-weight: bold;
            margin: 0;
            color: var(--pegadaian-green);
        }

        h2 {
            text-align: center;
            color: #FFFFFF;
        }

        p {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
        }

        span {
            font-size: 12px;
        }

        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
        }

        button {
            border-radius: 20px;
            border: 1px solid var(--pegadaian-green);
            background-color: var(--pegadaian-green);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
            margin-top: 10px;
        }

        button:active {
            transform: scale(0.95);
        }

        button:focus {
            outline: none;
        }

        button.ghost {
            background-color: transparent;
            border-color: #FFFFFF;
        }

        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        input, select {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
        }

        select {
            cursor: pointer;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 720px; /* PERBAIKAN TINGGI: Ditingkatkan agar form register muat */
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
            display: flex; 
            align-items: center; 
            justify-content: center;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }
        
        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: show 0.6s;
            /* Perbaikan: Mengatur form container agar konten tidak terpotong di bagian atas */
            align-items: flex-start;
            padding-top: 20px;
            overflow-y: auto; /* Memastikan scrollable jika di layar kecil */
        }
        
        /* Memastikan form di container register rata atas */
        .sign-up-container form {
             justify-content: flex-start;
             padding: 10px 50px 20px; /* Padding bawah ditambahkan untuk tombol dan link */
             height: auto; 
        }

        @keyframes show {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: var(--pegadaian-green);
            background: linear-gradient(to right, var(--pegadaian-dark), var(--pegadaian-green));
            background-repeat: no-repeat;
            background-size: cover;
            background-position: 0 0;
            color: #FFFFFF;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        .logo-img {
            width: 100px; 
            margin-bottom: 15px; 
        }

        .google-btn-wrapper {
            margin-top: 15px;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        /* --- STYLE PERBAIKAN FORM REGISTER --- */
        .form-group {
            width: 100%;
            margin-bottom: 15px; /* Ditingkatkan untuk spacing yang lebih baik (seperti registrasi akun baru) */
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .password-toggle {
            position: relative;
        }
        .password-toggle input {
            padding-right: 45px;
        }
        .password-toggle .toggle-icon {
            position: absolute;
            right: 15px;
            top: calc(50% + 10px); /* Menyesuaikan posisi ke tengah input */
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            font-size: 18px;
            z-index: 10;
        }
        /* Penyesuaian Style Password Strength Bar */
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .password-strength .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 3px;
        }
        .password-strength .strength-bar-fill {
            height: 100%;
            transition: all 0.3s;
        }
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }

        /* Style untuk link "Sudah punya akun?" di bawah tombol Daftar (sesuai permintaan) */
        .register-footer-link {
            margin-top: 15px;
            padding-top: 15px; /* Menambah jarak dari tombol Daftar */
            border-top: 1px solid #eee; /* Garis pemisah */
            text-align: center;
            width: 100%;
        }
        /* --- END STYLE PERBAIKAN --- */


        @media screen and (max-width: 768px) {
            .container {
                min-height: 500px;
            }
            
            .form-container {
                width: 100% !important;
                /* Mengatur ulang untuk mobile */
                align-items: center; 
                padding-top: 0;
            }
            .sign-up-container form {
                 justify-content: center;
                 padding-top: 0;
            }
            
            .overlay-container {
                display: none;
            }
            
            .sign-in-container,
            .sign-up-container {
                width: 100% !important;
                position: relative !important;
            }
            
            .container.right-panel-active .sign-in-container {
                display: none;
            }
            
            .container.right-panel-active .sign-up-container {
                display: flex;
                opacity: 1;
            }
            
            .mobile-toggle {
                display: block !important;
                text-align: center;
                margin-top: 20px;
            }
        }
        
        .mobile-toggle {
            display: none;
        }
        
        .mobile-toggle button {
            background: transparent;
            border: 1px solid var(--pegadaian-green);
            color: var(--pegadaian-green);
            padding: 10px 30px;
        }
    </style>
</head>
<body>
    <?php echo $message; ?>

    <div class="container" id="container">
        
        <div class="form-container sign-up-container">
            <form action="" method="POST" id="registerForm">
                <h1 style="margin-bottom: 10px;">Registrasi Akun Baru</h1>
                
                <input type="hidden" name="action" value="register_classic">
                
                <div class="form-group">
                    <label for="nama_lengkap">
                        <i class="fas fa-user"></i> Nama Lengkap *
                    </label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" 
                           placeholder="Masukkan nama lengkap" required
                           value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user-circle"></i> Username *
                    </label>
                    <input type="text" id="username" name="username" 
                           placeholder="Minimal 4 karakter" required minlength="4"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input type="email" id="email" name="email" 
                           placeholder="contoh@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i> Role / Jabatan *
                    </label>
                    <select id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="Surveyor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Surveyor') ? 'selected' : ''; ?>>
                            Surveyor
                        </option>
                        <option value="Admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Admin') ? 'selected' : ''; ?>>
                            Admin
                        </option>
                    </select>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" id="password" name="password" 
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
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Ulangi password" required minlength="6">
                    <i class="fas fa-eye toggle-icon" onclick="togglePassword('confirm_password', this)"></i>
                    <span id="passwordMatch" style="font-size: 12px; display: none; margin-top: 5px; text-align: left; width: 100%; position: relative; left: 0;"></span>
                </div>
                
                <button type="submit" id="submitBtn" style="margin-top: 15px;">Daftar</button>

                <div class="register-footer-link">
                    <p>
                        Sudah punya akun? 
                        <a href="#login" id="footerSignInLink" style="color: var(--pegadaian-green); font-weight: 600; text-decoration: none;">
                            Masuk di sini
                        </a>
                    </p>
                </div>

            </form>
        </div>

        <div class="form-container sign-in-container">
            <form action="" method="POST">
                <img src="p.png" alt="Logo Pegadaian" class="logo-img">
                <h1>Login</h1>
                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Username" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit">Masuk</button>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h2>Akses Sistem Survey Mikro Pegadaian</h2>
                    <p>Silakan masuk untuk melanjutkan pengisian atau verifikasi data survey nasabah.</p>
                    <button class="ghost" id="signIn">Masuk</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h2>Bergabung dengan Tim Surveyor</h2>
                    <p>Daftarkan akun Anda untuk mulai melakukan survey dan verifikasi data nasabah Pegadaian.</p>
                    <button class="ghost" id="signUp">Daftar</button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mobile-toggle">
        <button id="mobileToggle">Belum punya akun? Daftar</button>
    </div>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const mobileToggle = document.getElementById('mobileToggle');
        const container = document.getElementById('container');
        const footerSignInLink = document.getElementById('footerSignInLink');

        // Fungsi untuk mengaktifkan panel sign-in/login
        function activateSignInPanel() {
            container.classList.remove("right-panel-active");
            window.location.hash = '';
            if (mobileToggle) {
                 mobileToggle.textContent = "Belum punya akun? Daftar";
            }
        }
        
        // Fungsi untuk mengaktifkan panel sign-up/register
        function activateSignUpPanel() {
            container.classList.add("right-panel-active");
            window.location.hash = 'register';
            if (mobileToggle) {
                 mobileToggle.textContent = "Sudah punya akun? Masuk";
            }
        }
        
        signUpButton.addEventListener('click', activateSignUpPanel);
        signInButton.addEventListener('click', activateSignInPanel);
        if (footerSignInLink) footerSignInLink.addEventListener('click', activateSignInPanel);

        
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                if (container.classList.contains("right-panel-active")) {
                    activateSignInPanel();
                } else {
                    activateSignUpPanel();
                }
            });
        }
        
        // --- FUNGSI JAVASCRIPT DARI REGISTER.PHP LAMA (DIPINDAHKAN) ---
        
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
        
        const passwordInput = document.getElementById('password');
        const strengthDiv = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                if (confirmPassword) checkPasswordMatch(); // Update match on input change
                
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
        }
        
        function checkPasswordMatch() {
            if (!confirmPassword || !passwordInput || !passwordMatch) return; 
            
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
        
        if (confirmPassword) confirmPassword.addEventListener('input', checkPasswordMatch);
        
        const usernameInput = document.getElementById('username');
        if (usernameInput) {
            usernameInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
            });
        }
        
        // Cek hash URL untuk mengaktifkan panel register saat dimuat
        if (window.location.hash === '#register') {
            activateSignUpPanel();
        } else {
            // Memastikan panel login aktif saat hash kosong
            activateSignInPanel();
        }
        
        // Callback untuk Google Sign-In (Dikosongkan)
        function handleCredentialResponse(response) {
            alert('Google Sign-In saat ini dinonaktifkan.');
        }
    </script>
</body>
</html>