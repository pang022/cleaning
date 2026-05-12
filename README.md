[index.html.html](https://github.com/user-attachments/files/27624499/index.html.html)
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ตารางทำความสะอาด</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #F7F6F2;
    --surface: #FFFFFF;
    --surface2: #F1EFE8;
    --border: #E2E0D6;
    --border2: #CCCAB8;
    --text: #1A1916;
    --text2: #6B6960;
    --text3: #9E9C93;
    --green: #1D9E75;
    --green-light: #E1F5EE;
    --green-dark: #0F6E56;
    --red: #E24B4A;
    --red-light: #FCEBEB;
    --blue-light: #E6F1FB;
    --blue: #185FA5;
    --amber-light: #FAEEDA;
    --amber: #854F0B;
    --radius: 10px;
    --radius-sm: 6px;
  }

  body {
    font-family: 'Sarabun', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    font-size: 16px;
  }

  .app {
    max-width: 680px;
    margin: 0 auto;
    padding: 2rem 1.25rem 4rem;
  }

  /* Header */
  .header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.75rem;
  }
  .header-title {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .header-icon {
    width: 40px; height: 40px;
    background: var(--green);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
  }
  .header h1 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text);
    line-height: 1.2;
  }
  .header p {
    font-size: 13px;
    color: var(--text2);
    margin-top: 2px;
  }
  .btn-add {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    background: var(--green);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-family: 'Sarabun', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
  }
  .btn-add:hover { background: var(--green-dark); }
  .btn-add svg { width: 16px; height: 16px; }

  /* Stats */
  .stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 1.25rem;
  }
  .stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 14px;
  }
  .stat-val { font-size: 22px; font-weight: 600; }
  .stat-lbl { font-size: 12px; color: var(--text2); margin-top: 2px; }
  .stat-val.green { color: var(--green); }
  .stat-val.red { color: var(--red); }

  /* Filters */
  .filter-row {
    display: flex;
    gap: 6px;
    margin-bottom: 1rem;
    flex-wrap: wrap;
  }
  .filter-btn {
    font-family: 'Sarabun', sans-serif;
    font-size: 13px;
    padding: 5px 12px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text2);
    cursor: pointer;
    transition: all 0.15s;
  }
  .filter-btn:hover { border-color: var(--border2); color: var(--text); }
  .filter-btn.active {
    background: var(--text);
    color: #fff;
    border-color: var(--text);
  }

  /* Task list */
  .task-list { display: flex; flex-direction: column; gap: 8px; }

  .task-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 13px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: box-shadow 0.15s;
  }
  .task-card:hover { box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
  .task-card.done { opacity: 0.5; }
  .task-card.overdue { border-color: #F09595; border-left: 3px solid var(--red); }

  .check-btn {
    width: 24px; height: 24px;
    border-radius: 50%;
    border: 1.5px solid var(--border2);
    background: none;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
  }
  .check-btn:hover { border-color: var(--green); }
  .check-btn.checked {
    background: var(--green);
    border-color: var(--green);
  }
  .check-btn.checked::after {
    content: '';
    display: block;
    width: 10px; height: 6px;
    border-left: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(-45deg) translateY(-1px);
  }

  .task-info { flex: 1; min-width: 0; }
  .task-title {
    font-size: 15px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .task-card.done .task-title { text-decoration: line-through; }

  .task-meta {
    display: flex;
    gap: 6px;
    margin-top: 4px;
    flex-wrap: wrap;
    align-items: center;
  }

  .tag {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
  }
  .tag-daily  { background: var(--green-light); color: var(--green-dark); }
  .tag-weekly { background: var(--blue-light); color: var(--blue); }
  .tag-monthly{ background: var(--amber-light); color: var(--amber); }
  .tag-once   { background: var(--surface2); color: var(--text2); }

  .meta-item {
    font-size: 12px;
    color: var(--text3);
    display: flex;
    align-items: center;
    gap: 3px;
  }
  .meta-item svg { width: 12px; height: 12px; }
  .meta-item.overdue { color: var(--red); }

  .task-actions { display: flex; gap: 2px; }
  .icon-btn {
    width: 32px; height: 32px;
    border-radius: var(--radius-sm);
    border: none;
    background: none;
    cursor: pointer;
    color: var(--text3);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
  }
  .icon-btn:hover { background: var(--surface2); color: var(--text); }
  .icon-btn.del:hover { color: var(--red); background: var(--red-light); }
  .icon-btn svg { width: 16px; height: 16px; }

  /* Empty */
  .empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--text3);
  }
  .empty svg { width: 40px; height: 40px; margin-bottom: 10px; opacity: 0.35; }

  /* Modal */
  .overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }
  .overlay.show { display: flex; }

  .modal {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 1.5rem;
    width: 100%;
    max-width: 420px;
    border: 1px solid var(--border);
  }
  .modal h2 {
    font-size: 17px;
    font-weight: 600;
    margin-bottom: 1.25rem;
  }
  .field { margin-bottom: 1rem; }
  .field label {
    display: block;
    font-size: 13px;
    color: var(--text2);
    margin-bottom: 5px;
    font-weight: 500;
  }
  .field input, .field select {
    width: 100%;
    padding: 9px 12px;
    font-family: 'Sarabun', sans-serif;
    font-size: 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    color: var(--text);
    outline: none;
    transition: border-color 0.15s;
  }
  .field input:focus, .field select:focus { border-color: var(--green); }

  .modal-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 1.25rem;
  }
  .btn-cancel {
    padding: 8px 16px;
    font-family: 'Sarabun', sans-serif;
    font-size: 14px;
    cursor: pointer;
    border-radius: var(--radius-sm);
    background: none;
    border: 1px solid var(--border);
    color: var(--text);
    transition: background 0.15s;
  }
  .btn-cancel:hover { background: var(--surface2); }
  .btn-save {
    padding: 8px 20px;
    font-family: 'Sarabun', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border-radius: var(--radius-sm);
    background: var(--green);
    border: none;
    color: #fff;
    transition: background 0.15s;
  }
  .btn-save:hover { background: var(--green-dark); }

  /* Notice bar */
  .notice {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text3);
  }
  .notice svg { width: 14px; height: 14px; }
  .sync-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--green);
    display: inline-block;
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  @media (max-width: 480px) {
    .header { flex-direction: column; gap: 12px; }
    .btn-add { align-self: stretch; justify-content: center; }
    .stats { grid-template-columns: repeat(3,1fr); }
  }
