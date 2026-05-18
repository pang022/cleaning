<?php
date_default_timezone_set('Asia/Bangkok'); // ── บังคับ timezone ไทย (UTC+7) ──

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── DB Setup ──────────────────────────────────────────────
$db = new SQLite3(__DIR__ . '/database.db');
$db->exec("PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;");
$db->exec("
  CREATE TABLE IF NOT EXISTS checkpoints (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    location   TEXT DEFAULT '',
    token      TEXT UNIQUE NOT NULL,
    active     INTEGER DEFAULT 1,
    created_at TEXT DEFAULT (datetime('now','localtime'))
  );
  CREATE TABLE IF NOT EXISTS checklist_items (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    checkpoint_id INTEGER NOT NULL,
    label         TEXT NOT NULL,
    sort_order    INTEGER DEFAULT 0,
    FOREIGN KEY(checkpoint_id) REFERENCES checkpoints(id) ON DELETE CASCADE
  );
  CREATE TABLE IF NOT EXISTS submissions (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    checkpoint_id INTEGER NOT NULL,
    worker_name   TEXT NOT NULL,
    worker_role   TEXT DEFAULT '',
    note          TEXT DEFAULT '',
    submitted_at  TEXT DEFAULT (datetime('now','localtime')),
    latitude      REAL DEFAULT NULL,
    longitude     REAL DEFAULT NULL,
    accuracy      REAL DEFAULT NULL,
    FOREIGN KEY(checkpoint_id) REFERENCES checkpoints(id)
  );
  CREATE TABLE IF NOT EXISTS submission_photos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    file_path     TEXT NOT NULL,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE
  );
  CREATE TABLE IF NOT EXISTS submission_items (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    item_id       INTEGER NOT NULL,
    checked       INTEGER DEFAULT 0,
    FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE
  );
");

// ── Migration: DB เก่า ────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS submission_photos (
  id INTEGER PRIMARY KEY AUTOINCREMENT, submission_id INTEGER NOT NULL, file_path TEXT NOT NULL,
  FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE
);");
// เพิ่มคอลัมน์ GPS ถ้ายังไม่มี
foreach (['latitude REAL','longitude REAL','accuracy REAL'] as $col) {
  @$db->exec("ALTER TABLE submissions ADD COLUMN $col DEFAULT NULL");
}
// ตาราง log ไฟล์
$db->exec("CREATE TABLE IF NOT EXISTS access_logs (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  logged_at  TEXT DEFAULT (datetime('now','localtime')),
  action     TEXT NOT NULL,
  ip         TEXT DEFAULT '',
  user_agent TEXT DEFAULT '',
  detail     TEXT DEFAULT ''
);");

// ── Logger helper ─────────────────────────────────────────
function writeLog($db, $action, $detail='') {
  $ip = SQLite3::escapeString($_SERVER['REMOTE_ADDR'] ?? '');
  $ua = SQLite3::escapeString(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200));
  $det = SQLite3::escapeString(substr($detail, 0, 500));
  $db->exec("INSERT INTO access_logs (action,ip,user_agent,detail) VALUES ('$action','$ip','$ua','$det')");
}

