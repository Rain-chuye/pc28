#!/bin/bash

# PC28 加拿大 宝塔一键完美部署脚本
# 适用环境: Linux + 宝塔面板 (MySQL 5.6, PHP 7.2, Nginx)

echo "================================================="
echo "   PC28 加拿大 平台一键完美安装脚本   "
echo "================================================="

# 1. 参数输入
echo "请输入数据库名 (默认: pc28_db): "
read dbname
dbname=${dbname:-pc28_db}
echo "请输入数据库用户名 (默认: root): "
read dbuser
dbuser=${dbuser:-root}
echo "请输入数据库密码: "
read dbpass

# 2. 初始化数据库
echo "正在创建数据库 $dbname..."
mysql -u$dbuser -p$dbpass -e "CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

echo "正在导入表结构..."
mysql -u$dbuser -p$dbpass $dbname < ./database/mysql_schema.sql

# 3. 生成配置文件
echo "配置数据库连接..."
cat << CONFIG > ./src/Config/database.php
<?php
return [
    'host' => 'localhost',
    'dbname' => '$dbname',
    'user' => '$dbuser',
    'password' => '$dbpass',
    'port' => 3306,
    'driver' => 'mysql'
];
CONFIG

# 4. 设置文件权限
echo "优化文件权限..."
chmod -R 755 ./

# 5. 自动配置 Cron (尝试自动添加)
CRON_SCRAPER="*/5 * * * * php $(pwd)/scripts/scraper.php >> $(pwd)/scripts/scraper.log 2>&1"
CRON_SETTLE="* * * * * php $(pwd)/scripts/settle.php >> $(pwd)/scripts/settle.log 2>&1"

(crontab -l 2>/dev/null | grep -v "scripts/scraper.php" | grep -v "scripts/settle.php"; echo "$CRON_SCRAPER"; echo "$CRON_SETTLE") | crontab -

echo ""
echo "================================================="
echo "✅ 安装成功！"
echo "================================================="
echo "1. 域名指向: $(pwd)/public"
echo "2. 定时任务: 已自动添加到 crontab"
echo "3. 管理后台: /admin/index.php"
echo "4. 管理账号: admin / admin123"
echo "================================================="
echo "祝您运营顺利，红红火火！"
echo "================================================="
