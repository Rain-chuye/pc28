<?php
require_once __DIR__ . '/auth_logic.php';
?>
<style>
    /* Mobile-first UA styles */
    :root { --admin-primary: #4f46e5; }
    body { margin: 0; padding-bottom: 100px; font-family: -apple-system, sans-serif; background: #f8fafc; }
    .mobile-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-around; padding: 15px 0; z-index: 1000; box-shadow: 0 -4px 15px rgba(0,0,0,0.03); }
    .nav-link { display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #94a3b8; font-size: 10px; font-weight: 800; gap: 4px; }
    .nav-link.active { color: var(--admin-primary); }
    .nav-link i { font-size: 20px; }
    .admin-header { background: white; padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 900; }
    .admin-header h1 { margin: 0; font-size: 16px; font-weight: 900; color: #1e293b; }
    .card { background: white; margin: 15px; padding: 20px; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.01), 0 2px 4px -1px rgba(0,0,0,0.01); }
    .btn-indigo { background: var(--admin-primary); color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 900; font-size: 12px; cursor: pointer; transition: all 0.2s; }
    .btn-indigo:active { transform: scale(0.95); opacity: 0.8; }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px; }
    .form-input { width: 100%; border: 1px solid #e2e8f0; background: #f8fafc; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 600; outline: none; box-sizing: border-box; }
    .form-input:focus { border-color: var(--admin-primary); background: white; }
    .admin-sidebar { display: none !important; }
    .admin-main { margin-left: 0 !important; padding: 0 !important; }
</style>
<div class="mobile-nav">
    <a href="/admin/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : '' ?>"><i class="fas fa-th-large"></i><span>概览</span></a>
    <a href="/admin/pages/users.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'active' : '' ?>"><i class="fas fa-users"></i><span>会员</span></a>
    <a href="/admin/pages/chat_admin.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'chat_admin.php') !== false ? 'active' : '' ?>"><i class="fas fa-comments"></i><span>聊天</span></a>
    <a href="/admin/pages/chat.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'chat.php') !== false ? 'active' : '' ?>"><i class="fas fa-headset"></i><span>客服</span></a>
    <a href="/admin/pages/finance_list.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'finance_list.php') !== false ? 'active' : '' ?>"><i class="fas fa-exchange-alt"></i><span>财务</span></a>
</div>
