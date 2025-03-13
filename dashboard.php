<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/User.php';

// 检查用户是否已登录
if (!is_logged_in()) {
    redirect('index.php');
}

$user = new User($pdo);
$user_info = $user->getUserById($_SESSION['user_id']);

$page_title = '仪表板';
include 'templates/header.php';
?>

<div class="dashboard-container">
    <h2>欢迎回来，<?php echo htmlspecialchars($_SESSION['username']); ?>！</h2>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>积分</h3>
            <p class="stat-value"><?php echo $user_info['points']; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>注册日期</h3>
            <p class="stat-value"><?php echo date('Y-m-d', strtotime($user_info['created_at'])); ?></p>
        </div>
        
        <div class="stat-card">
            <h3>上次登录</h3>
            <p class="stat-value"><?php echo $user_info['last_login'] ? date('Y-m-d H:i', strtotime($user_info['last_login'])) : '首次登录'; ?></p>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <h3>快速操作</h3>
        <div class="action-buttons">
            <a href="pet.php" class="action-button">
                <span class="action-icon">🐾</span>
                <span class="action-text">我的宠物</span>
            </a>
            <a href="shop.php" class="action-button">
                <span class="action-icon">🛒</span>
                <span class="action-text">商店</span>
            </a>
            <a href="profile.php" class="action-button">
                <span class="action-icon">👤</span>
                <span class="action-text">个人资料</span>
            </a>
        </div>
    </div>
    
    <!-- 这里将来会显示用户的宠物列表 -->
    <div class="pet-list-container">
        <h3>我的宠物</h3>
        <p>您还没有宠物。<a href="pet.php?action=create">创建一个新宠物</a>吧！</p>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
