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
        AND m.parent_id IS NULL
        ORDER BY $order_by";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// === Добавление ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim(htmlspecialchars($_POST['message']));
    $city_id = (int)$_POST['city_id'];
    $company_id = (int)$_POST['company_id'];
    $office_id = (int)$_POST['office_id'];
    $type_id = (int)$_POST['type_id'];
    
    if ($message && $city_id && $company_id && $office_id && $type_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, city_id, company_id, office_id, type_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $message, $city_id, $company_id, $office_id, $type_id]);
        
        $query = http_build_query($_GET);
        header("Location: index.php?$query");
        exit;
    }
}

// === РЕНДЕР ===
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

    echo '<div class="message-item" data-id="'.$msg['id'].'" style="background:white; padding:20px; margin-bottom:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); margin-left:'.$indent.'px;">';
    echo '<div style="display:flex; gap:20px; align-items:flex-start;">';
    echo '<div class="vote-block" style="text-align:center; min-width:60px;">';
    echo '<i class="fas fa-caret-up upvote-btn" style="font-size:2.4em; color:'.$up_color.'; cursor:pointer;" data-id="'.$msg['id'].'"></i>';
    echo '<div class="vote-score" style="font-size:1.6em; font-weight:bold; margin:8px 0; color:'.$score_color.';">'.$score.'</div>';
    echo '<i class="fas fa-caret-down downvote-btn" style="font-size:2.4em; color:'.$down_color.'; cursor:pointer;" data-id="'.$msg['id'].'"></i>';
    echo '</div>';

    echo '<div style="flex:1;">';
    echo '<div style="margin-bottom:10px;">';
    echo '<strong>'.htmlspecialchars($msg['username']).'</strong> • ';
    echo '<span style="color:#7f8c8d;">'.date('d.m.Y H:i', strtotime($msg['created_at'])).'</span> '.$badge;
    echo '</div>';

    echo '<div class="message-text" data-id="'.$msg['id'].'" style="margin:15px 0; line-height:1.6; word-break:break-word;">'.nl2br(htmlspecialchars($msg['message'])).'</div>';

    echo '<div style="color:#7f8c8d; font-size:0.95em;">';
    if ($msg['city_name']) echo 'Город: '.htmlspecialchars($msg['city_name']).' | ';
    if ($msg['company_name']) echo 'Компания: '.htmlspecialchars($msg['company_name']).' | ';
    if ($msg['office_address']) echo 'Офис: '.htmlspecialchars($msg['office_address']).' | ';
    if ($msg['type_name']) echo 'Тип: '.htmlspecialchars($msg['type_name']);
    echo '</div>';

    if ($is_admin || $msg['user_id'] == $current_user_id) {
        echo '<div class="message-actions" style="margin-top:15px; font-size:1.5em;">';
        echo '<i class="fas fa-edit edit-btn" style="cursor:pointer; color:#f39c12; margin-right:20px;" data-id="'.$msg['id'].'"></i>';
        echo '<i class="fas fa-trash-alt delete-btn" style="cursor:pointer; color:#e74c3c;" data-id="'.$msg['id'].'"></i>';
        echo '</div>';
    }

    echo '</div></div></div>';

    // Ответы
    $reply_stmt = $pdo->prepare("SELECT m.*, u.username, c.name as city_name, comp.name as company_name, o.address as office_address, t.name as type_name 
                                 FROM messages m 
                                 JOIN users u ON m.user_id = u.id 
                                 LEFT JOIN cities c ON m.city_id = c.id 
                                 LEFT JOIN companies comp ON m.company_id = comp.id 
                                 LEFT JOIN offices o ON m.office_id = o.id 
                                 LEFT JOIN suggestion_types t ON m.type_id = t.id 
                                 WHERE m.parent_id = ? ORDER BY m.created_at ASC");
    $reply_stmt->execute([$msg['id']]);
    $replies = $reply_stmt->fetchAll();

    foreach ($replies as $reply) {
        renderMessage($reply, $current_user_id, $is_admin, $depth + 1);
    }
}
?>

<!-- ФОРМА -->
<div style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); margin-bottom:40px;">
    <h2 style="margin-bottom:25px; color:#2c3e50;">Новое предложение или жалоба</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom:20px;">
            <select name="city_id" id="city-select" required>
                <option value="">Выберите город</option>
                <?php
                $cities = $pdo->query("SELECT id, name FROM cities ORDER BY name")->fetchAll();
                foreach ($cities as $c) {
                    $sel = $c['id'] == $city_id ? 'selected' : '';
                    echo "<option value=\"{$c['id']}\" $sel>{$c['name']}</option>";
                }
                ?>
            </select>

            <select name="company_id" id="company-select" required>
                <option value="">Выберите компанию</option>
            </select>

            <select name="office_id" id="office-select" required>
                <option value="">Выберите офис</option>
            </select>

            <select name="type_id" required>
                <option value="">Тип сообщения</option>
                <?php
                $types = $pdo->query("SELECT id, name FROM suggestion_types ORDER BY id")->fetchAll();
                foreach ($types as $t) {
                    $sel = $t['id'] == $type_id ? 'selected' : '';
                    echo "<option value=\"{$t['id']}\" $sel>{$t['name']}</option>";
                }
                ?>
            </select>
        </div>

        <textarea name="message" placeholder="Текст..." required style="width:100%; min-height:140px; padding:15px; border:1px solid #ddd; border-radius:8px; font-size:16px; margin-bottom:20px;"></textarea>

        <button type="submit" style="padding:14px 32px; background:#3498db; color:white; border:none; border-radius:8px; font-size:18px; cursor:pointer;">Отправить</button>
    </form>
