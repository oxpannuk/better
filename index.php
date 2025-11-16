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

$sql = "SELECT m.*, u.username, c.name as city_name, comp.name as company_name, o.name as office_name, o.address as office_address, t.name as type_name 
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

// === Рендер сообщения ===
function renderMessage($msg, $current_user_id, $is_admin, $depth = 0) {
    global $pdo;
    $indent = $depth * 40;
    $max_depth = 3;
    $can_reply = $depth < $max_depth;

    $actions = ($is_admin || $msg['user_id'] == $current_user_id) ? "
        <div class='actions' style='margin-top:8px; font-size:1.2em; opacity:0.7;'>
            <span class='edit-btn' style='cursor:pointer; color:#f39c12; margin-right:12px;' data-id='{$msg['id']}' title='Редактировать'>Edit</span>
            <span class='delete-btn' style='cursor:pointer; color:#e74c3c;' data-id='{$msg['id']}' title='Удалить'>Delete</span>
        </div>
    " : '';

    $vote_stmt = $pdo->prepare("SELECT vote FROM message_votes WHERE message_id = ? AND user_id = ?");
    $vote_stmt->execute([$msg['id'], $current_user_id]);
    $user_vote = $vote_stmt->fetchColumn();

    $up_color = $user_vote === 1 ? '#16a085' : '#95a5a6';
    $down_color = $user_vote === -1 ? '#c0392b' : '#95a5a6';

    $score = $msg['upvotes'] - $msg['downvotes'];
    $score_color = $score > 0 ? '#16a085' : ($score < 0 ? '#c0392b' : '#95a5a6');

    $badge = $msg['type_name'] === 'Предложение' 
        ? '<span style="background:#27ae60; color:white; padding:2px 8px; border-radius:12px; font-size:0.8em; margin-left:8px;">Предложение</span>'
        : '<span style="background:#e74c3c; color:white; padding:2px 8px; border-radius:12px; font-size:0.8em; margin-left:8px;">Жалоба</span>';

    $office_display = $msg['office_name'] ? htmlspecialchars($msg['office_name']) . ' — ' . htmlspecialchars($msg['office_address']) : '—';

    $vote_block = "
        <div class='vote-block' data-id='{$msg['id']}' style='display:flex; flex-direction:column; align-items:center; margin-right:16px; font-size:1.3em; font-weight:bold;'>
            <span class='upvote-btn' style='cursor:pointer; color:$up_color;' title='Upvote'>+</span>
            <span class='vote-score' style='color:$score_color; margin:2px 0;'>$score</span>
            <span class='downvote-btn' style='cursor:pointer; color:$down_color;' title='Downvote'>−</span>
        </div>
    ";

    $reply_btn = $can_reply ? "<span class='reply-btn' style='cursor:pointer; color:#3498db; font-size:0.9em; margin-left:10px;'>Ответить</span>" : '';

    return "
        <div class='message-item' data-id='{$msg['id']}' style='display:flex; background:#fff; padding:15px; margin:12px 0; margin-left:{$indent}px; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.08);'>
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
                <div style='margin-top:8px; font-size:0.9em; color:#7f8c8d;'>$reply_btn</div>
                $actions
            </div>
        </div>
    ";
}

$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();
?>

<h1>Что улучшить в офисе?</h1>

