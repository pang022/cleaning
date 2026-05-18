<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — จุดทำความสะอาด</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="nav">
  <div class="nav-brand"><div class="icon">📂</div><span>Smart Check</span></div>
  <a href="index.php" class="active">จุดทำความสะอาด</a>
  <a href="report.php">รายงาน</a>
</nav>

<div class="page-wide">
  <div class="page-header">
    <div>
      <div class="page-title">จุดทำความสะอาด</div>
      <div class="page-sub">จัดการ checkpoint และ QR Code สำหรับแต่ละจุด</div>
    </div>
    <button class="btn btn-primary" onclick="openCreate()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      เพิ่มจุด
    </button>
  </div>

  <div id="cp-list"><div class="loading">⏳ กำลังโหลด...</div></div>
</div>

<!-- Modal: สร้าง/แก้ไข checkpoint -->
<div class="overlay" id="modal-overlay" onclick="closeModalOutside(event)">
  <div class="modal">
    <h2 id="modal-title">เพิ่มจุดทำความสะอาด</h2>
    <input type="hidden" id="edit-id">
    <div class="field">
      <label>ชื่อจุด <span style="color:var(--red)">*</span></label>
      <input type="text" id="f-name" placeholder="เช่น ล็อบบี้ชั้น 1, ห้องน้ำชาย B">
    </div>
    <div class="field">
      <label>ตำแหน่ง / พื้นที่</label>
      <input type="text" id="f-location" placeholder="เช่น อาคาร A ชั้น 3">
    </div>
    <div class="field">
      <label>รายการ Checklist</label>
      <div class="item-list" id="item-list"></div>
      <button class="btn btn-outline btn-sm" onclick="addItemRow()">+ เพิ่มรายการ</button>
    </div>
    <div class="modal-actions">
      <button class="btn btn-outline" onclick="closeModal()">ยกเลิก</button>
      <button class="btn btn-primary" onclick="saveCheckpoint()">บันทึก</button>
    </div>
  </div>
</div>

<!-- Modal: QR Code -->
<div class="overlay" id="qr-overlay" onclick="closeQROutside(event)">
  <div class="modal" style="max-width:360px">
    <h2 id="qr-title">QR Code</h2>
    <div class="qr-wrap">
      <img id="qr-img" src="" alt="QR Code">
      <div class="qr-url" id="qr-url"></div>
    </div>
    <div class="modal-actions" style="justify-content:space-between">
      <a id="qr-download" class="btn btn-outline btn-sm" download="qrcode.png">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        ดาวน์โหลด
      </a>
      <button class="btn btn-primary btn-sm" onclick="closeQR()">ปิด</button>
    </div>
  </div>
</div>

<script>
const API = 'api.php';
let checkpoints = [];

// ── Helpers ──────────────────────────────────────────────
async function api(action, method='GET', body=null) {
  const opts = { method, headers: {'Content-Type':'application/json'} };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(`${API}?action=${action}`, opts);
  const d = await r.json();
  if (!d.success) throw new Error(d.error);
  return d;
}
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function baseUrl() { return window.location.origin + window.location.pathname.replace('index.php',''); }

// ── Load ─────────────────────────────────────────────────
async function load() {
  try {
    const d = await api('list_checkpoints');
    checkpoints = d.checkpoints;
    renderList();
  } catch(e) {
    document.getElementById('cp-list').innerHTML = `<div class="empty"><div>โหลดไม่ได้: ${e.message}</div></div>`;
  }
}

function renderList() {
  const el = document.getElementById('cp-list');
  if (!checkpoints.length) {
    el.innerHTML = `<div class="empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><div>ยังไม่มีจุดทำความสะอาด</div></div>`;
    return;
  }
  el.innerHTML = `
  <table class="tbl" style="background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);overflow:hidden">
    <thead><tr>
      <th>ชื่อจุด</th><th>ตำแหน่ง</th><th>รายการ</th><th>สถานะ</th><th>QR</th><th></th>
    </tr></thead>
    <tbody>
    ${checkpoints.map(cp => `
      <tr>
        <td class="font-semibold">${esc(cp.name)}</td>
        <td class="text-gray">${esc(cp.location||'—')}</td>
        <td><span class="badge badge-blue">${cp.item_count} รายการ</span></td>
        <td>${cp.active
          ? '<span class="badge badge-green">เปิดใช้งาน</span>'
          : '<span class="badge badge-gray">ปิดใช้งาน</span>'}</td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="showQR(${cp.id},'${esc(cp.name)}','${cp.token}')">
            📱 QR
          </button>
        </td>
        <td>
          <div class="flex gap-2">
            <button class="btn-icon" onclick="openEdit(${cp.id})" title="แก้ไข">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
            </button>
            <button class="btn-icon" onclick="toggleCP(${cp.id})" title="${cp.active?'ปิด':'เปิด'}">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/>${cp.active?'<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>':'<polyline points="20 6 9 17 4 12"/>'}</svg>
            </button>
            <button class="btn-icon del" onclick="deleteCP(${cp.id},'${esc(cp.name)}')" title="ลบ">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            </button>
          </div>
        </td>
      </tr>
    `).join('')}
    </tbody>
  </table>`;
}

