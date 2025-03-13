<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/User.php';
require_once 'includes/Pet.php';

// 检查用户是否已登录
if (!is_logged_in()) {
    redirect('index.php');
}

$user = new User($pdo);
$pet_manager = new Pet($pdo);
$user_info = $user->getUserById($_SESSION['user_id']);

$error = '';
$success = '';
$pet_response = '';

// 处理创建新宠物
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_pet') {
    $name = get_post('pet_name');
    $type = get_post('pet_type');
    $csrf_token = get_post('csrf_token');
    
    // 验证CSRF令牌
    if (!verify_csrf_token($csrf_token)) {
        $error = '安全验证失败，请重试';
    } elseif (empty($name)) {
        $error = '请输入宠物名称';
    } elseif (empty($type)) {
        $error = '请选择宠物类型';
    } else {
        $result = $pet_manager->createPet($_SESSION['user_id'], $name, $type);
        
        if ($result['success']) {
            $success = $result['message'];
            // 更新用户积分
            $user->updatePoints($_SESSION['user_id'], 50); // 创建宠物奖励50积分
        } else {
            $error = $result['message'];
        }
    }
}

// 处理宠物互动
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'interact') {
    $pet_id = get_post('pet_id');
    $interaction_type = get_post('interaction_type');
    $csrf_token = get_post('csrf_token');
    
    // 验证CSRF令牌
    if (!verify_csrf_token($csrf_token)) {
        $error = '安全验证失败，请重试';
    } elseif (empty($pet_id) || empty($interaction_type)) {
        $error = '无效的请求';
    } else {
        $result = $pet_manager->interact($pet_id, $interaction_type);
        
        if ($result['success']) {
            $pet_response = $result['response'];
            $success = '互动成功！';
            
            // 更新用户积分
            if ($result['points_earned'] > 0) {
                $user->updatePoints($_SESSION['user_id'], $result['points_earned']);
            }
            
            // 如果宠物升级
            if ($result['level_up']) {
                $success .= ' 恭喜！您的宠物升级到了 ' . $result['new_level'] . ' 级！';
                // 额外奖励积分
                $user->updatePoints($_SESSION['user_id'], 20 * $result['new_level']);
            }
        } else {
            $error = $result['message'];
        }
    }
}

// 获取当前宠物ID（如果有）
$current_pet_id = isset($_GET['pet_id']) ? intval($_GET['pet_id']) : 0;

// 获取用户的所有宠物
$user_pets = $pet_manager->getUserPets($_SESSION['user_id']);

// 如果用户有宠物但没有指定当前宠物，则使用第一个宠物
if (!empty($user_pets) && $current_pet_id === 0) {
    $current_pet_id = $user_pets[0]['id'];
}

// 获取当前宠物信息
$current_pet = $current_pet_id > 0 ? $pet_manager->getPetById($current_pet_id) : null;

// 设置页面标题
$page_title = $current_pet ? htmlspecialchars($current_pet['name']) : '创建宠物';

// 添加宠物互动脚本
$extra_js = ['assets/js/pet.js'];

include 'templates/header.php';
?>

