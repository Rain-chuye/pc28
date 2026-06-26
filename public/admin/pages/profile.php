<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员设置 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>管理员账户设置</h1>
        <div class="w-6"></div>
    </header>

    <main class="space-y-4">
        <div class="card">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">修改登录凭证</h3>
            <div class="space-y-6">
                <div class="input-group">
                    <label>新登录密码</label>
                    <input type="password" id="new-pass" class="form-input" placeholder="输入新密码">
                </div>
                <div class="input-group">
                    <label>确认新密码</label>
                    <input type="password" id="confirm-pass" class="form-input" placeholder="再次输入新密码">
                </div>
                <button onclick="updatePass()" class="w-full btn-indigo">确认修改密码</button>
            </div>
        </div>

        <div class="px-4">
            <button onclick="location.href='/api/logout.php'" class="w-full py-4 bg-white border border-slate-200 text-slate-400 rounded-2xl font-black text-xs">退出管理系统</button>
        </div>
    </main>

    <script>
        async function updatePass() {
            const p = document.getElementById('new-pass').value;
            const c = document.getElementById('confirm-pass').value;
            if(!p || p !== c) return alert('两次密码不一致');

            const res = await fetch('/admin/api/update_profile.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: 'admin', password: p })
            }).then(r => r.json());

            if(res.success) alert('修改成功，请重新登录');
            else alert(res.message);
        }
    </script>
</body>
</html>
