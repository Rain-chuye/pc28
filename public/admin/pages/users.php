<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会员管理 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>会员管理</h1>
        <button onclick="loadUsers()" class="text-indigo-600 text-xs font-black uppercase"><i class="fas fa-sync-alt"></i></button>
    </header>

    <main id="user-list-container" class="space-y-4 pt-2">
        <div class="p-10 text-center text-slate-300 font-bold text-xs">正在载入会员列表...</div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 z-[2000] bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div class="bg-white rounded-[2rem] p-8 w-full max-w-sm shadow-2xl">
            <h3 class="text-lg font-black text-slate-800 mb-6">编辑会员信息</h3>
            <div class="space-y-4">
                <div class="input-group">
                    <label>昵称</label>
                    <input type="text" id="edit-nick" class="form-input">
                </div>
                <div class="input-group">
                    <label>QQ号</label>
                    <input type="text" id="edit-qq" class="form-input">
                </div>
                <div class="input-group">
                    <label>当前余额</label>
                    <input type="number" id="edit-bal" class="form-input" step="0.01">
                </div>
                <div class="input-group">
                    <label>修改密码 (留空不改)</label>
                    <input type="text" id="edit-pass" class="form-input" placeholder="新密码">
                </div>
            </div>
            <div class="flex gap-4 mt-8">
                <button onclick="closeModal()" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-xl font-black text-xs">取消</button>
                <button id="saveBtn" onclick="saveUser()" class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-black text-xs shadow-lg shadow-indigo-100">保存修改</button>
            </div>
        </div>
    </div>

    <script>
        let currentUser = null;

        async function loadUsers() {
            const res = await fetch('/admin/api/users_list.php').then(r => r.json());
            const container = document.getElementById('user-list-container');
            if(res.success) {
                container.innerHTML = res.data.map(u => `
                    <div class="card p-6 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-black text-slate-800">${u.nickname || u.username}</p>
                            <p class="text-[9px] text-slate-400 font-bold uppercase mt-1">ID: ${u.id} | ${u.username}</p>
                            <p class="text-xs font-black text-indigo-600 mt-2">¥ ${parseFloat(u.balance).toLocaleString()}</p>
                        </div>
                        <button onclick='openEdit(${JSON.stringify(u)})' class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                    </div>
                `).join('');
            }
        }

        function openEdit(user) {
            currentUser = user;
            document.getElementById('edit-nick').value = user.nickname || '';
            document.getElementById('edit-qq').value = user.qq_number || '';
            document.getElementById('edit-bal').value = user.balance;
            document.getElementById('edit-pass').value = '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        async function saveUser() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.innerText = '保存中...';

            const payload = {
                user_id: currentUser.id,
                nickname: document.getElementById('edit-nick').value.trim(),
                qq: document.getElementById('edit-qq').value.trim(),
                balance: document.getElementById('edit-bal').value,
                password: document.getElementById('edit-pass').value.trim()
            };

            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());

            if(res.success) {
                alert('修改成功');
                closeModal();
                loadUsers();
            } else alert(res.message);

            btn.disabled = false; btn.innerText = '保存修改';
        }

        loadUsers();
    </script>
</body>
</html>