<!-- ФИЛЬТРЫ -->
<div style="background:#f8f9fa; padding:20px; border-radius:12px; margin-bottom:25px;">
    <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:end;">
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; color:#2c3e50;">Город</label>
            <select id="city-filter" onchange="loadFilterCompanies(this.value); updateFilters();" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; width:180px;">
                <option value="">Все города</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $city_id == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; color:#2c3e50;">Компания</label>
            <select id="company-filter" onchange="loadFilterOffices(this.value); updateFilters();" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; width:220px;">
                <option value="">Все компании</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; color:#2c3e50;">Офис</label>
            <select id="office-filter" onchange="updateFilters();" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; width:280px;">
                <option value="">Все офисы</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600; color:#2c3e50;">Тип</label>
            <select id="type-filter" onchange="updateFilters();" style="padding:8px 12px; border:1px solid #ddd; border-radius:6px; width:140px;">
                <option value="">Всё</option>
                <option value="1" <?= $type_id == 1 ? 'selected' : '' ?>>Предложение</option>
                <option value="2" <?= $type_id == 2 ? 'selected' : '' ?>>Жалоба</option>
            </select>
        </div>
        <div>
            <a href="?sort=date" style="margin:0 4px; padding:8px 16px; background:<?=($sort==='date'?'#3498db':'#95a5a6')?>; color:white; text-decoration:none; border-radius:6px; font-size:0.9em;">По дате</a>
            <a href="?sort=votes" style="margin:0 4px; background:<?=($sort==='votes'?'#e67e22':'#95a5a6')?>; color:white; text-decoration:none; border-radius:6px; font-size:0.9em;">По голосам</a>
        </div>
    </div>
</div>

<!-- ФОРМА -->
<form method="POST" style="margin:25px 0; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2 8px rgba(0,0,0,0.05);">
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; margin-bottom:15px;">
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Город *</label>
            <select name="city_id" required onchange="loadCompanies(this.value)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">Выберите город</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:5px; font-weight:600;">Компания *</label>
            <select name="company_id" required onchange="loadOffices(this.value)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                <option value="">Сначала выберите город</option>
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
            <option value="">Выберите действие</option>
            <option value="1">Предложение</option>
            <option value="2">Жалоба</option>
        </select>
    </div>
    <textarea name="message" placeholder="Опишите подробно..." required style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; height:120px; font-size:16px; margin-bottom:10px;"></textarea>
    <button type="submit" style="background:#27ae60; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:15px;">Отправить</button>
</form>

<div id="messages-container">
    <?php
    foreach ($messages as $msg) {
        echo renderMessage($msg, $user_id, $is_admin);
        $replies = $pdo->prepare("SELECT m.*, u.username, c.name as city_name, comp.name as company_name, o.name as office_name, o.address as office_address, t.name as type_name 
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
    if (empty($messages)) {
        echo '<p style="text-align:center; color:#95a5a6; font-style:italic; padding:20px;">Нет предложений. Будьте первым!</p>';
    }
    ?>
</div>

<script>
// === Голосование ===
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('upvote-btn') || e.target.classList.contains('downvote-btn')) {
        const block = e.target.closest('.vote-block');
        const id = block.dataset.id;
        const vote = e.target.classList.contains('upvote-btn') ? 'up' : 'down';

        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=vote&message_id=' + id + '&vote=' + vote
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                block.querySelector('.vote-score').textContent = data.score;
                const up = block.querySelector('.upvote-btn');
                const down = block.querySelector('.downvote-btn');
                up.style.color = vote === 'up' ? '#16a085' : '#95a5a6';
                down.style.color = vote === 'down' ? '#c0392b' : '#95a5a6';
            }
        });
    }

    // === Удаление ===
    if (e.target.classList.contains('delete-btn')) {
        if (!confirm('Удалить сообщение?')) return;
        const id = e.target.dataset.id;
        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`.message-item[data-id="${id}"]`).remove();
            }
        });
    }

    // === Редактирование ===
    if (e.target.classList.contains('edit-btn')) {
        const id = e.target.dataset.id;
        const textDiv = document.querySelector(`.message-text[data-id="${id}"]`);
        const currentText = textDiv.textContent.trim();
        const textarea = document.createElement('textarea');
        textarea.value = currentText;
        textarea.style.width = '100%';
        textarea.style.height = '80px';
        textDiv.innerHTML = '';
        textDiv.appendChild(textarea);

        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Сохранить';
        saveBtn.style.marginTop = '5px';
        saveBtn.onclick = () => {
            const newText = textarea.value.trim();
            if (!newText) return;
            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update&id=' + id + '&message=' + encodeURIComponent(newText)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    textDiv.innerHTML = data.message;
                }
            });
        };
        textDiv.appendChild(saveBtn);
    }
});

