<?php require_once __DIR__ . '/../check_auth.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>福利中心 - PC28 PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <h1>红包与福利中心</h1>
        <div class="w-6"></div>
    </header>

    <main class="space-y-4">
        <div class="card p-8">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8">手动发放全服红包</h3>
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="input-group mb-0">
                        <label>总金额 (¥)</label>
                        <input type="number" id="rp-amount" class="form-input" value="100">
                    </div>
                    <div class="input-group mb-0">
                        <label>红包个数</label>
                        <input type="number" id="rp-count" class="form-input" value="10">
                    </div>
                </div>
                <div class="input-group">
                    <label>最低流水要求 (¥)</label>
                    <input type="number" id="rp-turnover" class="form-input" value="0">
                </div>
                <div class="input-group">
                    <label>红包附言</label>
                    <input type="text" id="rp-msg" class="form-input" value="恭喜发财，大吉大利！">
                </div>
                <button onclick="sendRP()" class="w-full btn-indigo shadow-lg shadow-indigo-100">立即发放红包</button>
            </div>
        </div>

        <div class="px-4">
            <div class="p-6 bg-amber-50 rounded-[2rem] border border-amber-100">
                <p class="text-[9px] text-amber-600 font-bold leading-relaxed">
                    温馨提示：红包发放后将立即推送到所有在线用户的红包交流群中。请确保余额充足。
                </p>
            </div>
        </div>
    </main>

    <script>
        async function sendRP() {
            const payload = {
                amount: document.getElementById('rp-amount').value,
                count: document.getElementById('rp-count').value,
                turnover: document.getElementById('rp-turnover').value,
                message: document.getElementById('rp-msg').value
            };
            const res = await fetch('/admin/api/send_red_packet.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            }).then(r => r.json());
            if(res.success) alert('红包已发放！');
            else alert(res.message);
        }
    </script>
</body>
</html>