</style>
</head>
<body>
<div class="app">

  <div class="header">
    <div class="header-title">
      <div class="header-icon">🧹</div>
      <div>
        <h1>ตารางทำความสะอาด</h1>
        <p>จัดการร่วมกันได้ทุกคนในบ้าน</p>
      </div>
    </div>
    <button class="btn-add" onclick="openModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      เพิ่มงาน
    </button>
  </div>

  <div class="stats" id="stats"></div>
  <div class="filter-row" id="filters"></div>
  <div class="task-list" id="task-list"></div>

  <div class="notice">
    <span class="sync-dot"></span>
    ข้อมูลบันทึกใน localStorage — แชร์หน้าเดียวกันเพื่อใช้ร่วมกัน
  </div>
</div>

<!-- Modal -->
<div class="overlay" id="overlay" onclick="closeModalOutside(event)">
  <div class="modal">
    <h2 id="modal-title">เพิ่มงานทำความสะอาด</h2>
    <div class="field">
      <label>ชื่องาน</label>
      <input type="text" id="f-title" placeholder="เช่น กวาดพื้นห้องนั่งเล่น">
    </div>
    <div class="field">
      <label>ผู้รับผิดชอบ</label>
      <input type="text" id="f-assignee" placeholder="ชื่อคนหรือ ทุกคน">
    </div>
    <div class="field">
      <label>ความถี่</label>
      <select id="f-freq">
        <option value="daily">ทุกวัน</option>
        <option value="weekly">รายสัปดาห์</option>
        <option value="monthly">รายเดือน</option>
        <option value="once">ครั้งเดียว</option>
      </select>
    </div>
    <div class="field">
      <label>วันที่กำหนด</label>
      <input type="date" id="f-date">
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeModal()">ยกเลิก</button>
      <button class="btn-save" onclick="saveTask()">บันทึก</button>
    </div>
  </div>
