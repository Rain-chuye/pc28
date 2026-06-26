<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>客服私聊 - 东爷国际</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: sans-serif; }
        .user-card { background: white; border-radius: 1.25rem; border: 1px solid #f1f5f9; transition: all 0.2s; cursor: pointer; }
        .user-card:hover { border-color: #6366f1; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; }
    </style>
</head>
<body class="p-6 pb-24">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-xl font-black text-slate-800">客服私聊中心</h1>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Customer Service Center</p>
        </div>
        <div class="flex gap-3">
            <button onclick="clearAllChat()" class="bg-rose-50 text-rose-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">清空全部</button>
            <button onclick="loadUsers()" class="w-10 h-10 rounded-full bg-white flex items-center justify-center border border-slate-100 text-indigo-600 shadow-sm"><i class="fas fa-sync-alt"></i></button>
        </div>
    </header>

    <main id="user-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="col-span-full py-20 text-center text-slate-300 font-bold italic text-sm uppercase">Loading active conversations...</div>
    </main>

    <script>
        async function loadUsers() {
            const res = await fetch('/api/chat.php?action=get_all_admin').then(r => r.json());
            if(res.success) {
                const container = document.getElementById('user-list');
                const groups = {};
                res.data.forEach(m => {
                    const uid = m.sender_id == 0 ? m.receiver_id : m.sender_id;
                    if(!groups[uid]) groups[uid] = { msgs: [], last_time: '', name: '会员 ' + uid };
                    groups[uid].msgs.push(m);
                    groups[uid].last_time = m.created_at;
                    if (m.sender_name) groups[uid].name = m.sender_name;
                    groups[uid].last_msg = m.message;
                });

                const sorted = Object.entries(groups).sort((a, b) => new Date(b[1].last_time) - new Date(a[1].last_time));

                if (sorted.length === 0) {
                    container.innerHTML = '<div class="col-span-full py-20 text-center text-slate-300 font-bold italic text-sm uppercase tracking-widest">暂无咨询记录</div>';
                    return;
                }

                container.innerHTML = sorted.map(([uid, data]) => {
                    const unread = data.msgs.filter(m => m.sender_id != 0 && m.is_read == 0).length;
                    const isImg = data.last_msg.startsWith('data:image');
                    const preview = isImg ? '[图片消息]' : data.last_msg;

                    return `
                        <div class="user-card p-5 flex items-center gap-4 animate-in fade-in slide-in-from-bottom-2 duration-300" onclick="window.open('/admin/pages/chat_private.php?uid=${uid}', '_blank')">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 text-lg relative">
                                <i class="fas fa-user"></i>
                                ${unread > 0 ? `<span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[8px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-white">${unread}</span>` : ''}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-1">
                                    <h3 class="text-sm font-black text-slate-800 truncate">${data.name} (UID: ${uid})</h3>
                                    <span class="text-[8px] font-bold text-slate-400">${data.last_time.split(' ')[1]}</span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium truncate">${preview || '点击进入聊天'}</p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-100 text-xs"></i>
                        </div>
                    `;
                }).join('');
            }
        }

        async function clearAllChat() {
            if(!confirm('确定要清空所有会员的客服咨询记录吗？此操作不可撤销。')) return;
            const res = await fetch('/api/chat.php?action=clear_private&user_id=0').then(r => r.json());
            if(res.success) loadUsers();
        }

        loadUsers();
        setInterval(loadUsers, 8000);
    </script>
</body>
</html>
