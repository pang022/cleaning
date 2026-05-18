<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รายงาน — CleanCheck</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav">
  <div class="nav-brand"><div class="icon">🧹</div><span>CleanCheck</span></div>
  <a href="index.php">จุดทำความสะอาด</a>
  <a href="report.php" class="active">รายงาน</a>
</nav>

<div class="page-wide">
  <div class="page-header">
    <div>
      <div class="page-title">รายงาน</div>
      <div class="page-sub">สรุปผลการทำความสะอาดรายวันและรายเดือน</div>
    </div>
    <button class="btn btn-outline" onclick="printReport()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      พิมพ์
    </button>
  </div>

  <!-- Tab -->
  <div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1.5rem">
    <button id="tab-daily" class="tab-btn active" onclick="switchTab('daily')">รายวัน</button>
    <button id="tab-monthly" class="tab-btn" onclick="switchTab('monthly')">รายเดือน</button>
  </div>

  <!-- Daily -->
  <div id="pane-daily">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem">
      <input type="date" id="date-picker" style="padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-family:'Sarabun',sans-serif;font-size:14px;background:var(--bg)">
      <button class="btn btn-primary btn-sm" onclick="loadDaily()">โหลด</button>
    </div>
    <div id="daily-stats" class="stats-grid"></div>
    <div id="daily-table"></div>
  </div>

  <!-- Monthly -->
  <div id="pane-monthly" style="display:none">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.25rem">
      <input type="month" id="month-picker" style="padding:8px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-family:'Sarabun',sans-serif;font-size:14px;background:var(--bg)">
      <button class="btn btn-primary btn-sm" onclick="loadMonthly()">โหลด</button>
    </div>
    <div id="monthly-chart-wrap" class="card" style="margin-bottom:1rem;display:none">
      <div class="card-title" style="margin-bottom:1rem">จำนวน Submission ต่อวัน</div>
      <div class="chart-wrap"><div class="bar-chart" id="bar-chart"></div></div>
    </div>
    <div id="monthly-cp"></div>
    <div id="monthly-table" style="margin-top:1rem"></div>
  </div>
</div>

<!-- Detail Modal -->
<div class="overlay" id="detail-overlay" onclick="if(event.target.id==='detail-overlay')closeDetail()">
  <div class="modal" style="max-width:520px">
    <h2 id="detail-title">รายละเอียด</h2>
    <div id="detail-body"></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeDetail()">ปิด</button></div>
  </div>
</div>

<style>
.tab-btn { background:none;border:none;font-family:'Sarabun',sans-serif;font-size:15px;padding:10px 20px;cursor:pointer;color:var(--text2);border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s; }
.tab-btn.active { color:var(--green);border-bottom-color:var(--green);font-weight:600; }
</style>

<script>
const API = 'api.php';

// ── Init ─────────────────────────────────────────────────
document.getElementById('date-picker').value = today();
document.getElementById('month-picker').value = todayMonth();
loadDaily();

function today() { return new Date().toISOString().slice(0,10); }
function todayMonth() { return new Date().toISOString().slice(0,7); }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function thDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('th-TH',{year:'numeric',month:'long',day:'numeric'});
}
function thTime(d) {
  if (!d) return '—';
  return new Date(d).toLocaleTimeString('th-TH',{hour:'2-digit',minute:'2-digit'});
}

// ── Tab ───────────────────────────────────────────────────
function switchTab(tab) {
  document.getElementById('pane-daily').style.display   = tab==='daily'   ? '' : 'none';
  document.getElementById('pane-monthly').style.display = tab==='monthly' ? '' : 'none';
  document.getElementById('tab-daily').classList.toggle('active', tab==='daily');
  document.getElementById('tab-monthly').classList.toggle('active', tab==='monthly');
  if (tab==='monthly') loadMonthly();
}

// ── Daily ─────────────────────────────────────────────────
async function loadDaily() {
  const date = document.getElementById('date-picker').value || today();
  try {
    const d = await fetch(`${API}?action=report_daily&date=${date}`).then(r=>r.json());
    if (!d.success) throw new Error(d.error);
    renderDailyStats(d.submissions);
    renderDailyTable(d.submissions, date);
  } catch(e) { document.getElementById('daily-table').innerHTML = `<div class="empty"><div>${e.message}</div></div>`; }
}

