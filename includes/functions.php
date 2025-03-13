<?php
// 启动会话（如果尚未启动）
function start_session_if_not_started() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// 检查用户是否已登录
function is_logged_in() {
    start_session_if_not_started();
    return isset($_SESSION['user_id']);
}

// 重定向到指定页面
function redirect($location) {
    header("Location: $location");
    exit;
}

// 安全地获取POST数据
function get_post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// 安全地获取GET数据
function get_get($key, $default = '') {
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

// 显示错误消息
function display_error($message) {
    return "<div class='error-message'>$message</div>";
}

// 显示成功消息
function display_success($message) {
    return "<div class='success-message'>$message</div>";
}

// 生成CSRF令牌
function generate_csrf_token() {
    start_session_if_not_started();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 验证CSRF令牌
function verify_csrf_token($token) {
    start_session_if_not_started();
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}
?>
