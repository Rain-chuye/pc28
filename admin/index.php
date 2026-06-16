<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.html');
    die();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PC28 管理后台</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #212529; color: white; }
        .nav-link { color: rgba(255,255,255,0.7); }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar py-4">
                <div class="px-3 mb-4">
                    <h5 class="text-amber-500"><i class="fas fa-dice"></i> PC28 后台</h5>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="?page=users"><i class="fas fa-users me-2"></i> 用户管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=odds"><i class="fas fa-percentage me-2"></i> 赔率设置</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=bets"><i class="fas fa-history me-2"></i> 投注历史</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=finance"><i class="fas fa-wallet me-2"></i> 充提管理</a></li>
                    <li class="nav-item mt-4"><a class="nav-link text-danger" href="/api/logout.php"><i class="fas fa-sign-out-alt me-2"></i> 退出登录</a></li>
                </ul>
            </nav>
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <?php
                $page = $_GET['page'] ?? 'users';
                $pagePath = __DIR__ . "/pages/$page.php";
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    echo "<h3>Page not found</h3>";
                }
                ?>
            </main>
        </div>
    </div>
</body>
</html>