// === Загрузка компаний/офисов ===
function loadCompanies(cityId) {
    const companySelect = document.querySelector('[name="company_id"]');
    const officeSelect = document.querySelector('[name="office_id"]');
    companySelect.innerHTML = '<option value="">Загрузка...</option>';
    officeSelect.innerHTML = '<option value="">Сначала выберите компанию</option>';
    officeSelect.disabled = true;

    if (!cityId) {
        companySelect.innerHTML = '<option value="">Сначала выберите город</option>';
        return;
    }

    fetch(`api.php?action=get_companies&city_id=${cityId}`)
        .then(r => r.json())
        .then(data => {
            companySelect.innerHTML = '<option value="">Выберите компанию</option>';
            data.forEach(c => {
                companySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        });
}

function loadOffices(companyId) {
    const officeSelect = document.querySelector('[name="office_id"]');
    officeSelect.innerHTML = '<option value="">Загрузка...</option>';
    officeSelect.disabled = true;

    if (!companyId) {
        officeSelect.innerHTML = '<option value="">Сначала выберите компанию</option>';
        return;
    }

    fetch(`api.php?action=get_offices&company_id=${companyId}`)
        .then(r => r.json())
        .then(data => {
            officeSelect.innerHTML = '<option value="">Выберите офис</option>';
            officeSelect.disabled = false;
            data.forEach(o => {
                officeSelect.innerHTML += `<option value="${o.id}">${o.name} — ${o.address}</option>`;
            });
        });
}

// === Фильтры ===
function loadFilterCompanies(cityId) {
    const companySelect = document.getElementById('company-filter');
    const officeSelect = document.getElementById('office-filter');
    companySelect.innerHTML = '<option value="">Загрузка...</option>';
    officeSelect.innerHTML = '<option value="">Все офисы</option>';

    if (!cityId) {
        companySelect.innerHTML = '<option value="">Все компании</option>';
        return;
    }

    fetch(`api.php?action=get_companies&city_id=${cityId}`)
        .then(r => r.json())
        .then(data => {
            companySelect.innerHTML = '<option value="">Все компании</option>';
            data.forEach(c => {
                companySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        });
}

function loadFilterOffices(companyId) {
    const officeSelect = document.getElementById('office-filter');
    officeSelect.innerHTML = '<option value="">Загрузка...</option>';

    if (!companyId) {
        officeSelect.innerHTML = '<option value="">Все офисы</option>';
        return;
    }

    fetch(`api.php?action=get_offices&company_id=${companyId}`)
        .then(r => r.json())
        .then(data => {
            officeSelect.innerHTML = '<option value="">Все офисы</option>';
            data.forEach(o => {
                officeSelect.innerHTML += `<option value="${o.id}">${o.name} — ${o.address}</option>`;
            });
        });
}

function updateFilters() {
    const params = new URLSearchParams();
    const city = document.getElementById('city-filter').value;
    const company = document.getElementById('company-filter').value;
    const office = document.getElementById('office-filter').value;
    const type = document.getElementById('type-filter').value;
    if (city) params.set('city', city);
    if (company) params.set('company', company);
    if (office) params.set('office', office);
    if (type) params.set('type', type);
    if ('<?= $sort ?>') params.set('sort', '<?= $sort ?>');
    location.href = '?' + params.toString();
}

// Инициализация фильтров
window.onload = function() {
    const city = '<?= $city_id ?>';
    const company = '<?= $company_id ?>';
    if (city) {
        loadFilterCompanies(city);
        setTimeout(() => {
            if (company) loadFilterOffices(company);
        }, 300);
    }
};
</script>

<?php require 'footer.php'; ?>