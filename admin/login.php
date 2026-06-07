<?php
/**
 * login.php
 * Khadomeh Admin Panel Login
 * 
 * Provides a secure form for admin authentication,
 * with CSRF protection and input validation.
 */

// Include system bootstrapper
require_once dirname(__DIR__) . '/includes/init.php';

use App\Core\Response;
use App\Core\Validator;
use App\Repositories\AdminUserRepository;

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    Response::redirect(admin_url('dashboard.php'));
}

$errors = [];
$email = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    $submittedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf_token($submittedToken)) {
        $errors['csrf'] = 'خطأ في التحقق من أمان الجلسة (CSRF). يرجى المحاولة مجدداً.';
    } else {
        // 2. Extract and Validate Input
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        $validator = new Validator();
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min_length' => 6]
        ];
        
        if ($validator->validate(['email' => $email, 'password' => $password], $rules)) {
            // 3. Search Admin in Database
            $adminRepo = new AdminUserRepository();
            $admin = $adminRepo->findByEmail($email);
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // 4. Set Session variables on success
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Update last login timestamp in DB
                $adminRepo->updateLastLogin($admin['id']);
                
                // 5. Redirect back or to dashboard
                $redirectUrl = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : admin_url('dashboard.php');
                unset($_SESSION['redirect_url']);
                
                Response::redirect($redirectUrl);
            } else {
                $errors['auth'] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
            }
        } else {
            $errors = array_merge($errors, $validator->getErrors());
        }
    }
}

// Page variables for layout
$pageTitle = 'تسجيل دخول الإشراف';
$metaDesc = 'صفحة تسجيل الدخول المخصصة لمدراء منصة خدومة للتحكم بالدليل وإدارة الحرفيين.';

require_once APP_DIR . '/includes/header.php';
?>

<div class="container">
    <div class="login-wrapper">
        <div class="login-logo-container">
            <span style="font-size: 3rem;">🔐</span>
            <h1 class="login-title">لوحة إشراف خدومة</h1>
            <p style="color: var(--text-secondary); margin-top: 5px;">سجل دخولك لإدارة دليل الحرفيين والتقييمات</p>
        </div>

        <div class="card login-card">
            <!-- Alert Notifications -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <ul style="padding-right: 15px; list-style-type: disc;">
                        <?php foreach ($errors as $field => $errList): ?>
                            <?php if (is_array($errList)): ?>
                                <?php foreach ($errList as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><?= e($errList) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= admin_url('login.php') ?>" method="POST" novalidate>
                <!-- CSRF Token -->
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="admin@khadomeh.local" required autocomplete="email">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">تسجيل الدخول</button>
            </form>
        </div>
    </div>
</div>

<?php
require_once APP_DIR . '/includes/footer.php';
?>
