<?php
ob_start();
session_start();
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect straight to the admin dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /admin/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = trim($_POST['username'] ?? '');
    $password_input = trim($_POST['password'] ?? '');

    if (!empty($user_input) && !empty($password_input)) {
        // Query DB for user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([
            'username' => $user_input,
            'email'    => $user_input
        ]);
        $user = $stmt->fetch();

        // Direct plain-text password comparison
        if ($user && $password_input === $user['password']) {
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            ob_clean();
            header('Location: /admin/index.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Bhutan Destination</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            margin: 0 0 0.5rem 0;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .login-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #334155;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            text-align: center;
            border: 1px solid #fecaca;
        }

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
        }

        .login-footer a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .login-footer a:hover {
            color: #1e293b;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Bhutan Destination Management</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/admin/auth/login.php" method="POST">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="login-footer">
            <a href="/index.php">&larr; Return to Main Website</a>
        </div>
    </div>

</body>
</html>