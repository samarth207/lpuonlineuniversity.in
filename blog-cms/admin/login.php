<?php
require_once __DIR__ . '/../config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM blog_admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Blog Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Inter', sans-serif; }
        .login-card { max-width: 420px; margin: 100px auto; }
        .login-card .card { border: none; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .login-card .card-body { padding: 40px; }
        .login-card h2 { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 6px; }
        .login-card p { color: #777; font-size: 14px; }
        .btn-primary { background: #f58220; border-color: #f58220; font-weight: 600; }
        .btn-primary:hover { background: #e0741a; border-color: #e0741a; }
        .form-control:focus { border-color: #f58220; box-shadow: 0 0 0 .2rem rgba(245,130,32,.15); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card">
            <div class="card-body">
                <h2>Blog Admin</h2>
                <p>Sign in to manage your blog</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 mt-3" role="alert"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus
                               value="<?= e($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 mt-2">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