// ── Router ────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
  switch ($action) {

    /* ───── CHECKPOINTS ───── */
    case 'list_checkpoints':
      $rows = [];
      $res = $db->query("SELECT * FROM checkpoints ORDER BY id DESC");
      while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
        $r['active'] = (bool)$r['active'];
        // count items
        $r['item_count'] = (int)$db->querySingle("SELECT COUNT(*) FROM checklist_items WHERE checkpoint_id={$r['id']}");
        $rows[] = $r;
      }
      ok(['checkpoints' => $rows]);
      break;

    case 'get_checkpoint':
      $token = esc($_GET['token'] ?? '');
      $cp = $db->querySingle("SELECT * FROM checkpoints WHERE token='$token' AND active=1", true);
      if (!$cp) err(404, 'ไม่พบจุดทำความสะอาดนี้');
      $cp['active'] = (bool)$cp['active'];
      $items = [];
      $res = $db->query("SELECT * FROM checklist_items WHERE checkpoint_id={$cp['id']} ORDER BY sort_order,id");
      while ($r = $res->fetchArray(SQLITE3_ASSOC)) $items[] = $r;
      writeLog($db, 'get_checkpoint', "cp=".$cp['name']);
      ok(['checkpoint' => $cp, 'items' => $items]);
      break;

    case 'create_checkpoint':
      need('POST');
      $b = body();
      if (empty($b['name'])) err(400, 'กรุณาระบุชื่อจุด');
      $token = bin2hex(random_bytes(8));
      $db->exec("INSERT INTO checkpoints (name,location,token) VALUES ('".esc($b['name'])."','".esc($b['location']??'')."','$token')");
      $id = $db->lastInsertRowID();
      foreach (($b['items'] ?? []) as $i => $label) {
        if (trim($label) === '') continue;
        $db->exec("INSERT INTO checklist_items (checkpoint_id,label,sort_order) VALUES ($id,'".esc($label)."',$i)");
      }
      $cp = $db->querySingle("SELECT * FROM checkpoints WHERE id=$id", true);
      ok(['checkpoint' => $cp]);
      break;

    case 'update_checkpoint':
      need('POST');
      $b = body(); $id = (int)($b['id'] ?? 0);
      if (!$id) err(400, 'id required');
      $db->exec("UPDATE checkpoints SET name='".esc($b['name']??'')."',location='".esc($b['location']??'')."' WHERE id=$id");
      if (isset($b['items']) && is_array($b['items'])) {
        $db->exec("DELETE FROM checklist_items WHERE checkpoint_id=$id");
        foreach ($b['items'] as $i => $label) {
          if (trim($label) === '') continue;
          $db->exec("INSERT INTO checklist_items (checkpoint_id,label,sort_order) VALUES ($id,'".esc($label)."',$i)");
        }
      }
      ok(['ok' => true]);
      break;

    case 'delete_checkpoint':
      need('POST');
      $b = body(); $id = (int)($b['id'] ?? 0);
      $db->exec("DELETE FROM checkpoints WHERE id=$id");
      ok(['ok' => true]);
      break;

    case 'toggle_checkpoint':
      need('POST');
      $b = body(); $id = (int)($b['id'] ?? 0);
      $db->exec("UPDATE checkpoints SET active = CASE WHEN active=1 THEN 0 ELSE 1 END WHERE id=$id");
      ok(['ok' => true]);
      break;

    /* ───── UPLOAD PHOTO ───── */
    case 'upload_photo':
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') err(405, 'Need POST');
      if (empty($_FILES['photo'])) err(400, 'ไม่พบไฟล์');
      $file = $_FILES['photo'];
      $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
      if (!in_array($file['type'], $allowed)) err(400, 'ประเภทไฟล์ไม่รองรับ');
      if ($file['size'] > 10 * 1024 * 1024) err(400, 'ไฟล์ใหญ่เกิน 10MB');
      $dateDir = date('Y/m/d');
      $uploadDir = __DIR__ . '/uploads/' . $dateDir . '/';
      if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
      $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
      $filename = uniqid('photo_', true) . '.' . strtolower($ext);
      $dest = $uploadDir . $filename;
      if (!move_uploaded_file($file['tmp_name'], $dest)) err(500, 'อัปโหลดไม่สำเร็จ');
      ok(['path' => 'uploads/' . $dateDir . '/' . $filename]);
      break;

    /* ───── SUBMIT ───── */
    case 'submit':
      need('POST');
      $b = body();
      $cp_id = (int)($b['checkpoint_id'] ?? 0);
      if (!$cp_id || empty($b['worker_name'])) err(400, 'ข้อมูลไม่ครบ');
      $now = date('Y-m-d H:i:s');
      $lat  = isset($b['latitude'])  && is_numeric($b['latitude'])  ? (float)$b['latitude']  : 'NULL';
      $lng  = isset($b['longitude']) && is_numeric($b['longitude']) ? (float)$b['longitude'] : 'NULL';
      $acc  = isset($b['accuracy'])  && is_numeric($b['accuracy'])  ? (float)$b['accuracy']  : 'NULL';
      $db->exec("BEGIN");
      $db->exec("INSERT INTO submissions (checkpoint_id,worker_name,worker_role,note,submitted_at,latitude,longitude,accuracy)
        VALUES ($cp_id,'".esc($b['worker_name'])."','".esc($b['worker_role']??'')."','".esc($b['note']??'')."','$now',$lat,$lng,$acc)");
      $sid = $db->lastInsertRowID();
      if (!$sid) { $db->exec('ROLLBACK'); err(500, 'บันทึกไม่สำเร็จ'); }
      foreach (($b['items'] ?? []) as $item_id => $chk) {
        $iid = (int)$item_id; $c = $chk ? 1 : 0;
        $db->exec("INSERT INTO submission_items (submission_id,item_id,checked) VALUES ($sid,$iid,$c)");
      }
      foreach (($b['photos'] ?? []) as $path) {
        $p = esc(trim($path));
        if ($p) $db->exec("INSERT INTO submission_photos (submission_id,file_path) VALUES ($sid,'$p')");
      }
      $db->exec('COMMIT');
      $gpsInfo = ($lat !== 'NULL') ? "lat=$lat,lng=$lng,acc=$acc" : 'no-gps';
      writeLog($db, 'submit', "sid=$sid cp=$cp_id worker=".esc($b['worker_name'])." $gpsInfo");
      ok(['submission_id' => $sid]);
      break;

    /* ───── REPORTS ───── */
    case 'report_daily':
      $date = esc($_GET['date'] ?? date('Y-m-d'));
      $rows = [];
      $res = $db->query("
        SELECT s.id, s.worker_name, s.worker_role, s.note, s.submitted_at,
               c.name AS cp_name, c.location AS cp_location,
               (SELECT COUNT(*) FROM submission_items si WHERE si.submission_id=s.id AND si.checked=1) AS done_count,
               (SELECT COUNT(*) FROM submission_items si WHERE si.submission_id=s.id) AS total_count
        FROM submissions s JOIN checkpoints c ON c.id=s.checkpoint_id
        WHERE date(s.submitted_at)='$date'
        ORDER BY s.submitted_at DESC
      ");
      while ($r = $res->fetchArray(SQLITE3_ASSOC)) $rows[] = $r;
      ok(['date' => $date, 'submissions' => $rows]);
      break;

    case 'report_monthly':
      $month = esc($_GET['month'] ?? date('Y-m'));
      $daily = []; $by_cp = [];
      $res = $db->query("
        SELECT date(s.submitted_at) AS day,
               COUNT(DISTINCT s.id) AS submission_count,
               COUNT(DISTINCT s.checkpoint_id) AS checkpoint_count,
               SUM(CASE WHEN si.checked=1 THEN 1 ELSE 0 END) AS done_items,
               COUNT(si.id) AS total_items
        FROM submissions s LEFT JOIN submission_items si ON si.submission_id=s.id
        WHERE strftime('%Y-%m', s.submitted_at)='$month'
        GROUP BY day ORDER BY day
      ");
      while ($r = $res->fetchArray(SQLITE3_ASSOC)) $daily[] = $r;
      $res2 = $db->query("
        SELECT c.name, c.location, COUNT(DISTINCT s.id) AS submission_count
        FROM submissions s JOIN checkpoints c ON c.id=s.checkpoint_id
        WHERE strftime('%Y-%m', s.submitted_at)='$month'
        GROUP BY c.id ORDER BY submission_count DESC
      ");
      while ($r = $res2->fetchArray(SQLITE3_ASSOC)) $by_cp[] = $r;
      ok(['month' => $month, 'daily' => $daily, 'by_checkpoint' => $by_cp]);
      break;

    case 'report_detail':
      $sid = (int)($_GET['sid'] ?? 0);
      $s = $db->querySingle("SELECT s.*,c.name AS cp_name,c.location AS cp_location FROM submissions s JOIN checkpoints c ON c.id=s.checkpoint_id WHERE s.id=$sid", true);
      if (!$s) err(404, 'ไม่พบ');
      $items = [];
      $res = $db->query("SELECT si.checked, ci.label FROM submission_items si JOIN checklist_items ci ON ci.id=si.item_id WHERE si.submission_id=$sid ORDER BY ci.sort_order,ci.id");
      while ($r = $res->fetchArray(SQLITE3_ASSOC)) { $r['checked']=(bool)$r['checked']; $items[]=$r; }
      $photos = [];
      $res2 = $db->query("SELECT file_path FROM submission_photos WHERE submission_id=$sid ORDER BY id");
      while ($r = $res2->fetchArray(SQLITE3_ASSOC)) $photos[] = $r['file_path'];
      ok(['submission' => $s, 'items' => $items, 'photos' => $photos]);
      break;

    default: err(404, 'unknown action');
  }
} catch (Exception $e) { err(500, $e->getMessage()); }

function ok($d)  { echo json_encode(array_merge(['success'=>true],$d),JSON_UNESCAPED_UNICODE); exit; }
function err($c,$m){ http_response_code($c); echo json_encode(['success'=>false,'error'=>$m],JSON_UNESCAPED_UNICODE); exit; }
function body()  { return json_decode(file_get_contents('php://input'),true) ?? []; }
function esc($s) { return SQLite3::escapeString((string)$s); }
function need($m){ if ($_SERVER['REQUEST_METHOD']!==$m) err(405,"Need $m"); }
