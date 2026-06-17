<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
    <h3 class="font-black mb-8">财务申请审核</h3>
    <table class="w-full text-left">
        <thead>
            <tr class="text-xs text-slate-400 uppercase tracking-widest">
                <th class="pb-4">ID</th>
                <th class="pb-4">用户</th>
                <th class="pb-4">类型</th>
                <th class="pb-4">金额</th>
                <th class="pb-4">状态</th>
                <th class="pb-4">申请时间</th>
                <th class="pb-4">操作</th>
            </tr>
        </thead>
        <tbody id="finance-table-body"></tbody>
    </table>
</div>

<script>
async function loadFinance() {
    const res = await fetch('/admin/api/finance_list_all.php');
    const data = await res.json();
    const body = document.getElementById('finance-table-body');
    body.innerHTML = data.data.map(item => `
        <tr class="border-t border-slate-50">
            <td class="py-4 text-sm">${item.id}</td>
            <td class="py-4 text-sm font-bold">${item.username}</td>
            <td class="py-4 text-sm">
                <span class="px-2 py-1 rounded-md text-[10px] font-bold ${item.type === 'deposit' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}">
                    ${item.type === 'deposit' ? '充值' : '提现'}
                </span>
            </td>
            <td class="py-4 text-sm font-black">¥ ${parseFloat(item.amount).toFixed(2)}</td>
            <td class="py-4 text-sm">
                <span class="text-[10px] font-bold ${item.status === 'pending' ? 'text-amber-500' : (item.status === 'approved' ? 'text-emerald-500' : 'text-slate-300')}">
                    ${item.status === 'pending' ? '待处理' : (item.status === 'approved' ? '已通过' : '已驳回')}
                </span>
            </td>
            <td class="py-4 text-xs text-slate-400">${item.created_at}</td>
            <td class="py-4">
                ${item.status === 'pending' ? `
                    <button onclick="review(${item.id}, 'approved')" class="text-emerald-600 text-xs font-bold mr-3">通过</button>
                    <button onclick="review(${item.id}, 'rejected')" class="text-rose-600 text-xs font-bold">驳回</button>
                ` : '-'}
            </td>
        </tr>
    `).join('');
}

async function review(id, status) {
    if(!confirm('确定要执行此操作吗？')) return;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);
    const res = await fetch('/admin/api/finance_review.php', { method: 'POST', body: formData });
    const data = await res.json();
    if(data.success) {
        alert('操作成功');
        loadFinance();
    } else alert(data.message);
}
loadFinance();
</script>
