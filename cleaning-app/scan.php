<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SmartCheck — บันทึกการตรวจสอบ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
.photo-btn-row { display:flex; gap:8px; flex-wrap:wrap; }
.photo-btn-cam { background:var(--green);color:#fff;border:none; }
.photo-btn-cam:hover { background:var(--green-dark); }
.photo-btn-lib { background:none;border:1px solid var(--border);color:var(--text2); }
.photo-btn-lib:hover { border-color:var(--border2);color:var(--text); }
.gps-badge { display:inline-flex;align-items:center;gap:5px;font-size:12px;padding:4px 10px;border-radius:20px;margin-top:6px; }
.gps-ok   { background:var(--green-light);color:var(--green-dark); }
.gps-wait { background:var(--amber-light);color:var(--amber); }
.gps-fail { background:var(--red-light);color:var(--red); }
.photo-thumb { position:relative;width:80px;height:80px; }
.photo-thumb img { width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border); }
.photo-thumb .rm { position:absolute;top:2px;right:2px;width:20px;height:20px;background:rgba(0,0,0,.55);
  border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;color:#fff;border:none; }
</style>
</head>
<body style="background:var(--bg)">

<div id="app">
  <div class="loading" style="padding:4rem">⏳ กำลังโหลด...</div>
</div>

<script>
const API = 'api.php';
const token = new URLSearchParams(location.search).get('token') || '';
let checkpoint = null, items = [], checked = {};

async function init() {
  if (!token) { showError('ไม่พบ QR Token'); return; }
  try {
    const d = await fetch(`${API}?action=get_checkpoint&token=${encodeURIComponent(token)}`).then(r=>r.json());
    if (!d.success) { showError(d.error); return; }
    checkpoint = d.checkpoint;
    items = d.items;
    items.forEach(it => checked[it.id] = false);
    renderForm();
  } catch(e) { showError('ไม่สามารถโหลดข้อมูล: '+e.message); }
}

function showError(msg) {
  document.getElementById('app').innerHTML = `
    <div style="text-align:center;padding:4rem 2rem">
      <div style="font-size:48px;margin-bottom:1rem">⚠️</div>
      <div style="font-weight:600;font-size:18px;margin-bottom:.5rem">เกิดข้อผิดพลาด</div>
      <div style="color:var(--text2)">${msg}</div>
    </div>`;
}

function renderForm() {
  const doneCount = Object.values(checked).filter(Boolean).length;
  const pct = items.length ? Math.round(doneCount/items.length*100) : 0;

  document.getElementById('app').innerHTML = `
    <div class="scan-header">
      <h1>🧹 ${esc(checkpoint.name)}</h1>
      <p>${esc(checkpoint.location || 'กรุณากรอกข้อมูลให้ครบถ้วน')}</p>
    </div>
    <div class="scan-body">

      <!-- Progress -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
        <span class="text-sm text-gray">ความคืบหน้า</span>
        <span class="text-sm font-semibold" style="color:var(--green)">${doneCount}/${items.length} รายการ</span>
      </div>
      <div class="progress-bar-wrap">
        <div class="progress-bar" style="width:${pct}%"></div>
      </div>

      <!-- Worker info -->
      <div class="card mt-2">
        <div class="card-title" style="margin-bottom:1rem">ข้อมูลผู้ทำความสะอาด</div>
        <div class="field-row">
          <div class="field">
            <label>ชื่อ-นามสกุล <span style="color:var(--red)">*</span></label>
            <input type="text" id="f-name" placeholder="กรอกชื่อ" oninput="saveLocal()" value="${getLocal('name')}">
          </div>
          <div class="field">
            <label>ตำแหน่ง</label>
            <select id="f-role" onchange="saveLocal()" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-family:'Sarabun',sans-serif;font-size:15px;background:var(--bg);color:var(--text);">
              <option value="">-- เลือกตำแหน่ง --</option>
              ${['แม่บ้าน','ช่างไฟฟ้า/ประปา','พนักงานขับรถ','พนักงานด่านเก็บเงิน','พนักงานด่านชั่งน้ำหนัก','พนักงานรักษาความปลอดภัย']
                .map(r => `<option value="${r}"${getLocal('role')===r?' selected':''}>${r}</option>`).join('')}
            </select>
          </div>
        </div>
      </div>

      <!-- Checklist -->
      <div class="card mt-2">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
          <div class="card-title">รายการทำความสะอาด</div>
          <button class="btn btn-outline btn-sm" onclick="checkAll()">เลือกทั้งหมด</button>
        </div>
        ${items.length === 0
          ? '<div style="color:var(--text3);font-size:14px;text-align:center;padding:1rem">ยังไม่มีรายการ</div>'
          : items.map(it => `
          <div class="check-item" onclick="toggle(${it.id})" style="cursor:pointer">
            <div class="check-box${checked[it.id]?' checked':''}" id="cb-${it.id}"></div>
            <span class="check-label${checked[it.id]?' done':''}" id="cl-${it.id}">${esc(it.label)}</span>
          </div>`).join('')}
      </div>

      <!-- Photo Upload -->
      <div class="card mt-2">
        <div class="card-title" style="margin-bottom:.75rem">📷 แนบรูปภาพ (ถ้ามี)</div>
        <div id="photo-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px"></div>
        <div class="photo-btn-row">
          <label class="btn photo-btn-cam" style="cursor:pointer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
              <circle cx="12" cy="13" r="4"/>
            </svg>
            ถ่ายรูป
            <input type="file" accept="image/*" capture="environment" multiple style="display:none" onchange="handlePhotos(event)">
          </label>
          <label class="btn photo-btn-lib" style="cursor:pointer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
            เลือกจากอัลบั้ม
            <input type="file" accept="image/*" multiple style="display:none" onchange="handlePhotos(event)">
          </label>
        </div>
        <div id="photo-err" style="color:var(--red);font-size:12px;margin-top:6px"></div>
      </div>

      <!-- GPS -->
      <div class="card mt-2">
        <div class="card-title" style="margin-bottom:.5rem">📍 ตำแหน่ง GPS</div>
        <div id="gps-status"><span class="gps-badge gps-wait">⏳ กำลังระบุตำแหน่ง...</span></div>
      </div>

      <!-- Note -->
      <div class="card mt-2">
        <div class="field" style="margin:0">
          <label>หมายเหตุ (ถ้ามี)</label>
          <textarea id="f-note" placeholder="เช่น พื้นที่บางส่วนยังชำรุด, ขาดอุปกรณ์...">${getLocal('note')}</textarea>
        </div>
      </div>

      <!-- Submit -->
      <button class="btn btn-primary w-full mt-3" style="justify-content:center;padding:14px" onclick="submit()">
        ✅ บันทึกการทำความสะอาด
      </button>
      <div id="submit-err" style="color:var(--red);font-size:13px;text-align:center;margin-top:8px"></div>
    </div>`;

  // เริ่มดึง GPS หลัง render
  startGPS();
}

function toggle(id) {
  checked[id] = !checked[id];
  document.getElementById(`cb-${id}`).classList.toggle('checked', checked[id]);
  document.getElementById(`cl-${id}`).classList.toggle('done', checked[id]);
  // update progress
  const done = Object.values(checked).filter(Boolean).length;
  const pct = items.length ? Math.round(done/items.length*100) : 0;
  document.querySelector('.progress-bar').style.width = pct+'%';
  document.querySelector('.progress-bar-wrap + div span:last-child, .scan-body > div:first-of-type span:last-child').textContent;
  // recount label
  const lbl = document.querySelector('[style*="color:var(--green)"]');
  if (lbl) lbl.textContent = `${done}/${items.length} รายการ`;
}

function checkAll() {
  const allChecked = items.every(it => checked[it.id]);
  items.forEach(it => {
    checked[it.id] = !allChecked;
    document.getElementById(`cb-${it.id}`).classList.toggle('checked', !allChecked);
    document.getElementById(`cl-${it.id}`).classList.toggle('done', !allChecked);
  });
  const done = Object.values(checked).filter(Boolean).length;
  const pct = items.length ? Math.round(done/items.length*100) : 0;
  document.querySelector('.progress-bar').style.width = pct+'%';
  const lbl = document.querySelector('.progress-bar-wrap');
  if (lbl && lbl.previousElementSibling) {
    lbl.previousElementSibling.querySelector('span:last-child').textContent = `${done}/${items.length} รายการ`;
  }
}

async function submit() {
  const name = document.getElementById('f-name').value.trim();
  if (!name) {
    document.getElementById('submit-err').textContent = 'กรุณากรอกชื่อผู้ทำความสะอาด';
    document.getElementById('f-name').focus(); return;
  }
  const itemsPayload = {};
  items.forEach(it => itemsPayload[it.id] = checked[it.id] ? 1 : 0);
  try {
    const r = await fetch(`${API}?action=submit`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({
        checkpoint_id: checkpoint.id,
        worker_name: name,
        worker_role: document.getElementById('f-role').value.trim(),
        note: document.getElementById('f-note').value.trim(),
        items: itemsPayload,
        photos: uploadedPhotoPaths,
        latitude:  gpsData.latitude,
        longitude: gpsData.longitude,
        accuracy:  gpsData.accuracy
      })
    }).then(r=>r.json());
    if (!r.success) throw new Error(r.error);
    const done = Object.values(checked).filter(Boolean).length;
    showSuccess(done);
    clearLocal();
    uploadedPhotoPaths = [];
  } catch(e) {
    document.getElementById('submit-err').textContent = 'บันทึกไม่สำเร็จ: '+e.message;
  }
}

