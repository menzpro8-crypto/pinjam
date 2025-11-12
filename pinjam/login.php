<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// Include configuration
require_once 'config.php';

// Handle login form submission
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $loginError = 'Username dan password harus diisi';
    } else {
        try {
            // Check admin credentials
            $stmt = $pdo->prepare("SELECT id, username, password, nama, email, status FROM admin WHERE username = ? AND status = 'Aktif'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Update last login
                $stmt = $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);

                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];
                $_SESSION['admin_email'] = $admin['email'];

                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                $loginError = 'Username atau password salah';
            }
        } catch (PDOException $e) {
            $loginError = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Peminjaman Alat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .login-body {
            padding: 30px 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            font-size: 14px;
        }

        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .login-footer p {
            color: #6b7280;
            font-size: 14px;
        }

        .school-info {
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }

            .login-header {
                padding: 20px 15px;
            }

            .login-body {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-tools"></i> Admin Login</h1>
            <p>Sistem Peminjaman Alat SMK Al-Basthomi</p>
        </div>

        <div class="login-body">
            <?php if ($loginError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               placeholder="Masukkan username" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Masukkan password" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </button>
            </form>
        </div>
            <div class="school-info">
                SMK Al-Basthomi © <?php echo date('Y'); ?>
            </div>
        </div>
    </div>

    <script>
        // Focus on username field when page loads
        document.getElementById('username').focus();

        // Add enter key support for form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input');

            inputs.forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const nextInput = this.nextElementSibling;
                        if (nextInput && nextInput.tagName === 'INPUT') {
                            nextInput.focus();
                        } else {
                            form.submit();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
