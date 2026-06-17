#!/bin/bash

# PC28 加拿大 宝塔一键部署脚本
# 适用环境: CentOS 7/8, Ubuntu 18.04+, Debian 9+
# 依赖: MySQL 5.6, PHP 7.2, Nginx

echo "================================================="
echo "   PC28 加拿大 平台一键安装脚本 (宝塔环境)   "
echo "================================================="

# 1. 检查权限
if [ "$EUID" -ne 0 ]; then
  echo "请以 root 权限运行此脚本"
  die "Require root"
fi

# 2. 获取参数
echo "请输入数据库名 (默认: pc28_db): "
read dbname
dbname=${dbname:-pc28_db}
echo "请输入数据库用户名 (默认: root): "
read dbuser
dbuser=${dbuser:-root}
echo "请输入数据库密码: "
read dbpass

# 3. 创建数据库
mysql -u$dbuser -p$dbpass -e "CREATE DATABASE IF NOT EXISTS $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;"

# 4. 导入数据
mysql -u$dbuser -p$dbpass $dbname < ./database/mysql_schema.sql
echo "数据库导入完成"

# 5. 修改配置文件
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
echo "配置文件已更新"

# 6. 设置权限
chmod -R 755 ./
chown -R www:www ./
echo "目录权限设置完成"

# 7. 提示 Cron 任务
echo ""
echo "================================================="
echo "安装完成！请在宝塔计划任务中添加以下两条任务："
echo "1. 开奖脚本 (每5分钟):"
echo "   php $(pwd)/scripts/scraper.php"
echo "2. 结算脚本 (每1分钟):"
echo "   php $(pwd)/scripts/settle.php"
echo "================================================="
echo "默认管理后台: /admin/index.php"
echo "管理账号: admin / admin123"
echo "================================================="
