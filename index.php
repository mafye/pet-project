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

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = get_post('username');
    $password = get_post('password');
    $csrf_token = get_post('csrf_token');
    
    // 验证CSRF令牌
    if (!verify_csrf_token($csrf_token)) {
        $error = '安全验证失败，请重试';
    } elseif (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        $user = new User($pdo);
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // 设置会话变量
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            
            // 重定向到仪表板
            redirect('dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = '登录';
include 'templates/header.php';
?>

<div class="form-container">
    <h2 class="form-title">登录</h2>
    
    <?php if (!empty($error)): ?>
        <?php echo display_error($error); ?>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <?php echo display_success($success); ?>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="form-group">
            <label for="username">用户名</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">密码</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <button type="submit">登录</button>
        </div>
        
        <p class="form-footer">
            还没有账号？<a href="register.php">注册</a>
        </p>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