</div>

<div id="messages-list">
    <?php foreach ($messages as $msg): ?>
        <?php renderMessage($msg, $user_id, $is_admin); ?>
    <?php endforeach; ?>
</div>

<script>
// ДЕЛЕГИРОВАННЫЙ ОБРАБОТЧИК — РАБОТАЕТ НА ВСЕХ СООБЩЕНИЯ, ВКЛЮЧАЯ ОТВЕТЫ
document.addEventListener('click', function(e) {
    const target = e.target;

    // ГОЛОСОВАНИЕ
    if (target.classList.contains('upvote-btn') || target.classList.contains('downvote-btn')) {
        const isUp = target.classList.contains('upvote-btn');
        const id = target.dataset.id;
        const vote = isUp ? 'up' : 'down';

        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=vote&message_id=${id}&vote=${vote}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const block = target.closest('.vote-block');
                block.querySelector('.vote-score').textContent = data.score;

                // Сбрасываем цвета
                block.querySelector('.upvote-btn').style.color = '#95a5a6';
                block.querySelector('.downvote-btn').style.color = '#95a5a6';

                // Подсвечиваем текущий голос
                if (isUp) {
                    block.querySelector('.upvote-btn').style.color = '#16a085';
                } else {
                    block.querySelector('.downvote-btn').style.color = '#c0392b';
                }
            } else {
                alert('Ошибка голосования');
            }
        });
    }

    // УДАЛЕНИЕ
    if (target.classList.contains('delete-btn')) {
        if (!confirm('Удалить сообщение и все ответы?')) return;
        const id = target.dataset.id;

        fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка удаления');
            }
        });
    }

    // РЕДАКТИРОВАНИЕ
    if (target.classList.contains('edit-btn')) {
        const id = target.dataset.id;
        const textDiv = document.querySelector(`.message-text[data-id="${id}"]`);
        const currentText = textDiv.textContent.trim();
        const originalHTML = textDiv.innerHTML;

        const textarea = document.createElement('textarea');
        textarea.value = currentText;
        textarea.style.cssText = 'width:100%; min-height:120px; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px; margin:10px 0;';

        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Сохранить';
        saveBtn.style.cssText = 'padding:10px 20px; background:#27ae60; color:white; border:none; border-radius:6px;';

        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Отмена';
        cancelBtn.style.cssText = 'padding:10px 20px; background:#95a5a6; color:white; border:none; border-radius:6px; margin-left:10px;';

        cancelBtn.onclick = () => textDiv.innerHTML = originalHTML;

        saveBtn.onclick = () => {
            const newText = textarea.value.trim();
            if (!newText) return alert('Пустое сообщение');

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&id=${id}&message=${encodeURIComponent(newText)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    textDiv.innerHTML = data.message;
                }
            });
        };

        textDiv.innerHTML = '';
        textDiv.appendChild(textarea);
        textDiv.appendChild(document.createElement('br'));
        textDiv.appendChild(saveBtn);
        textDiv.appendChild(cancelBtn);
    }
});

// Зависимые селекты (работают идеально)
function loadCompanies() {
    const cityId = document.getElementById('city-select').value;
    const companySelect = document.getElementById('company-select');
    const officeSelect = document.getElementById('office-select');

    companySelect.innerHTML = '<option value="">Загрузка...</option>';
    officeSelect.innerHTML = '<option value="">Сначала выберите компанию</option>';

    if (!cityId) return;

    fetch(`api.php?action=get_companies&city_id=${cityId}`)
        .then(r => r.json())
        .then(data => {
            companySelect.innerHTML = '<option value="">Выберите компанию</option>';
            data.forEach(c => {
                companySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        });
}

function loadOffices() {
    const companyId = document.getElementById('company-select').value;
    const cityId = document.getElementById('city-select').value;
    const officeSelect = document.getElementById('office-select');

    officeSelect.innerHTML = '<option value="">Загрузка...</option>';

    if (!companyId || !cityId) return;

    fetch(`api.php?action=get_offices&company_id=${companyId}&city_id=${cityId}`)
        .then(r => r.json())
        .then(data => {
            officeSelect.innerHTML = '<option value="">Выберите офис</option>';
            data.forEach(o => {
                officeSelect.innerHTML += `<option value="${o.id}">${o.address}</option>`;
            });
        });
}

document.getElementById('city-select').addEventListener('change', loadCompanies);
document.getElementById('company-select').addEventListener('change', loadOffices);

// Автозагрузка при открытии страницы
window.addEventListener('load', () => {
    if (document.getElementById('city-select').value) loadCompanies();
    if (document.getElementById('company-select').value) loadOffices();
});
</script>

<?php require 'footer.php'; ?>