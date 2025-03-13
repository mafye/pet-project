<?php
// 启动会话
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - 我的虚拟宠物' : '我的虚拟宠物'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <header>
            <h1>我的虚拟宠物</h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <nav>
                    <ul>
                        <li><a href="dashboard.php">仪表板</a></li>
                        <li><a href="pet.php">我的宠物</a></li>
                        <li><a href="shop.php">商店</a></li>
                        <li><a href="profile.php">个人资料</a></li>
                        <li><a href="logout.php">退出</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </header>
        <main>
