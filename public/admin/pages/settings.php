<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>系统设置 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-sidebar { height: 100vh; position: fixed; left: 0; top: 0; width: 260px; background: #0f172a; color: white; }
        .admin-main { margin-left: 260px; min-height: 100vh; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="admin-sidebar p-6 flex flex-col">
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black italic">28</div>
            <h1 class="text-xl font-black">PC28 <span class="text-indigo-400">PRO</span></h1>
        </div>
        <nav class="flex-1 space-y-2">
            <a href="/admin/index.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-home w-5 text-slate-400"></i> 控制台大盘</a>
            <a href="/admin/pages/settings.php" class="bg-indigo-600 flex items-center gap-3 px-4 py-3 rounded-xl font-bold"><i class="fas fa-cog w-5"></i> 系统全局配置</a>
            <a href="/admin/pages/users.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-users w-5 text-slate-400"></i> 会员管理</a>
            <a href="/admin/pages/chat.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-headset w-5 text-slate-400"></i> 客服与机器人</a>
            <a href="/admin/pages/finance_list.php" class="flex items-center gap-3 hover:bg-white/5 px-4 py-3 rounded-xl transition-colors"><i class="fas fa-wallet w-5 text-slate-400"></i> 财务充提审批</a>
        </nav>
    </div>

    <div class="admin-main p-10">
        <header class="mb-10">
            <h2 class="text-2xl font-black text-slate-900">系统全局配置</h2>
            <p class="text-slate-400 text-sm">管理公告、抽奖间隔、聊天禁言等核心参数</p>
        </header>

        <div class="grid grid-cols-2 gap-8">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 border-b pb-4">基础配置</h3>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">首页滚动公告</label>
                    <textarea id="announcement" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 outline-none focus:border-indigo-500 font-bold text-xs h-24"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">邀请链接前缀</label>
                    <input type="text" id="agent-link" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 outline-none focus:border-indigo-500 font-bold text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">自定义开奖间隔 (秒)</label>
                    <input type="number" id="draw-interval" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 outline-none focus:border-indigo-500 font-bold text-xs">
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">
                <h3 class="font-black text-slate-800 border-b pb-4">社交与聊天室控制</h3>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-xs font-black text-slate-800">全体禁言 (红包群)</p>
                        <p class="text-[10px] text-slate-400 font-bold">开启后仅管理员和机器人可发言</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="mute-all" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-xs font-black text-slate-800">机器人自动回复</p>
                        <p class="text-[10px] text-slate-400 font-bold">开启后机器人将根据关键词自动响应</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="bot-reply" class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                <div class="pt-4 border-t">
                    <button onclick="clearChatHistory()" class="w-full py-4 bg-rose-50 text-rose-600 rounded-xl text-xs font-black hover:bg-rose-100 transition-colors">
                        <i class="fas fa-trash-alt mr-2"></i> 清除所有聊天历史记录
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <button onclick="saveSettings()" class="w-full bg-indigo-600 text-white py-5 rounded-2xl font-black shadow-lg shadow-indigo-100 active:scale-95 transition-all">保存所有系统设置</button>
        </div>
    </div>

    <script>
        async function loadSettings() {
            const res = await fetch('/api/system_info.php?action=get_settings').then(r => r.json());
            if(res.success) {
                document.getElementById('announcement').value = res.data.announcement || '';
                document.getElementById('agent-link').value = res.data.agent_link_prefix || '';
                document.getElementById('draw-interval').value = res.data.custom_draw_interval || 300;
                document.getElementById('mute-all').checked = res.data.chat_mute_all == '1';
                document.getElementById('bot-reply').checked = res.data.bot_auto_reply_enabled == '1';
            }
        }

        async function saveSettings() {
            const payload = {
                announcement: document.getElementById('announcement').value,
                agent_link_prefix: document.getElementById('agent-link').value,
                custom_draw_interval: document.getElementById('draw-interval').value,
                chat_mute_all: document.getElementById('mute-all').checked ? '1' : '0',
                bot_auto_reply_enabled: document.getElementById('bot-reply').checked ? '1' : '0'
            };
            const res = await fetch('/admin/api/update_settings.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            alert(res.message);
        }

        async function clearChatHistory() {
            if(!confirm('确定要清除所有聊天历史记录吗？此操作不可撤销。')) return;
            const res = await fetch('/admin/api/update_settings.php?action=clear_chat').then(r => r.json());
            alert(res.message);
        }

        loadSettings();
    </script>
</body>
</html>
