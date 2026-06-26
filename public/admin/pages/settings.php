<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统配置 - 东爷国际 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>系统全局配置</h1>
        <div class="w-6"></div>
    </header>

    <main class="space-y-4">
        <div class="card">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">基础运营参数</h3>
            <div class="space-y-6">
                <div class="input-group">
                    <label>首页滚动公告</label>
                    <textarea id="announcement" class="form-input h-24"></textarea>
                </div>
                <div class="input-group">
                    <label>登录弹窗公告 (支持 HTML)</label>
                    <textarea id="login_announcement" class="form-input h-32" placeholder="用户每次登录会话看到一次"></textarea>
                </div>
                <div class="input-group">
                    <label>邀请链接前缀</label>
                    <input type="text" id="agent-link" class="form-input">
                </div>
                <div class="input-group">
                    <label>开奖间隔 (秒)</label>
                    <input type="number" id="draw-interval" class="form-input">
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">交互状态控制</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <p class="text-xs font-black text-slate-800">全体禁言 (红包房)</p>
                        <p class="text-[9px] text-slate-400 font-bold">开启后仅管理员可发言</p>
                    </div>
                    <input type="checkbox" id="mute-all" class="w-10 h-6">
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                    <div>
                        <p class="text-xs font-black text-slate-800">机器人自动回复</p>
                        <p class="text-[9px] text-slate-400 font-bold">全局开关关键词响应</p>
                    </div>
                    <input type="checkbox" id="bot-reply" class="w-10 h-6">
                </div>
            </div>
        </div>

        <div class="px-4 space-y-3">
            <button onclick="saveSettings()" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-indigo-100">保存所有设置</button>
            <button onclick="clearChatHistory()" class="w-full py-4 bg-white border border-rose-100 text-rose-500 rounded-2xl font-black text-xs">清除所有聊天记录</button>
        </div>
    </main>

    <script>
        async function loadSettings() {
            const res = await fetch('/api/system_info.php?action=get_settings').then(r => r.json());
            if(res.success) {
                document.getElementById('announcement').value = res.data.announcement || '';
                document.getElementById('login_announcement').value = res.data.login_announcement || '';
                document.getElementById('agent-link').value = res.data.agent_link_prefix || '';
                document.getElementById('draw-interval').value = res.data.custom_draw_interval || 300;
                document.getElementById('mute-all').checked = res.data.chat_mute_all == '1';
                document.getElementById('bot-reply').checked = res.data.bot_auto_reply_enabled == '1';
            }
        }
        async function saveSettings() {
            const payload = {
                announcement: document.getElementById('announcement').value,
                login_announcement: document.getElementById('login_announcement').value,
                agent_link_prefix: document.getElementById('agent-link').value,
                custom_draw_interval: document.getElementById('draw-interval').value,
                chat_mute_all: document.getElementById('mute-all').checked ? '1' : '0',
                bot_auto_reply_enabled: document.getElementById('bot-reply').checked ? '1' : '0'
            };
            const res = await fetch('/admin/api/update_settings.php', { method: 'POST', body: JSON.stringify(payload) }).then(r => r.json());
            alert(res.message);
        }
        async function clearChatHistory() {
            if(!confirm('确定清除？')) return;
            const res = await fetch('/admin/api/update_settings.php?action=clear_chat').then(r => r.json());
            alert(res.message);
        }
        loadSettings();
    </script>
    <style>
        body { background: #f8fafc; padding: 20px; font-family: sans-serif; }
        .admin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
        .admin-header h1 { font-size: 18px; font-weight: 900; color: #1e293b; }
        .card { background: white; padding: 24px; border-radius: 24px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); }
        .input-group label { display: block; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
        .form-input { w-full: 100%; width: 100%; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px; font-size: 12px; font-weight: 600; outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: #6366f1; background: white; }
    </style>
</body>
</html>
