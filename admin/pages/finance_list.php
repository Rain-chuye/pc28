<?php
// require_once __DIR__ . '/../../src/Utils/DB.php';
// $db = \App\Utils\DB::getInstance()->getConnection();
// Simple PHP template for Admin to list and approve/reject
?>
<div class="card">
    <div class="card-header">充值提现管理</div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户</th>
                    <th>类型</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>凭证</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="finance-table-body">
                <!-- JS will load data here -->
            </tbody>
        </table>
    </div>
</div>

<script>
function loadFinance() {
    fetch('/admin/api/finance_list_all.php')
    .then(r => r.json())
    .then(res => {
        const body = document.getElementById('finance-table-body');
        body.innerHTML = res.data.map(item => `
            <tr>
                <td>${item.id}</td>
                <td>${item.username}</td>
                <td>${item.type === 'deposit' ? '充值' : '提现'}</td>
                <td>${item.amount}</td>
                <td>${item.status}</td>
                <td>${item.proof_img ? '<a href="/uploads/' + item.proof_img + '" target="_blank">查看</a>' : '-'}</td>
                <td>
                    ${item.status === 'pending' ? `
                        <button onclick="review(${item.id}, 'approved')" class="btn btn-sm btn-success">通过</button>
                        <button onclick="review(${item.id}, 'rejected')" class="btn btn-sm btn-danger">驳回</button>
                    ` : '-'}
                </td>
            </tr>
        `).join('');
    });
}

function review(id, status) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);
    fetch('/admin/api/finance_review.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            alert('操作成功');
            loadFinance();
        } else {
            alert(res.message);
        }
    });
}
loadFinance();
</script>
