<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/User.php';

// 如果用户已登录，重定向到仪表板
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = get_post('username');
    $email = get_post('email');
    $password = get_post('password');
    $confirm_password = get_post('confirm_password');
    $csrf_token = get_post('csrf_token');
    
    // 验证CSRF令牌
    if (!verify_csrf_token($csrf_token)) {
        $error = '安全验证失败，请重试';
    } elseif (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = '所有字段都是必填的';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不匹配';
    } elseif (strlen($password) < 6) {
        $error = '密码必须至少包含6个字符';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '请输入有效的电子邮件地址';
    } else {
        $user = new User($pdo);
        $result = $user->register($username, $email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            // 可以选择自动登录用户
            // $_SESSION['user_id'] = $result['user_id'];
            // $_SESSION['username'] = $username;
            // redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = '注册';
include 'templates/header.php';
?>

<div class="form-container">
    <h2 class="form-title">创建账户</h2>
    
    <?php if (!empty($error)): ?>
        <?php echo display_error($error); ?>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <?php echo display_success($success); ?>
        <p class="text-center">
            <a href="index.php" class="button">前往登录</a>
        </p>
    <?php else: ?>
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">电子邮件</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">确认密码</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit">注册</button>
            </div>
            
            <p class="form-footer">
                已有账号？<a href="index.php">登录</a>
            </p>
        </form>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
