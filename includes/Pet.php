<?php
class Pet {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // 创建新宠物
    public function createPet($userId, $name, $type) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO pets (user_id, name, type) VALUES (:user_id, :name, :type)");
            $stmt->execute([
                ':user_id' => $userId,
                ':name' => $name,
                ':type' => $type
            ]);
            
            return [
                'success' => true, 
                'message' => '宠物创建成功！', 
                'pet_id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '创建宠物失败: ' . $e->getMessage()];
        }
    }
    
    // 获取用户的所有宠物
    public function getUserPets($userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM pets WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // 获取单个宠物信息
    public function getPetById($petId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM pets WHERE id = :id");
            $stmt->execute([':id' => $petId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // 更新宠物状态
    public function updatePetStatus($petId, $data) {
        $fields = [];
        $params = [':id' => $petId];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['hunger', 'happiness', 'energy', 'health', 'experience', 'level'])) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => '没有提供有效的更新字段'];
        }
        
        try {
            $sql = "UPDATE pets SET " . implode(', ', $fields) . ", last_interaction = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return ['success' => true, 'message' => '宠物状态已更新'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => '更新宠物状态失败: ' . $e->getMessage()];
        }
    }
    
    // 互动并更新宠物状态
    public function interact($petId, $actionType) {
        // 获取当前宠物状态
        $pet = $this->getPetById($petId);
        if (!$pet) {
            return ['success' => false, 'message' => '找不到宠物'];
        }
        
        // 根据互动类型更新宠物状态
        $updates = [];
        $pointsEarned = 0;
        
        switch ($actionType) {
            case 'feed':
                $updates['hunger'] = min(100, $pet['hunger'] + 30);
                $updates['energy'] = min(100, $pet['energy'] + 10);
                $pointsEarned = 5;
                break;
                
            case 'play':
                $updates['happiness'] = min(100, $pet['happiness'] + 30);
                $updates['hunger'] = max(0, $pet['hunger'] - 10);
                $updates['energy'] = max(0, $pet['energy'] - 15);
                $pointsEarned = 10;
                break;
                
            case 'sleep':
                $updates['energy'] = min(100, $pet['energy'] + 50);
                $updates['health'] = min(100, $pet['health'] + 10);
                $pointsEarned = 5;
                break;
                
            case 'clean':
                $updates['health'] = min(100, $pet['health'] + 20);
                $updates['happiness'] = min(100, $pet['happiness'] + 10);
                $pointsEarned = 5;
                break;
                
            default:
                return ['success' => false, 'message' => '未知的互动类型'];
        }
        
        // 更新经验值
        $updates['experience'] = $pet['experience'] + $pointsEarned;
        
        // 检查是否升级
        $currentLevel = $pet['level'];
        $newLevel = $this->calculateLevel($updates['experience']);
        
        if ($newLevel > $currentLevel) {
            $updates['level'] = $newLevel;
        }
        
        // 更新宠物状态
        $result = $this->updatePetStatus($petId, $updates);
        
        if ($result['success']) {
            // 记录互动
            $this->recordInteraction($petId, $actionType, $pointsEarned);
            
            // 获取互动响应
            $response = $this->getInteractionResponse($actionType, $this->getPetMood($updates));
            
            return [
                'success' => true,
                'message' => $result['message'],
                'response' => $response,
                'points_earned' => $pointsEarned,
                'level_up' => $newLevel > $currentLevel,
                'new_level' => $newLevel,
                'updates' => $updates
            ];
        }
        
        return $result;
    }

    public function getPetEmoji() {
        // Determine the pet's primary state based on its lowest attribute
        $lowestAttribute = min($this->hunger, $this->happiness, $this->energy, $this->health);
        
        // Return emoji based on the pet's state
        if ($lowestAttribute <= 20) {
            // Critical state
            if ($this->hunger <= 20) return "😫"; // Very hungry
            if ($this->happiness <= 20) return "😭"; // Very sad
            if ($this->energy <= 20) return "😴"; // Very tired
            if ($this->health <= 20) return "🤒"; // Very sick
        } else if ($lowestAttribute <= 50) {
            // Concerning state
            if ($this->hunger <= 50) return "😕"; // Hungry
            if ($this->happiness <= 50) return "😔"; // Sad
            if ($this->energy <= 50) return "😩"; // Tired
            if ($this->health <= 50) return "😷"; // Sick
        } else if ($lowestAttribute <= 80) {
            // Okay state
            return "😊"; // Content
        } else {
            // Great state
            return "😁"; // Very happy
        }
        
        // Default emoji if no conditions are met
        return "🐾";
    }
    
    // 记录互动
    private function recordInteraction($petId, $actionType, $pointsEarned) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO interactions (pet_id, action_type, points_earned) VALUES (:pet_id, :action_type, :points_earned)");
            $stmt->execute([
                ':pet_id' => $petId,
                ':action_type' => $actionType,
                ':points_earned' => $pointsEarned
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // 获取互动响应
    private function getInteractionResponse($actionType, $mood) {
        try {
            // 尝试获取与动作和心情匹配的响应
            $stmt = $this->pdo->prepare("
                SELECT response_text FROM pet_responses 
                WHERE action_type = :action_type AND mood_type = :mood_type
                ORDER BY RAND() LIMIT 1
            ");
            $stmt->execute([
                ':action_type' => $actionType,
                ':mood_type' => $mood
            ]);
            $response = $stmt->fetch();
            
            if ($response) {
                return $response['response_text'];
            }
            
            // 如果没有特定心情的响应，尝试获取通用响应
            $stmt = $this->pdo->prepare("
                SELECT response_text FROM pet_responses 
                WHERE action_type = :action_type AND mood_type IS NULL
                ORDER BY RAND() LIMIT 1
            ");
            $stmt->execute([':action_type' => $actionType]);
            $response = $stmt->fetch();
            
            if ($response) {
                return $response['response_text'];
            }
            
            // 默认响应
            $defaultResponses = [
                'feed' => '你的宠物吃得很开心！',
                'play' => '你的宠物玩得很开心！',
                'sleep' => '你的宠物睡得很香！',
                'clean' => '你的宠物现在干净多了！',
            ];
            
            return $defaultResponses[$actionType] ?? '你的宠物对你的互动做出了反应。';
            
        } catch (PDOException $e) {
            return '你的宠物对你的互动做出了反应。';
        }
    }
    
    // 根据宠物状态确定心情
    private function getPetMood($status) {
        // 计算平均状态值
        $avgStatus = ($status['hunger'] + $status['happiness'] + $status['energy'] + $status['health']) / 4;
        
        if ($avgStatus >= 80) {
            return 'happy';
        } elseif ($avgStatus >= 50) {
            return 'normal';
        } elseif ($avgStatus >= 30) {
            return 'sad';
        } else {
            return 'angry';
        }
    }
    
    // 计算等级
    private function calculateLevel($experience) {
        // 简单的等级计算公式：每100点经验升一级
        return floor($experience / 100) + 1;
    }
}
?> 