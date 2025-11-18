<?php
$page_title = "Предложения по улучшению";
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// === ФИЛЬТРЫ ===
$city_id = $_GET['city'] ?? '';
$company_id = $_GET['company'] ?? '';
$office_id = $_GET['office'] ?? '';
$type_id = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? 'votes';

$where = [];
$params = [];
if ($city_id) { $where[] = "m.city_id = ?"; $params[] = $city_id; }
if ($company_id) { $where[] = "m.company_id = ?"; $params[] = $company_id; }
if ($office_id) { $where[] = "m.office_id = ?"; $params[] = $office_id; }
if ($type_id) { $where[] = "m.type_id = ?"; $params[] = $type_id; }

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$order_by = $sort === 'date' ? 'm.created_at DESC' : '(m.upvotes - m.downvotes) DESC, m.created_at DESC';

$sql = "SELECT m.*, u.username, c.name as city_name, comp.name as company_name, o.address as office_address, t.name as type_name 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        LEFT JOIN cities c ON m.city_id = c.id 
        LEFT JOIN companies comp ON m.company_id = comp.id 
        LEFT JOIN offices o ON m.office_id = o.id 
        LEFT JOIN suggestion_types t ON m.type_id = t.id 
        $where_sql 
        ORDER BY $order_by";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// === Добавление предложения ===
if ($_POST['message'] && isset($_POST['city_id']) && isset($_POST['company_id']) && isset($_POST['office_id']) && isset($_POST['type_id'])) {
    $message = trim(htmlspecialchars($_POST['message']));
    $city_id = (int)$_POST['city_id'];
    $company_id = (int)$_POST['company_id'];
    $office_id = (int)$_POST['office_id'];
    $type_id = (int)$_POST['type_id'];
    
    if ($message && $city_id && $company_id && $office_id && $type_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, city_id, company_id, office_id, type_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $message, $city_id, $company_id, $office_id, $type_id]);
    }
    $query = http_build_query($_GET);
    header("Location: index.php?$query");
    exit;
}

// === РЕНДЕР СООБЩЕНИЯ  ===
function renderMessage($msg, $current_user_id, $is_admin, $depth = 0) {
    global $pdo;
    $indent = $depth * 40;

    $vote_stmt = $pdo->prepare("SELECT vote FROM message_votes WHERE message_id = ? AND user_id = ?");
    $vote_stmt->execute([$msg['id'], $current_user_id]);
    $user_vote = $vote_stmt->fetchColumn() ?: 0;

    $up_color = $user_vote === 1 ? '#16a085' : '#95a5a6';
    $down_color = $user_vote === -1 ? '#c0392b' : '#95a5a6';
    $score = $msg['upvotes'] - $msg['downvotes'];
    $score_color = $score > 0 ? '#16a085' : ($score < 0 ? '#c0392b' : '#95a5a6');

    $badge = $msg['type_name'] === 'Предложение' 
        ? '<span style="background:#27ae60; color:white; padding:2px 8px; border-radius:12px; font-size:0.8em; margin-left:8px;">Предложение</span>'
        : '<span style="background:#e74c3c; color:white; padding:2px 8px; border-radius:12px; font-size:0.8em; margin-left:8px;">Жалоба</span>';

    $office_display = $msg['office_address'] ? htmlspecialchars($msg['office_address']) : '—';

    // КНОПКИ EDIT И DELETE
    $actions = ($is_admin || $msg['user_id'] == $current_user_id) ? "
        <div style='margin-top:10px; font-size:0.9em;'>
            <span class='edit-btn' style='cursor:pointer; color:#f39c12; margin-right:15px;' data-id='{$msg['id']}'>Редактировать</span>
            <span class='delete-btn' style='cursor:pointer; color:#e74c3c;' data-id='{$msg['id']}'>Удалить</span>
        </div>
    " : '';

    $vote_block = "
        <div class='vote-block' data-id='{$msg['id']}' style='display:flex; flex-direction:column; align-items:center; margin-right:16px; font-size:1.3em; font-weight:bold;'>
            <span class='upvote-btn' style='cursor:pointer; color:$up_color;'>+</span>
            <span class='vote-score' style='color:$score_color; margin:4px 0;'>$score</span>
            <span class='downvote-btn' style='cursor:pointer; color:$down_color;'>−</span>
        </div>
    ";

    return "
        <div class='message-item' data-id='{$msg['id']}' style='display:flex; background:#fff; padding:15px; margin:12px 0; margin-left:{$indent}px; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.08); border-left:4px solid #3498db;'>
            $vote_block
            <div style='flex:1;'>
                <div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;'>
                    <div>
                        <strong style='color:#3498db;'>".htmlspecialchars($msg['username'])."</strong>
                        <span style='color:#7f8c8d; font-size:0.85em; margin-left:8px;'>{$msg['city_name']} → {$msg['company_name']} → {$office_display}</span>
                        $badge
                    </div>
                    <span style='color:#7f8c8d; font-size:0.85em;'>".date('d.m H:i', strtotime($msg['created_at']))."</span>
                </div>
                <div class='message-text' data-id='{$msg['id']}'>".nl2br(htmlspecialchars($msg['message']))."</div>
                $actions <!-- ← КНОПКИ ЗДЕСЬ, ВНУТРИ СООБЩЕНИЯ → JS ВИДИТ! -->
            </div>
        </div>
    ";
}

$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();
?>

<h1 style="text-align:center; margin:40px 0 20px; color:#2c3e50;">Что улучшить в офисе?</h1>

