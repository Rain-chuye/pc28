<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
    <div class="flex justify-between items-center mb-8">
        <h3 class="font-black">赔率配置</h3>
        <button onclick="saveOdds()" class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-bold">保存修改</button>
    </div>
    <div class="grid grid-cols-2 gap-8" id="odds-container">
        <!-- Odds list here -->
    </div>
</div>

<script>
async function loadOdds() {
    const res = await fetch('/admin/api/odds_list.php');
    const data = await res.json();
    const container = document.getElementById('odds-container');
    container.innerHTML = data.data.map(o => `
        <div class="space-y-3 p-4 bg-slate-50 rounded-2xl">
            <p class="text-xs font-black uppercase text-slate-400">${o.play_type}</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] text-slate-400 font-bold block mb-1">低赔率</label>
                    <input type="number" step="0.001" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm font-bold" data-id="${o.id}" data-field="odds_low" value="${o.odds_low}">
                </div>
                <div>
                    <label class="text-[10px] text-slate-400 font-bold block mb-1">高赔率</label>
                    <input type="number" step="0.001" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm font-bold" data-id="${o.id}" data-field="odds_high" value="${o.odds_high}">
                </div>
            </div>
        </div>
    `).join('');
}

async function saveOdds() {
    const inputs = document.querySelectorAll('#odds-container input');
    const updates = {};
    inputs.forEach(i => {
        const id = i.getAttribute('data-id');
        if(!updates[id]) updates[id] = {id: id};
        updates[id][i.getAttribute('data-field')] = i.value;
    });

    const res = await fetch('/admin/api/odds_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({updates: Object.values(updates)})
    });
    const result = await res.json();
    if(result.success) alert('保存成功');
}
loadOdds();
</script>
