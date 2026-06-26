<?php require_once __DIR__ . '/../check_auth.php'; ?>
<?php $uid = (int)$_GET['uid']; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>对话会员 (UID: <?php echo $uid; ?>) - 东爷国际</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafc; height: 100vh; display: flex; flex-direction: column; overflow: hidden; font-family: sans-serif; }
        .chat-scroll { flex: 1; overflow-y: auto; padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; }
        .bubble { max-width: 75%; padding: 1rem 1.25rem; border-radius: 1.5rem; font-size: 14px; font-weight: 500; line-height: 1.6; }
        .bubble-admin { background: #6366f1; color: white; align-self: flex-end; border-bottom-right-radius: 4px; box-shadow: 0 10px 15px -3px rgba(99,102,241,0.2); }
        .bubble-user { background: white; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 4px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
        .chat-img { max-width: 300px; border-radius: 1rem; cursor: zoom-in; transition: transform 0.2s; }
        .chat-img:hover { transform: scale(1.02); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body>
    <header class="bg-white border-b border-slate-100 p-6 flex justify-between items-center shrink-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-lg shadow-sm">U</div>
            <div>
                <h1 class="text-sm font-black text-slate-800">正在与 UID <?php echo $uid; ?> 对话</h1>
                <p class="text-[9px] text-green-500 font-bold uppercase tracking-widest flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> 实时连接中</p>
            </div>
        </div>
        <button onclick="clearChat()" class="text-rose-500 text-[10px] font-black uppercase tracking-widest border border-rose-100 px-5 py-2.5 rounded-xl hover:bg-rose-50 transition-all">清除此人记录</button>
    </header>

    <main id="chat-box" class="chat-scroll no-scrollbar bg-slate-50/50"></main>

    <footer class="p-6 bg-white border-t border-slate-100 shrink-0 shadow-2xl">
        <div class="max-w-4xl mx-auto flex flex-col gap-4">
            <div id="preview-area" class="hidden relative inline-block self-start">
                <img id="img-preview" class="w-24 h-24 object-cover rounded-2xl border-2 border-indigo-100 shadow-xl">
                <button onclick="clearImg()" class="absolute -top-2 -right-2 bg-rose-500 text-white w-6 h-6 rounded-full text-xs shadow-md border-2 border-white flex items-center justify-center"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex gap-4">
                <button onclick="document.getElementById('file-input').click()" class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-100 hover:bg-slate-100 transition-colors shadow-sm"><i class="fas fa-image text-lg"></i></button>
                <input type="file" id="file-input" class="hidden" accept="image/*" onchange="handleImg(this)">
                <input type="text" id="msg-input" class="flex-1 bg-slate-50 border-none rounded-2xl px-6 outline-none font-bold text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/5 transition-all" placeholder="输入回复消息..." onkeydown="if(event.key==='Enter') send()">
                <button onclick="send()" class="bg-indigo-600 text-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-90"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </footer>

    <script>
        const uid = <?php echo $uid; ?>;
        let imgBase64 = '';

        async function load() {
            const res = await fetch(`/api/chat.php?action=get_all_admin`).then(r => r.json());
            if(res.success) {
                const msgs = res.data.filter(m => m.sender_id == uid || m.receiver_id == uid);
                const box = document.getElementById('chat-box');
                const wasAtBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 150;

                box.innerHTML = msgs.map(m => {
                    const isSystem = m.sender_id == 0;
                    const isImg = m.message.startsWith('data:image');
                    const content = isImg ? `<img src="${m.message}" class="chat-img" onclick="window.open(this.src)">` : m.message.replace(/\n/g, '<br>');
                    return `
                        <div class="bubble ${isSystem ? 'bubble-admin' : 'bubble-user'} animate-in fade-in slide-in-from-${isSystem?'right':'left'}-4 duration-300">
                            <p>${content}</p>
                            <p class="text-[8px] mt-2 opacity-40 uppercase font-black ${isSystem ? 'text-right' : ''}">${m.created_at.split(' ')[1]}</p>
                        </div>
                    `;
                }).join('');

                if(wasAtBottom) box.scrollTop = box.scrollHeight;
            }
        }

        async function send() {
            const input = document.getElementById('msg-input');
            const msg = imgBase64 || input.value.trim();
            const type = imgBase64 ? 'image' : 'text';
            if(!msg) return;

            const res = await fetch('/api/chat.php?action=send_admin', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: uid, message: msg, type: type })
            }).then(r => r.json());

            if(res.success) {
                input.value = '';
                clearImg();
                load();
            }
        }

        async function clearChat() {
            if(!confirm('确定永久清除与该会员的所有聊天记录吗？此操作无法恢复。')) return;
            await fetch(`/api/chat.php?action=clear_private&user_id=${uid}`);
            load();
        }

        function handleImg(input) {
            const file = input.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const max = 1000;
                    let w = img.width, h = img.height;
                    if(w > h) { if(w > max) { h *= max/w; w = max; } } else { if(h > max) { w *= max/h; h = max; } }
                    canvas.width = w; canvas.height = h;
                    ctx.drawImage(img, 0, 0, w, h);
                    imgBase64 = canvas.toDataURL('image/jpeg', 0.65);
                    document.getElementById('img-preview').src = imgBase64;
                    document.getElementById('preview-area').classList.remove('hidden');
                };
            };
            reader.readAsDataURL(file);
        }
        function clearImg() { imgBase64 = ''; document.getElementById('preview-area').classList.add('hidden'); }

        load();
        setInterval(load, 4000);
    </script>
</body>
</html>
