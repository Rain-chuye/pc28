<h3>用户管理</h3>
<?php
// In a real environment, we would fetch users from the database
// $users = User::getAll();
?>
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>余额 (¥)</th>
                    <th>状态</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>demo_user</td>
                    <td>12,580.00</td>
                    <td><span class="badge bg-success">正常</span></td>
                    <td>2024-06-16</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger">冻结</button>
                        <button class="btn btn-sm btn-outline-primary">充值</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
