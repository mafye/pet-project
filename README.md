# 电子宠物项目

一个基于PHP和MySQL的虚拟宠物养成系统，可以在XAMPP环境中运行。

## 项目概述

这个项目允许用户创建和照顾虚拟宠物。用户可以通过各种互动（喂食、玩耍、睡觉等）来照顾他们的宠物，宠物会根据互动做出反应并改变状态。

### 主要功能

- 用户注册和登录系统 ✅
- 创建和自定义宠物 ✅
- 宠物互动（喂食、玩耍、睡觉等）✅
- 宠物状态系统（饥饿度、心情、健康等）✅
- 多样化的宠物反馈 🚧
- 宠物成长系统 ✅
- 物品商店 🚧

## 技术架构

- **前端**: HTML, CSS, JavaScript
- **后端**: PHP
- **数据库**: MySQL
- **环境**: XAMPP

## 文件结构

```
pet_project/
├── index.php                 # 入口文件/登录页面 ✅
├── register.php              # 注册页面 ✅
├── dashboard.php             # 用户仪表板/宠物选择 ✅
├── logout.php                # 退出登录 ✅
├── pet.php                   # 宠物互动主界面 ✅
├── shop.php                  # 宠物商店 🚧
├── profile.php               # 用户资料 🚧
├── README.md                 # 项目说明 ✅
├── database.sql              # 数据库结构 ✅
├── config/
│   └── db.php                # 数据库配置 ✅
├── includes/
│   ├── functions.php         # 通用函数 ✅
│   ├── User.php              # 用户类 ✅
│   ├── Pet.php               # 宠物类 ✅
│   ├── PetResponse.php       # 宠物反馈类 🚧
│   └── Item.php              # 物品类 🚧
├── assets/
│   ├── css/
│   │   └── styles.css        # 主样式表 ✅
│   ├── js/
│   │   ├── main.js           # 主脚本 🚧
│   │   └── pet.js            # 宠物互动脚本 ✅
│   └── images/
│       ├── pets/             # 宠物图像 🚧
│       └── items/            # 物品图像 🚧
└── templates/
    ├── header.php            # 页头模板 ✅
    ├── footer.php            # 页脚模板 ✅
    └── pet_display.php       # 宠物显示模板 🚧
```

## 数据库设计

### users表 ✅
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  points INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME
);
```

### pets表 ✅
```sql
CREATE TABLE pets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  type VARCHAR(20) NOT NULL,
  level INT DEFAULT 1,
  experience INT DEFAULT 0,
  hunger INT DEFAULT 100,
  happiness INT DEFAULT 100,
  energy INT DEFAULT 100,
  health INT DEFAULT 100,
  last_interaction DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### pet_responses表 ✅
```sql
CREATE TABLE pet_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action_type VARCHAR(50) NOT NULL,
  mood_type VARCHAR(50),
  response_text TEXT NOT NULL,
  min_level INT DEFAULT 1,
  rarity INT DEFAULT 1
);
```

### items表 ✅
```sql
CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(20) NOT NULL,
  description TEXT NOT NULL,
  effects TEXT NOT NULL,
  price INT NOT NULL,
  image_url VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### user_items表 ✅
```sql
CREATE TABLE user_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (item_id) REFERENCES items(id)
);
```

### interactions表 ✅
```sql
CREATE TABLE interactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pet_id INT NOT NULL,
  action_type VARCHAR(50) NOT NULL,
  points_earned INT DEFAULT 0,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pet_id) REFERENCES pets(id)
);
```

## 项目进度

- ✅ 完成
- 🚧 开发中
- ⏳ 计划中

### 当前进度

1. **基础框架** ✅
   - 设置数据库 ✅
   - 创建基本文件结构 ✅
   - 实现用户注册/登录 ✅

2. **宠物系统核心** ✅
   - 宠物类实现 ✅
   - 宠物创建 ✅
   - 基本属性系统 ✅
   - 简单互动功能 ✅
   - 宠物表情显示 ✅

3. **互动与反馈** 🚧
   - 实现多样化反馈 🚧
   - 宠物状态变化 ✅
   - 成长系统 ✅

4. **扩展功能** ⏳
   - 商店系统 ⏳
   - 物品效果 ⏳
   - 成就系统 ⏳

5. **优化与完善** ⏳
   - 界面美化 🚧
   - 用户体验改进 ⏳
   - 性能优化 ⏳

## 安装说明

1. 克隆仓库到XAMPP的htdocs目录
2. 创建MySQL数据库 `pet_project`
3. 导入数据库结构（使用项目中的database.sql文件）
4. 配置config/db.php文件中的数据库连接信息
5. 访问http://localhost/pet_project/

## 贡献者

- [您的名字]

## 许可证

MIT许可证