<div class="pet-container">
    <?php if (!empty($error)): ?>
        <?php echo display_error($error); ?>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <?php echo display_success($success); ?>
    <?php endif; ?>
    
    <?php if (empty($user_pets)): ?>
        <!-- 创建新宠物表单 -->
        <div class="form-container">
            <h2 class="form-title">创建您的第一个宠物</h2>
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create_pet">
                
                <div class="form-group">
                    <label for="pet_name">宠物名称</label>
                    <input type="text" id="pet_name" name="pet_name" required>
                </div>
                
                <div class="form-group">
                    <label for="pet_type">宠物类型</label>
                    <select id="pet_type" name="pet_type" required>
                        <option value="">-- 选择类型 --</option>
                        <option value="dog">狗</option>
                        <option value="cat">猫</option>
                        <option value="rabbit">兔子</option>
                        <option value="bird">鸟</option>
                        <option value="fish">鱼</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit">创建宠物</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <!-- 宠物选择器 -->
        <div class="pet-selector">
            <h3>我的宠物</h3>
            <div class="pet-tabs">
                <?php foreach ($user_pets as $pet): ?>
                    <a href="pet.php?pet_id=<?php echo $pet['id']; ?>" class="pet-tab <?php echo $pet['id'] == $current_pet_id ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($pet['name']); ?>
                    </a>
                <?php endforeach; ?>
                <a href="pet.php?action=create" class="pet-tab add-pet">+ 添加宠物</a>
            </div>
        </div>
        
        <?php if (isset($_GET['action']) && $_GET['action'] === 'create'): ?>
            <!-- 创建新宠物表单 -->
            <div class="form-container">
                <h2 class="form-title">创建新宠物</h2>
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="create_pet">
                    
                    <div class="form-group">
                        <label for="pet_name">宠物名称</label>
                        <input type="text" id="pet_name" name="pet_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pet_type">宠物类型</label>
                        <select id="pet_type" name="pet_type" required>
                            <option value="">-- 选择类型 --</option>
                            <option value="dog">狗</option>
                            <option value="cat">猫</option>
                            <option value="rabbit">兔子</option>
                            <option value="bird">鸟</option>
                            <option value="fish">鱼</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">创建宠物</button>
                        <a href="pet.php" class="button button-secondary">取消</a>
                    </div>
                </form>
            </div>
        <?php elseif ($current_pet): ?>
            <!-- 宠物显示和互动界面 -->
            <div class="pet-display">
                <div class="pet-avatar">
                    <?php
                    $pet_type = $current_pet['type'];
                    $pet_image = "assets/images/pets/{$pet_type}.png";
                    if (!file_exists($pet_image)) {
                        $pet_image = "assets/images/pets/default.png";
                    }
                    ?>
                    <img src="<?php echo $pet_image; ?>" alt="<?php echo htmlspecialchars($current_pet['name']); ?>">
                </div>
                
                <div class="pet-info">
                    <h2><?php echo htmlspecialchars($current_pet['name']); ?></h2>
                    <p>类型: <?php echo ucfirst($current_pet['type']); ?></p>
                    <p>等级: <?php echo $current_pet['level']; ?></p>
                    <p>经验: <?php echo $current_pet['experience']; ?> / <?php echo $current_pet['level'] * 100; ?></p>
                </div>
                
                <?php if (!empty($pet_response)): ?>
                    <div class="pet-response">
                        <p><?php echo $pet_response; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="pet-status">
                    <div class="status-bar">
                        <label>饥饿度</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $current_pet['hunger']; ?>%"></div>
                        </div>
                        <span><?php echo $current_pet['hunger']; ?>%</span>
                    </div>
                    
                    <div class="status-bar">
                        <label>心情</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $current_pet['happiness']; ?>%"></div>
                        </div>
                        <span><?php echo $current_pet['happiness']; ?>%</span>
                    </div>
                    
                    <div class="status-bar">
                        <label>能量</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $current_pet['energy']; ?>%"></div>
                        </div>
                        <span><?php echo $current_pet['energy']; ?>%</span>
                    </div>
                    
                    <div class="status-bar">
                        <label>健康</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $current_pet['health']; ?>%"></div>
                        </div>
                        <span><?php echo $current_pet['health']; ?>%</span>
                    </div>
                </div>
                
                <div class="pet-actions">
                    <h3>互动</h3>
                    <div class="action-buttons">
                        <form method="post" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="interact">
                            <input type="hidden" name="pet_id" value="<?php echo $current_pet['id']; ?>">
                            
                            <button type="submit" name="interaction_type" value="feed" class="action-button">
                                <span class="action-icon">🍖</span>
                                <span class="action-text">喂食</span>
                            </button>
                            
                            <button type="submit" name="interaction_type" value="play" class="action-button">
                                <span class="action-icon">🎾</span>
                                <span class="action-text">玩耍</span>
                            </button>
                            
                            <button type="submit" name="interaction_type" value="sleep" class="action-button">
                                <span class="action-icon">😴</span>
                                <span class="action-text">睡觉</span>
                            </button>
                            
                            <button type="submit" name="interaction_type" value="clean" class="action-button">
                                <span class="action-icon">🚿</span>
                                <span class="action-text">清洁</span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="pet-history">
                    <h3>最近互动</h3>
                    <p class="text-center">即将推出...</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?> 