</div>

<script>
  // ==================== STATE ====================
  let tasks = [];
  let filter = 'all';
  let editId = null;

  const FREQ_LABELS = { daily:'ทุกวัน', weekly:'รายสัปดาห์', monthly:'รายเดือน', once:'ครั้งเดียว' };
  const FREQ_TAGS   = { daily:'tag-daily', weekly:'tag-weekly', monthly:'tag-monthly', once:'tag-once' };
  const STORAGE_KEY = 'cleaning_schedule_v1';

  // ==================== STORAGE ====================
  function loadData() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      tasks = raw ? JSON.parse(raw) : defaultTasks();
    } catch(e) {
      tasks = defaultTasks();
    }
  }

  function saveData() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
  }

  function defaultTasks() {
    const fmt = d => d.toISOString().slice(0,10);
    const add = n => { let d = new Date(); d.setDate(d.getDate()+n); return fmt(d); };
    return [
      { id:'1', title:'กวาดและถูพื้นทั้งบ้าน', assignee:'ทุกคน',  freq:'daily',   date: add(0),  done:false },
      { id:'2', title:'เช็ดกระจกหน้าต่าง',       assignee:'แม่',    freq:'weekly',  date: add(2),  done:false },
      { id:'3', title:'ล้างห้องน้ำ',              assignee:'ทุกคน',  freq:'weekly',  date: add(-1), done:false },
      { id:'4', title:'ซักผ้าม่าน',               assignee:'พ่อ',    freq:'monthly', date: add(5),  done:true  },
      { id:'5', title:'ทำความสะอาดตู้เย็น',       assignee:'แม่',    freq:'monthly', date: add(10), done:false },
    ];
  }

  // ==================== HELPERS ====================
  function today() { return new Date().toISOString().slice(0,10); }

  function isOverdue(t) {
    return !t.done && t.date && t.date < today();
  }

  function formatDate(d) {
    if (!d) return '';
    if (d === today()) return 'วันนี้';
    const dt = new Date(d);
    return dt.toLocaleDateString('th-TH', { day:'numeric', month:'short' });
  }

  // SVG icons
  const icons = {
    calendar: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
    user:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>`,
    edit:     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
    trash:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>`,
    users:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17" cy="8" r="3"/><path d="M22 20c0-3.3-2.7-5.5-6-6"/></svg>`,
    happy:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>`,
  };

  // ==================== RENDER ====================
  function render() {
    renderStats();
    renderFilters();
    renderList();
  }

  function renderStats() {
    const total   = tasks.length;
    const done    = tasks.filter(t => t.done).length;
    const overdue = tasks.filter(t => isOverdue(t)).length;
    document.getElementById('stats').innerHTML = `
      <div class="stat"><div class="stat-val">${total}</div><div class="stat-lbl">งานทั้งหมด</div></div>
      <div class="stat"><div class="stat-val green">${done}</div><div class="stat-lbl">เสร็จแล้ว</div></div>
      <div class="stat"><div class="stat-val red">${overdue}</div><div class="stat-lbl">เกินกำหนด</div></div>
    `;
  }

  function renderFilters() {
    const items = [
      { key:'all',     label:'ทั้งหมด' },
      { key:'pending', label:'ยังไม่เสร็จ' },
      { key:'done',    label:'เสร็จแล้ว' },
      { key:'overdue', label:'เกินกำหนด' },
      { key:'daily',   label:'ทุกวัน' },
      { key:'weekly',  label:'รายสัปดาห์' },
      { key:'monthly', label:'รายเดือน' },
    ];
    document.getElementById('filters').innerHTML = items.map(f =>
      `<button class="filter-btn${filter===f.key?' active':''}" onclick="setFilter('${f.key}')">${f.label}</button>`
    ).join('');
  }

  function setFilter(f) { filter = f; render(); }

  function getFiltered() {
    return tasks.filter(t => {
      if(filter === 'all')     return true;
      if(filter === 'pending') return !t.done;
      if(filter === 'done')    return t.done;
      if(filter === 'overdue') return isOverdue(t);
      return t.freq === filter;
    });
  }

  function renderList() {
    const list = getFiltered();
    const el = document.getElementById('task-list');
    if (!list.length) {
      el.innerHTML = `<div class="empty">${icons.happy}<div>ไม่มีงานในหมวดนี้</div></div>`;
      return;
    }
    el.innerHTML = list.map(t => {
      const over = isOverdue(t);
      return `
      <div class="task-card${t.done?' done':''}${over?' overdue':''}">
        <button class="check-btn${t.done?' checked':''}" onclick="toggleDone('${t.id}')" title="${t.done?'ยกเลิก':'ทำเสร็จแล้ว'}"></button>
        <div class="task-info">
          <div class="task-title">${escHtml(t.title)}</div>
          <div class="task-meta">
            <span class="tag ${FREQ_TAGS[t.freq]}">${FREQ_LABELS[t.freq]}</span>
            ${t.date ? `<span class="meta-item${over?' overdue':''}">${icons.calendar} ${formatDate(t.date)}</span>` : ''}
            ${t.assignee ? `<span class="meta-item">${icons.user} ${escHtml(t.assignee)}</span>` : ''}
          </div>
        </div>
        <div class="task-actions">
          <button class="icon-btn" onclick="openModal('${t.id}')" title="แก้ไข">${icons.edit}</button>
          <button class="icon-btn del" onclick="deleteTask('${t.id}')" title="ลบ">${icons.trash}</button>
        </div>
      </div>`;
    }).join('');
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ==================== ACTIONS ====================
  function toggleDone(id) {
    const t = tasks.find(x => x.id === id);
    if (t) { t.done = !t.done; saveData(); render(); }
  }

  function deleteTask(id) {
    if (!confirm('ลบงานนี้?')) return;
    tasks = tasks.filter(x => x.id !== id);
    saveData(); render();
  }

  // ==================== MODAL ====================
  function openModal(id) {
    editId = id || null;
    const t = id ? tasks.find(x => x.id === id) : null;
    document.getElementById('modal-title').textContent = id ? 'แก้ไขงาน' : 'เพิ่มงานทำความสะอาด';
    document.getElementById('f-title').value    = t ? t.title    : '';
    document.getElementById('f-assignee').value = t ? t.assignee : '';
    document.getElementById('f-freq').value     = t ? t.freq     : 'weekly';
    document.getElementById('f-date').value     = t ? t.date     : today();
    document.getElementById('overlay').classList.add('show');
    document.getElementById('f-title').focus();
  }

  function closeModal() {
    document.getElementById('overlay').classList.remove('show');
    editId = null;
  }

  function closeModalOutside(e) {
    if (e.target === document.getElementById('overlay')) closeModal();
  }

  function saveTask() {
    const title = document.getElementById('f-title').value.trim();
    if (!title) { document.getElementById('f-title').focus(); return; }

    const task = {
      id:       editId || Date.now().toString(),
      title,
      assignee: document.getElementById('f-assignee').value.trim() || 'ทุกคน',
      freq:     document.getElementById('f-freq').value,
      date:     document.getElementById('f-date').value,
      done:     editId ? (tasks.find(x => x.id === editId) || {}).done || false : false
    };

    if (editId) {
      tasks = tasks.map(x => x.id === editId ? task : x);
    } else {
      tasks.push(task);
    }

    saveData();
    closeModal();
    render();
  }

  // Enter key in modal
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
    if (e.key === 'Enter' && document.getElementById('overlay').classList.contains('show')) saveTask();
  });

  // ==================== INIT ====================
  loadData();
  render();
</script>
</body>
</html>