function showSuccess(done) {
  document.getElementById('app').innerHTML = `
    <div class="scan-header">
      <h1>🧹 ${esc(checkpoint.name)}</h1>
      <p>${esc(checkpoint.location||'')}</p>
    </div>
    <div class="scan-body">
      <div class="success-screen">
        <div class="success-icon">✅</div>
        <h2>บันทึกเสร็จแล้ว!</h2>
        <p>ทำความสะอาดครบ ${done}/${items.length} รายการ<br>ขอบคุณสำหรับการทำงาน</p>
        <div style="margin-top:2rem">
          <button class="btn btn-primary" onclick="resetForm()">บันทึกรอบใหม่</button>
        </div>
      </div>
    </div>`;
}

function resetForm() {
  items.forEach(it => checked[it.id] = false);
  renderForm();
}

// localStorage helpers
function saveLocal() {
  localStorage.setItem('worker_name', document.getElementById('f-name')?.value||'');
  localStorage.setItem('worker_role', document.getElementById('f-role')?.value||'');
}
function getLocal(k) { try { return localStorage.getItem('worker_'+k)||''; } catch(e) { return ''; } }
function clearLocal() { try { localStorage.removeItem('worker_note'); } catch(e) {} }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ── GPS ──────────────────────────────────────────────────
let gpsData = { latitude: null, longitude: null, accuracy: null };

