<?php
session_start();
// Admin auth check
if (!isset($_SESSION['admin_logged_in'])) {
    // header('Location: login.php');
    // For now, allow for development
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PC28 Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="?page=users">用户管理</a></li>
                        <li class="nav-item"><a class="nav-link" href="?page=odds">赔率设置</a></li>
                        <li class="nav-item"><a class="nav-link" href="?page=bets">投注历史</a></li>
                        <li class="nav-item"><a class="nav-link" href="?page=finance">充提管理</a></li>
                    </ul>
                </div>
            </nav>
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                <?php
                $page = $_GET['page'] ?? 'users';
                include "pages/$page.php";
                ?>
            </main>
        </div>
    </div>
</body>
</html>
