<?php
require_once __DIR__ . '/../../src/Model/User.php';
$users = \App\Model\User::getAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">用户管理</h1>
</div>

<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>角色</th>
                    <th>余额 (¥)</th>
                    <th>状态</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td><span class="badge bg-secondary"><?php echo $user['role']; ?></span></td>
                    <td class="text-success font-monospace">¥ <?php echo number_format($user['balance'], 2); ?></td>
                    <td>
                        <?php if ($user['status'] == 1): ?>
                            <span class="badge bg-success">正常</span>
                        <?php else: ?>
                            <span class="badge bg-danger">锁定</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">冻结</button>
                        <button class="btn btn-sm btn-outline-primary">充值</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