function renderDailyStats(rows) {
  const total = rows.length;
  const doneAll = rows.filter(r => r.done_count === r.total_count && r.total_count > 0).length;
  const cps = new Set(rows.map(r=>r.cp_name)).size;
  document.getElementById('daily-stats').innerHTML = `
    <div class="stat-card"><div class="stat-val blue">${total}</div><div class="stat-lbl">รายการบันทึก</div></div>
    <div class="stat-card"><div class="stat-val green">${doneAll}</div><div class="stat-lbl">ครบทุกรายการ</div></div>
    <div class="stat-card"><div class="stat-val">${cps}</div><div class="stat-lbl">จุดที่บันทึก</div></div>`;
}

function renderDailyTable(rows, date) {
  const el = document.getElementById('daily-table');
  if (!rows.length) {
    el.innerHTML = `<div class="empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg><div>ไม่มีข้อมูลวันนี้ (${thDate(date)})</div></div>`;
    return;
  }
  el.innerHTML = `
    <div class="card" style="padding:0;overflow:hidden">
    <table class="tbl">
      <thead><tr><th>เวลา</th><th>จุด</th><th>ผู้ทำ</th><th>ตำแหน่ง</th><th>รายการ</th><th>หมายเหตุ</th><th></th></tr></thead>
      <tbody>
      ${rows.map(r => {
        const pct = r.total_count ? Math.round(r.done_count/r.total_count*100) : 0;
        const badge = pct===100 ? 'badge-green' : pct>0 ? 'badge-blue' : 'badge-gray';
        return `<tr>
          <td class="text-sm text-gray">${thTime(r.submitted_at)}</td>
          <td><div class="font-semibold">${esc(r.cp_name)}</div><div class="text-xs text-gray">${esc(r.cp_location||'')}</div></td>
          <td class="font-semibold">${esc(r.worker_name)}</td>
          <td class="text-gray text-sm">${esc(r.worker_role||'—')}</td>
          <td><span class="badge ${badge}">${r.done_count}/${r.total_count}</span></td>
          <td class="text-sm text-gray">${esc(r.note||'—')}</td>
          <td><button class="btn btn-outline btn-sm" onclick="openDetail(${r.id})">ดู</button></td>
        </tr>`;
      }).join('')}
      </tbody>
    </table>
    </div>`;
}

// ── Monthly ───────────────────────────────────────────────
async function loadMonthly() {
  const month = document.getElementById('month-picker').value || todayMonth();
  try {
    const d = await fetch(`${API}?action=report_monthly&month=${month}`).then(r=>r.json());
    if (!d.success) throw new Error(d.error);
    renderMonthlyChart(d.daily);
    renderMonthlyCPTable(d.by_checkpoint);
    renderMonthlyDailyTable(d.daily, month);
  } catch(e) { document.getElementById('monthly-table').innerHTML = `<div class="empty"><div>${e.message}</div></div>`; }
}

function renderMonthlyChart(daily) {
  const wrap = document.getElementById('monthly-chart-wrap');
  if (!daily.length) { wrap.style.display='none'; return; }
  wrap.style.display='';
  const max = Math.max(...daily.map(d=>parseInt(d.submission_count)||0), 1);
  document.getElementById('bar-chart').innerHTML = daily.map(d => {
    const h = Math.max(Math.round((d.submission_count/max)*120), 2);
    const label = d.day ? d.day.slice(8) : '';
    return `<div class="bar-col">
      <div class="bar-val">${d.submission_count}</div>
      <div class="bar" style="height:${h}px" title="${d.day}: ${d.submission_count} รายการ"></div>
      <div class="bar-lbl">${label}</div>
    </div>`;
  }).join('');
}

function renderMonthlyCPTable(cps) {
  const el = document.getElementById('monthly-cp');
  if (!cps.length) { el.innerHTML=''; return; }
  const total = cps.reduce((s,c)=>s+parseInt(c.submission_count||0),0);
  el.innerHTML = `
    <div class="card">
      <div class="card-title" style="margin-bottom:1rem">สรุปตามจุดทำความสะอาด</div>
      <table class="tbl">
        <thead><tr><th>จุด</th><th>ตำแหน่ง</th><th>จำนวนครั้ง</th><th>สัดส่วน</th></tr></thead>
        <tbody>
        ${cps.map(c => {
          const pct = total ? Math.round(c.submission_count/total*100) : 0;
          return `<tr>
            <td class="font-semibold">${esc(c.name)}</td>
            <td class="text-gray text-sm">${esc(c.location||'—')}</td>
            <td><span class="badge badge-blue">${c.submission_count} ครั้ง</span></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:var(--border);border-radius:3px;overflow:hidden">
                  <div style="width:${pct}%;height:100%;background:var(--green);border-radius:3px"></div>
                </div>
                <span class="text-xs text-gray">${pct}%</span>
              </div>
            </td>
          </tr>`;
        }).join('')}
        </tbody>
      </table>
    </div>`;
}

