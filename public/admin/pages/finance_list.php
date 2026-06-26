<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>财务审核 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .token-box { word-break: break-all; font-family: monospace; }
        .proof-img-admin { max-width: 100%; border-radius: 1rem; cursor: zoom-in; margin-top: 10px; border: 1px solid #f1f5f9; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="admin-header">
        <h1>财务审核流水</h1>
        <button onclick="loadFinance()" class="text-indigo-600"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main id="finance-container" class="p-4 space-y-4 pt-2">
        <div class="p-10 text-center text-slate-300 font-bold text-xs uppercase">同步实时账单中...</div>
    </main>

    <script>
        async function loadFinance() {
            try {
                const res = await fetch('/admin/api/finance_list_all.php').then(r => r.json());
                if(res.success) {
                    const container = document.getElementById('finance-container');
                    container.innerHTML = res.data.map(f => {
                        const isDeposit = f.type === 'deposit';
                        const statusColor = f.status === 'pending' ? 'bg-amber-50 text-amber-600' : (f.status === 'approved' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600');

                        let proofHtml = '';
                        if(f.proof_img) {
                            if(f.proof_img.includes('data:image')) {
                                proofHtml = `<img src="${f.proof_img}" class="proof-img-admin" onclick="window.open(this.src)">`;
                            } else {
                                proofHtml = `<div class="token-box text-xs font-black text-slate-800 select-all">${f.proof_img}</div>
                                             <button onclick="copyToClipboard('${f.proof_img}')" class="mt-3 text-[9px] font-black text-blue-600 uppercase border-b-2 border-blue-100 pb-0.5">复制详细信息</button>`;
                            }
                        }

                        return `
                            <div class="card p-6 border border-slate-100 shadow-sm relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">${isDeposit ? '📥 充值上分' : '📤 提现回分'}</p>
                                        <h3 class="text-lg font-black text-slate-800">¥ ${parseFloat(f.amount).toLocaleString()}</h3>
                                    </div>
                                    <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase ${statusColor}">${f.status}</span>
                                </div>

                                <div class="space-y-3">
                                    <p class="text-[10px] font-bold text-slate-500 uppercase">会员: <span class="text-slate-800 font-black">${f.username} (UID: ${f.user_id})</span></p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase">时间: ${f.created_at}</p>

                                    ${isDeposit ? `
                                        <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                            <p class="text-[8px] font-black text-indigo-500 uppercase mb-2">支付宝口令 / 凭证</p>
                                            ${proofHtml}
                                        </div>
                                    ` : `<div class="text-[10px] font-bold text-slate-500 uppercase">回分方式/账号: <span class="text-rose-600 font-black">${f.proof_img}</span></div>`}
                                </div>

                                ${f.status === 'pending' ? `
                                    <div class="flex gap-3 mt-6 pt-6 border-t border-slate-50">
                                        <button onclick="review(${f.id}, 'approved')" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase shadow-lg shadow-indigo-100">确认入账</button>
                                        <button onclick="review(${f.id}, 'rejected')" class="flex-1 py-3 bg-slate-100 text-slate-400 rounded-xl font-black text-[10px] uppercase">驳回</button>
                                    </div>
                                ` : (f.status === 'rejected' ? `<p class="mt-4 text-[9px] font-black text-rose-400 uppercase">原因: ${f.refusal_reason || '无'}</p>` : '')}
                            </div>
                        `;
                    }).join('');
                }
            } catch (e) { console.error(e); }
        }

        async function review(id, status) {
            let reason = '';
            if(status === 'rejected') reason = prompt('请输入驳回原因:');

            const res = await fetch('/admin/api/finance_review.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id=${id}&status=${status}&reason=${reason}`
            }).then(r => r.json());

            if(res.success) {
                alert('状态已更新');
                loadFinance();
            } else alert(res.message);
        }

        function copyToClipboard(text) {
            const clean = text.replace('口令: ', '');
            navigator.clipboard.writeText(clean).then(() => alert('内容已复制'));
        }

        loadFinance();
    </script>
</body>
</html>