function startGPS() {
  if (!navigator.geolocation) {
    setGPSStatus('fail', '❌ เบราว์เซอร์ไม่รองรับ GPS');
    return;
  }
  navigator.geolocation.getCurrentPosition(
    pos => {
      gpsData.latitude  = pos.coords.latitude;
      gpsData.longitude = pos.coords.longitude;
      gpsData.accuracy  = Math.round(pos.coords.accuracy);
      const mapsUrl = `https://www.google.com/maps?q=${gpsData.latitude},${gpsData.longitude}`;
      setGPSStatus('ok',
        `✅ ระบุตำแหน่งแล้ว (±${gpsData.accuracy}ม.) &nbsp;` +
        `<a href="${mapsUrl}" target="_blank" style="color:var(--green-dark);font-size:11px">ดูแผนที่</a>`
      );
    },
    err => {
      const msg = err.code === 1 ? 'ผู้ใช้ไม่อนุญาต GPS' : err.code === 2 ? 'ไม่พบสัญญาณ GPS' : 'หมดเวลา GPS';
      setGPSStatus('fail', `⚠️ ${msg}`);
    },
    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
  );
}

function setGPSStatus(type, html) {
  const el = document.getElementById('gps-status');
  if (!el) return;
  el.innerHTML = `<span class="gps-badge gps-${type}">${html}</span>`;
}

// ── Photo Upload ─────────────────────────────────────────
let uploadedPhotoPaths = [];

async function handlePhotos(event) {
  const files = Array.from(event.target.files);
  document.getElementById('photo-err').textContent = '';
  for (const file of files) {
    if (file.size > 10 * 1024 * 1024) {
      document.getElementById('photo-err').textContent = `ไฟล์ "${file.name}" ใหญ่เกิน 10MB`;
      continue;
    }
    const previewId = 'prev_' + Date.now() + '_' + Math.random().toString(36).slice(2);
    // แสดง preview ทันที
    const reader = new FileReader();
    reader.onload = ev => {
      const wrap = document.createElement('div');
      wrap.className = 'photo-thumb';
      wrap.id = previewId;
      wrap.innerHTML = `
        <img src="${ev.target.result}">
        <button class="rm" onclick="removePhoto('${previewId}',this.dataset.path)" data-path="">⏳</button>`;
      document.getElementById('photo-preview').appendChild(wrap);
    };
    reader.readAsDataURL(file);
    // อัปโหลดจริง
    try {
      const fd = new FormData(); fd.append('photo', file);
      const r = await fetch(`${API}?action=upload_photo`, { method:'POST', body:fd }).then(r=>r.json());
      if (!r.success) throw new Error(r.error);
      uploadedPhotoPaths.push(r.path);
      setTimeout(() => {
        const el = document.getElementById(previewId);
        if (el) { const btn = el.querySelector('.rm'); if (btn) { btn.dataset.path = r.path; btn.textContent = '✕'; } }
      }, 100);
    } catch(e) {
      document.getElementById('photo-err').textContent = 'อัปโหลดไม่สำเร็จ: ' + e.message;
      document.getElementById(previewId)?.remove();
    }
  }
  event.target.value = '';
}

function removePhoto(previewId, path) {
  document.getElementById(previewId)?.remove();
  uploadedPhotoPaths = uploadedPhotoPaths.filter(p => p !== path);
}

init();
</script>
</body>
</html>