function renderMonthlyDailyTable(daily, month) {
  const el = document.getElementById('monthly-table');
  if (!daily.length) {
    el.innerHTML = `<div class="empty"><div>ไม่มีข้อมูลเดือนนี้</div></div>`;
    return;
  }
  const totalSubs = daily.reduce((s,d)=>s+parseInt(d.submission_count||0),0);
  const totalDone = daily.reduce((s,d)=>s+parseInt(d.done_items||0),0);
  const totalItems = daily.reduce((s,d)=>s+parseInt(d.total_items||0),0);
  el.innerHTML = `
    <div class="stats-grid" style="margin-bottom:1rem">
      <div class="stat-card"><div class="stat-val blue">${totalSubs}</div><div class="stat-lbl">รายการบันทึกทั้งเดือน</div></div>
      <div class="stat-card"><div class="stat-val green">${totalDone}</div><div class="stat-lbl">รายการที่ทำสำเร็จ</div></div>
      <div class="stat-card"><div class="stat-val">${totalItems ? Math.round(totalDone/totalItems*100) : 0}%</div><div class="stat-lbl">อัตราความสำเร็จ</div></div>
    </div>
    <div class="card" style="padding:0;overflow:hidden">
    <table class="tbl">
      <thead><tr><th>วันที่</th><th>จำนวน Submission</th><th>จุดที่บันทึก</th><th>รายการสำเร็จ</th></tr></thead>
      <tbody>
      ${daily.map(d => {
        const pct = d.total_items ? Math.round(d.done_items/d.total_items*100) : 0;
        return `<tr>
          <td class="font-semibold">${thDate(d.day)}</td>
          <td><span class="badge badge-blue">${d.submission_count}</span></td>
          <td>${d.checkpoint_count} จุด</td>
          <td>
            <span class="badge ${pct===100?'badge-green':pct>0?'badge-blue':'badge-gray'}">${d.done_items}/${d.total_items} (${pct}%)</span>
          </td>
        </tr>`;
      }).join('')}
      </tbody>
    </table>
    </div>`;
}

// ── Detail ────────────────────────────────────────────────
async function openDetail(sid) {
  document.getElementById('detail-body').innerHTML = '<div class="loading">กำลังโหลด...</div>';
  document.getElementById('detail-overlay').classList.add('show');
  try {
    const d = await fetch(`${API}?action=report_detail&sid=${sid}`).then(r=>r.json());
    if (!d.success) throw new Error(d.error);
    const s = d.submission;
    const doneCount = d.items.filter(i=>i.checked).length;
    document.getElementById('detail-title').textContent = `📋 ${esc(s.cp_name)}`;
    document.getElementById('detail-body').innerHTML = `
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:1rem;font-size:14px">
        <div><span class="text-gray">ผู้ทำ:</span> <strong>${esc(s.worker_name)}</strong></div>
        <div><span class="text-gray">ตำแหน่ง:</span> ${esc(s.worker_role||'—')}</div>
        <div><span class="text-gray">จุด:</span> ${esc(s.cp_name)}</div>
        <div><span class="text-gray">เวลา:</span> ${thDate(s.submitted_at)} ${thTime(s.submitted_at)}</div>
        ${s.note ? `<div style="grid-column:1/-1"><span class="text-gray">หมายเหตุ:</span> ${esc(s.note)}</div>` : ''}
      </div>
      <div style="font-size:13px;font-weight:600;color:var(--text2);margin-bottom:8px">รายการ (${doneCount}/${d.items.length})</div>
      ${d.items.map(it => `
        <div class="check-item" style="cursor:default">
          <div class="check-box${it.checked?' checked':''}"></div>
          <span class="check-label${it.checked?' done':''}">${esc(it.label)}</span>
        </div>`).join('')}
      ${d.photos && d.photos.length ? `
        <div style="font-size:13px;font-weight:600;color:var(--text2);margin-top:1rem;margin-bottom:8px">รูปภาพ (${d.photos.length})</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
          ${d.photos.map(p => `
            <a href="${esc(p)}" target="_blank">
              <img src="${esc(p)}" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border);cursor:pointer" onerror="this.style.display='none'">
            </a>`).join('')}
        </div>` : ''}`;
  } catch(e) {
    document.getElementById('detail-body').innerHTML = `<div style="color:var(--red)">${e.message}</div>`;
  }
}
function closeDetail() { document.getElementById('detail-overlay').classList.remove('show'); }

function printReport() { window.print(); }

document.addEventListener('keydown', e => { if (e.key==='Escape') closeDetail(); });
</script>
</body>
</html>
