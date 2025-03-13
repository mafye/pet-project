<?php
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // 注册新用户
    public function register($username, $email, $password) {
        // 检查用户名是否已存在
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => '用户名已被使用'];
        }
        
        // 检查邮箱是否已存在
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => '邮箱已被注册'];
        }
        
        // 哈希密码
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashed_password
            ]);
            
            return ['success' => true, 'message' => '注册成功！', 'user_id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '注册失败: ' . $e->getMessage()];
        }
    }
    
    // 用户登录
    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // 更新最后登录时间
                $update = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $update->execute([':id' => $user['id']]);
                
                return ['success' => true, 'message' => '登录成功！', 'user_id' => $user['id'], 'username' => $user['username']];
            } else {
                return ['success' => false, 'message' => '用户名或密码不正确'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '登录失败: ' . $e->getMessage()];
        }
    }
    
    // 检查用户名是否存在
    private function usernameExists($username) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return $stmt->fetchColumn() > 0;
    }
    
    // 检查邮箱是否存在
    private function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    // 获取用户信息
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id, username, email, points, created_at, last_login FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    // 更新用户积分
    public function updatePoints($userId, $points) {
        $stmt = $this->pdo->prepare("UPDATE users SET points = points + :points WHERE id = :id");
        return $stmt->execute([
            ':points' => $points,
            ':id' => $userId
        ]);
    }
}
?>