<!-- ФОРМА ДОБАВЛЕНИЯ -->
<form method="POST" style="margin:25px 0; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Город *</label>
            <select name="city_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">Выберите город</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Компания *</label>
            <select name="company_id" required onchange="loadOffices(this.value)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">Загрузка компаний...</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Офис *</label>
            <select name="office_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">Сначала выберите компанию</option>
            </select>
        </div>
    </div>
    <div style="margin-bottom:15px;">
        <label style="display:block; margin-bottom:5px; font-weight:600;">Тип *</label>
        <select name="type_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
            <option value="">Выберите тип</option>
            <option value="1">Предложение</option>
            <option value="2">Жалоба</option>
        </select>
    </div>
    <textarea name="message" placeholder="Опишите подробно..." required style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; height:120px; font-size:16px; margin-bottom:10px;"></textarea>
    <button type="submit" style="background:#27ae60; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:15px;">Отправить</button>
</form>

<!-- ФИЛЬТРЫ И СООБЩЕНИЯ -->
<div id="messages-container">
    <?php
    foreach ($messages as $msg) {
        if (!$msg['parent_id']) {
            echo renderMessage($msg, $user_id, $is_admin);
            $replies = $pdo->prepare("SELECT m.*, u.username, c.name as city_name, comp.name as company_name, o.address as office_address, t.name as type_name 
                                      FROM messages m 
                                      JOIN users u ON m.user_id = u.id 
                                      LEFT JOIN cities c ON m.city_id = c.id 
                                      LEFT JOIN companies comp ON m.company_id = comp.id 
                                      LEFT JOIN offices o ON m.office_id = o.id 
                                      LEFT JOIN suggestion_types t ON m.type_id = t.id 
                                      WHERE parent_id = ? ORDER BY m.created_at ASC");
            $replies->execute([$msg['id']]);
            foreach ($replies->fetchAll() as $reply) {
                echo renderMessage($reply, $user_id, $is_admin, 1);
            }
        }
    }
    if (empty($messages)) {
        echo '<p style="text-align:center; color:#95a5a6; font-style:italic; padding:40px 20px;">Нет предложений. Будьте первым!</p>';
    }
    ?>
</div>

<script>
// Загрузка компаний при открытии страницы
fetch('api.php?action=get_companies')
    .then(r => r.json())
    .then(data => {
        const select = document.querySelector('[name="company_id"]');
        select.innerHTML = '<option value="">Выберите компанию</option>';
        data.forEach(c => {
            select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        });
    });

// Загрузка офисов
function loadOffices(companyId) {
    const officeSelect = document.querySelector('[name="office_id"]');
    officeSelect.innerHTML = '<option value="">Загрузка офисов...</option>';
    if (!companyId) return;

    fetch(`api.php?action=get_offices&company_id=${companyId}`)
        .then(r => r.json())
        .then(data => {
            officeSelect.innerHTML = '<option value="">Выберите офис</option>';
            data.forEach(o => {
                officeSelect.innerHTML += `<option value="${o.id}">${o.address}</option>`;
            });
        });
}

// Основной обработчик кликов — Edit, Delete, Vote
document.addEventListener('click', function(e) {

    // ГОЛОСОВАНИЕ
    if (e.target.classList.contains('upvote-btn') || e.target.classList.contains('downvote-btn')) {
        const block = e.target.closest('.vote-block');
        const id = block.dataset.id;
        const vote = e.target.classList.contains('upvote-btn') ? 'up' : 'down';

        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=vote&message_id=${id}&vote=${vote}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                block.querySelector('.vote-score').textContent = data.score;
                block.querySelector('.upvote-btn').style.color = vote === 'up' ? '#16a085' : '#95a5a6';
                block.querySelector('.downvote-btn').style.color = vote === 'down' ? '#c0392b' : '#95a5a6';
            }
        });
    }

    // УДАЛЕНИЕ
    if (e.target.classList.contains('delete-btn')) {
        if (!confirm('Удалить это сообщение и все ответы?')) return;
        const id = e.target.dataset.id;

        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Удаляем и родительское, и все ответы
                document.querySelectorAll(`.message-item[data-id="${id}"]`).forEach(el => el.remove());
                // Также удаляем ответы (у них parent_id = id)
                document.querySelectorAll(`.message-item`).forEach(el => {
                    if (el.innerHTML.includes(`data-id="${id}"`)) el.remove();
                });
            }
        });
    }

    // РЕДАКТИРОВАНИЕ
    if (e.target.classList.contains('edit-btn')) {
        const id = e.target.dataset.id;
        const textDiv = document.querySelector(`.message-text[data-id="${id}"]`);
        const currentText = textDiv.textContent.trim();

        const textarea = document.createElement('textarea');
        textarea.value = currentText;
        textarea.style.width = '100%';
        textarea.style.minHeight = '80px';
        textarea.style.padding = '8px';
        textarea.style.border = '1px solid #ddd';
        textarea.style.borderRadius = '6px';

        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Сохранить';
        saveBtn.style.marginTop = '8px';
        saveBtn.style.padding = '8px 16px';
        saveBtn.style.background = '#27ae60';
        saveBtn.style.color = 'white';
        saveBtn.style.border = 'none';
        saveBtn.style.borderRadius = '6px';
        saveBtn.style.cursor = 'pointer';

        saveBtn.onclick = function() {
            const newText = textarea.value.trim();
            if (!newText) return;

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&id=${id}&message=${encodeURIComponent(newText)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    textDiv.innerHTML = data.message; // nl2br уже в api.php
                } else {
                    alert('Ошибка сохранения');
                }
            });
        };

        textDiv.innerHTML = '';
        textDiv.appendChild(textarea);
        textDiv.appendChild(saveBtn);
    }
});
</script>

<?php require 'footer.php'; ?>