// ── Modal Create/Edit ─────────────────────────────────────
const DEFAULT_ITEMS = ['ปัดฝุ่น/เช็ดพื้นผิว','กวาดและถูพื้น','เช็ดกระจก','ล้างห้องน้ำ','เก็บขยะ'];

function openCreate() {
  document.getElementById('modal-title').textContent = 'เพิ่มจุดทำความสะอาด';
  document.getElementById('edit-id').value = '';
  document.getElementById('f-name').value = '';
  document.getElementById('f-location').value = '';
  renderItemRows(DEFAULT_ITEMS);
  document.getElementById('modal-overlay').classList.add('show');
  document.getElementById('f-name').focus();
}

async function openEdit(id) {
  const cp = checkpoints.find(c => c.id === id);
  if (!cp) return;
  // load items from server
  const d = await api(`get_checkpoint&token=${cp.token}`).catch(()=>null);
  document.getElementById('modal-title').textContent = 'แก้ไขจุด: ' + cp.name;
  document.getElementById('edit-id').value = id;
  document.getElementById('f-name').value = cp.name;
  document.getElementById('f-location').value = cp.location || '';
  renderItemRows(d ? d.items.map(i=>i.label) : []);
  document.getElementById('modal-overlay').classList.add('show');
}

function renderItemRows(items) {
  const el = document.getElementById('item-list');
  el.innerHTML = '';
  (items.length ? items : ['']).forEach(label => addItemRow(label));
}

function addItemRow(val='') {
  const div = document.createElement('div');
  div.className = 'item-row';
  div.innerHTML = `
    <input type="text" placeholder="รายการทำความสะอาด" value="${esc(val)}">
    <button class="btn-icon del" onclick="this.parentElement.remove()" title="ลบ">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>`;
  document.getElementById('item-list').appendChild(div);
}

function closeModal() { document.getElementById('modal-overlay').classList.remove('show'); }
function closeModalOutside(e) { if (e.target.id==='modal-overlay') closeModal(); }

async function saveCheckpoint() {
  const name = document.getElementById('f-name').value.trim();
  if (!name) { document.getElementById('f-name').focus(); return; }
  const items = [...document.querySelectorAll('#item-list .item-row input')].map(i=>i.value.trim()).filter(Boolean);
  const id = document.getElementById('edit-id').value;
  try {
    if (id) {
      await api('update_checkpoint','POST',{ id:parseInt(id), name, location:document.getElementById('f-location').value.trim(), items });
    } else {
      await api('create_checkpoint','POST',{ name, location:document.getElementById('f-location').value.trim(), items });
    }
    closeModal();
    await load();
  } catch(e) { alert('บันทึกไม่สำเร็จ: '+e.message); }
}

// ── Toggle / Delete ───────────────────────────────────────
async function toggleCP(id) {
  try { await api('toggle_checkpoint','POST',{id}); await load(); }
  catch(e) { alert(e.message); }
}

async function deleteCP(id, name) {
  if (!confirm(`ลบจุด "${name}" ?\nข้อมูล submission ทั้งหมดจะถูกลบด้วย`)) return;
  try { await api('delete_checkpoint','POST',{id}); await load(); }
  catch(e) { alert(e.message); }
}

// ── QR Code ───────────────────────────────────────────────
function showQR(id, name, token) {
  const url = baseUrl() + `scan.php?token=${token}`;
  const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
  document.getElementById('qr-title').textContent = '📱 QR — ' + name;
  document.getElementById('qr-img').src = qrApiUrl;
  document.getElementById('qr-url').textContent = url;
  document.getElementById('qr-download').href = qrApiUrl;
  document.getElementById('qr-overlay').classList.add('show');
}
function closeQR() { document.getElementById('qr-overlay').classList.remove('show'); }
function closeQROutside(e) { if (e.target.id==='qr-overlay') closeQR(); }

// Keyboard
document.addEventListener('keydown', e => {
  if (e.key==='Escape') { closeModal(); closeQR(); }
  if (e.key==='Enter' && document.getElementById('modal-overlay').classList.contains('show')) saveCheckpoint();
});

load();
</script>
</body>
</html>
