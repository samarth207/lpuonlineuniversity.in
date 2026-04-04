<?php
define('ADMIN_PAGE', true);
require_once __DIR__ . '/../config.php';
requireLogin();

$pageTitle = 'Change Password';
$currentPage = '';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $currentPass  = $_POST['current_password'] ?? '';
        $newPass      = $_POST['new_password'] ?? '';
        $confirmPass  = $_POST['confirm_password'] ?? '';

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
            $error = 'All fields are required.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New password and confirmation do not match.';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
            $error = 'Password must contain at least one uppercase letter and one number.';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM blog_admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($currentPass, $admin['password_hash'])) {
                $error = 'Current password is incorrect.';
            } else {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE blog_admins SET password_hash = ? WHERE id = ?")
                    ->execute([$newHash, $_SESSION['admin_id']]);
                $success = 'Password changed successfully!';
            }
        }
    }
}

$csrf = generateCSRFToken();
include 'layout-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4"><i class="las la-lock"></i> Change Admin Password</h5>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= e($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">Min 8 chars, at least 1 uppercase letter and 1 number.</small>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layout-footer.php'; ?>
