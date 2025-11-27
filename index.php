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

$where = ["m.parent_id IS NULL"]; // ВАЖНО: только корневые сообщения
$params = [];
if ($city_id) {
    $where[] = "m.city_id = ?";
    $params[] = $city_id;
}
if ($company_id) {
    $where[] = "m.company_id = ?";
    $params[] = $company_id;
}
if ($office_id) {
    $where[] = "m.office_id = ?";
    $params[] = $office_id;
}
if ($type_id) {
    $where[] = "m.type_id = ?";
    $params[] = $type_id;
}

$where_sql = 'WHERE ' . implode(' AND ', $where);
$order_by = $sort === 'date' ? 'm.created_at DESC' : '(m.upvotes - m.downvotes) DESC, m.created_at DESC';

// ЗАПРОС ТОЛЬКО КОРНЕВЫХ СООБЩЕНИЙ
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

// === Добавление КОРНЕВОГО сообщения ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim(htmlspecialchars($_POST['message']));
    $city_id = (int)$_POST['city_id'];
    $company_id = (int)$_POST['company_id'];
    $office_id = (int)$_POST['office_id'];
    $type_id = (int)$_POST['type_id'];

    if ($message && $city_id && $company_id && $office_id && $type_id) {
        // ВАЖНО: parent_id не указываем - это корневое сообщение
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message, city_id, company_id, office_id, type_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $message, $city_id, $company_id, $office_id, $type_id]);

        $query = http_build_query($_GET);
        header("Location: index.php?$query");
        exit;
    }
}

// === РЕНДЕР ===
function renderMessage($msg, $current_user_id, $is_admin, $depth = 0)
{
    global $pdo;
    $indent = $depth * 40;

    $vote_stmt = $pdo->prepare("SELECT vote FROM message_votes WHERE message_id = ? AND user_id = ?");
    $vote_stmt->execute([$msg['id'], $current_user_id]);
    $user_vote = $vote_stmt->fetchColumn() ?: 0;

    // Подсчёт количества ответов
    $replies_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE parent_id = ?");
    $replies_count_stmt->execute([$msg['id']]);
    $replies_count = $replies_count_stmt->fetchColumn();

    echo '
    <div class="message" id="msg-' . $msg['id'] . '" data-user-vote="' . $user_vote . '" style="margin-left: ' . $indent . 'px; border-left:3px solid #3498db; padding:20px; background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span style="font-weight:bold; color:#3498db;">' . htmlspecialchars($msg['username']) . '</span>
            <span style="color:#95a5a6; font-size:0.9em;">' . date('d.m.Y H:i', strtotime($msg['created_at'])) . '</span>
        </div>
        
        <div class="msg-text" data-id="' . $msg['id'] . '" style="margin-bottom:15px;">' . nl2br(htmlspecialchars($msg['message'])) . '</div>
        
        <div style="color:#7f8c8d; font-size:0.9em; margin-bottom:10px;">
            <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>' . htmlspecialchars($msg['city_name'] ?? 'Не указан') . ' &middot; 
            <i class="fas fa-building" style="margin-right:5px;"></i>' . htmlspecialchars($msg['company_name'] ?? 'Не указана') . ' &middot; 
            <i class="fas fa-home" style="margin-right:5px;"></i>' . htmlspecialchars($msg['office_address'] ?? 'Не указан') . ' &middot; 
            <i class="fas fa-tag" style="margin-right:5px;"></i>' . htmlspecialchars($msg['type_name'] ?? 'Не указан') . '
        </div>
        
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="upvote-btn" onclick="vote(' . $msg['id'] . ', \'up\')" style="background:none; border:none; cursor:pointer; color:' . ($user_vote === 1 ? '#16a085' : '#bdc3c7') . '; font-size:1.2em;"><i class="fas fa-thumbs-up"></i></button>
            <span class="score" style="font-weight:bold; color:#34495e;">' . ($msg['upvotes'] - $msg['downvotes']) . '</span>
            <button class="downvote-btn" onclick="vote(' . $msg['id'] . ', \'down\')" style="background:none; border:none; cursor:pointer; color:' . ($user_vote === -1 ? '#c0392b' : '#bdc3c7') . '; font-size:1.2em;"><i class="fas fa-thumbs-down"></i></button>
            
            ' . ($is_admin || $msg['user_id'] == $current_user_id ? '
            <button class="edit-btn" onclick="editMessage(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:#f39c12; margin-left:10px;"><i class="fas fa-edit"></i> Редактировать</button>
            <button class="delete-btn" onclick="deleteMessage(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:#e74c3c; margin-left:10px;"><i class="fas fa-trash"></i> Удалить</button>
            ' : '') . '
            
            <button onclick="toggleReply(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:#3498db; margin-left:10px;"><i class="fas fa-reply"></i> Ответить</button>
        </div>
        
        <div id="reply-form-' . $msg['id'] . '" style="display:none; margin-top:15px;">
            <textarea id="reply-text-' . $msg['id'] . '" placeholder="Ваш ответ..." style="width:100%; min-height:80px; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
            <button onclick="submitReply(' . $msg['id'] . ')" style="margin-top:10px; padding:8px 16px; background:#3498db; color:white; border:none; border-radius:6px; cursor:pointer;">Отправить</button>
        </div>

        <div>
            <button id="show-replies-' . $msg['id'] . '" onclick="loadReplies(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:#3498db; margin-top:10px;">' . ($replies_count > 0 ? 'Показать ответы (' . $replies_count . ')' : 'Нет ответов') . '</button>
            <div id="replies-' . $msg['id'] . '" style="display:none; margin-top:15px;"></div>
        </div>
    </div>';
}
?>

<!-- Остальная часть HTML кода без изменений -->
<div
    style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); margin-bottom:40px;">
    <h2 style="margin-bottom:25px; color:#2c3e50;">Новое предложение или жалоба</h2>
    <form method="POST">
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom:20px;">
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

        <textarea name="message" placeholder="Текст..." required
            style="width:100%; min-height:140px; padding:15px; border:1px solid #ddd; border-radius:8px; font-size:16px; margin-bottom:20px;"></textarea>

        <button type="submit"
            style="padding:14px 32px; background:#3498db; color:white; border:none; border-radius:8px; font-size:18px; cursor:pointer;">Отправить</button>
    </form>
</div>

<div id="messages-list">
    <?php foreach ($messages as $msg): ?>
    <?php renderMessage($msg, $user_id, $is_admin); ?>
    <?php endforeach; ?>
</div>

<script>
// Переменные для проверки прав
const currentUserId = <?= $user_id ?>;
const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;

// ГОЛОСОВАНИЕ
function vote(id, type) {
    const msgEl = document.getElementById(`msg-${id}`);
    let currentVote = parseInt(msgEl.dataset.userVote || '0');

    let newVote;
    if (type === 'up') {
        newVote = currentVote === 1 ? 0 : 1;
    } else {
        newVote = currentVote === -1 ? 0 : -1;
    }

    let postData;
    if (newVote === 0) {
        postData = `action=vote&message_id=${id}&vote=remove`;
    } else {
        let voteType = newVote === 1 ? 'up' : 'down';
        postData = `action=vote&message_id=${id}&vote=${voteType}`;
    }

    fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: postData,
            credentials: 'same-origin'
        })
        .then(r => r.text().then(text => JSON.parse(text)))
        .then(data => {
            if (data.success) {
                const scoreEl = msgEl.querySelector('.score');
                scoreEl.textContent = data.score;

                const upBtn = msgEl.querySelector('.upvote-btn');
                const downBtn = msgEl.querySelector('.downvote-btn');
                upBtn.style.color = newVote === 1 ? '#16a085' : '#bdc3c7';
                downBtn.style.color = newVote === -1 ? '#c0392b' : '#bdc3c7';

                msgEl.dataset.userVote = newVote;
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестно'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Ошибка при голосовании');
        });
}

// УДАЛЕНИЕ
function deleteMessage(id) {
    if (!confirm('Удалить сообщение и все ответы?')) return;

    fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=delete&id=${id}`,
            credentials: 'same-origin'
        })
        .then(r => r.text().then(text => JSON.parse(text)))
        .then(data => {
            if (data.success) {
                document.getElementById(`msg-${id}`).remove();
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестно'));
            }
        })
        .catch(error => {
            console.error('Ошибка удаления:', error);
            alert('Ошибка сети.');
        });
}

// РЕДАКТИРОВАНИЕ
function editMessage(id) {
    const textDiv = document.querySelector(`#msg-${id} .msg-text`);
    const originalHTML = textDiv.innerHTML;
    const currentText = textDiv.textContent.trim();

    const textarea = document.createElement('textarea');
    textarea.value = currentText;
    textarea.style.cssText =
        'width:100%; min-height:120px; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;';

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Сохранить';
    saveBtn.style.cssText =
        'margin-top:10px; padding:10px 20px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer;';

    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Отмена';
    cancelBtn.style.cssText =
        'margin-top:10px; margin-left:10px; padding:10px 20px; background:#95a5a6; color:white; border:none; border-radius:6px; cursor:pointer;';

    cancelBtn.onclick = () => textDiv.innerHTML = originalHTML;

    saveBtn.onclick = () => {
        const newText = textarea.value.trim();
        if (!newText) return alert('Пустое сообщение');

        fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=update&id=${id}&message=${encodeURIComponent(newText)}`,
                credentials: 'same-origin'
            })
            .then(r => r.text().then(text => JSON.parse(text)))
            .then(data => {
                if (data.success) {
                    textDiv.innerHTML = data.message;
                } else {
                    alert('Ошибка: ' + (data.error || 'Неизвестно'));
                }
            })
            .catch(error => {
                console.error('Ошибка редактирования:', error);
                alert('Ошибка сети.');
            });
    };

    textDiv.innerHTML = '';
    textDiv.appendChild(textarea);
    textDiv.appendChild(document.createElement('br'));
    textDiv.appendChild(saveBtn);
    textDiv.appendChild(cancelBtn);
}

// ОТВЕТЫ
function toggleReply(id) {
    const form = document.getElementById(`reply-form-${id}`);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function submitReply(id) {
    const text = document.getElementById(`reply-text-${id}`).value.trim();
    if (!text) return alert('Пустой ответ');

    fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=reply&parent_id=${id}&message=${encodeURIComponent(text)}`,
            credentials: 'same-origin'
        })
        .then(r => r.text().then(text => JSON.parse(text)))
        .then(data => {
            if (data.success) {
                // Очищаем и скрываем форму
                document.getElementById(`reply-text-${id}`).value = '';
                toggleReply(id);

                // Обновляем счетчик ответов
                const showButton = document.getElementById(`show-replies-${id}`);
                let count = parseInt(showButton.textContent.match(/\d+/) || 0) + 1;
                showButton.textContent = count > 0 ? `Показать ответы (${count})` : 'Нет ответов';

                // Если ответы открыты - перезагружаем их
                const repliesDiv = document.getElementById(`replies-${id}`);
                if (repliesDiv.style.display === 'block') {
                    loadReplies(id);
                }
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестно'));
            }
        })
        .catch(error => {
            console.error('Ошибка ответа:', error);
            alert('Ошибка сети при отправке ответа');
        });
}

// ЗАГРУЗКА ОТВЕТОВ - ИСПРАВЛЕННАЯ ВЕРСИЯ С КНОПКАМИ УПРАВЛЕНИЯ
function loadReplies(id) {
    const repliesDiv = document.getElementById(`replies-${id}`);
    const showButton = document.getElementById(`show-replies-${id}`);

    console.log('loadReplies called for id:', id);

    // Если ответы уже показаны, скрываем их
    if (repliesDiv.style.display === 'block') {
        console.log('Hiding replies');
        repliesDiv.style.display = 'none';
        showButton.textContent = showButton.textContent.replace('Скрыть', 'Показать');
        return;
    }

    console.log('Loading replies from API...');

    // Показываем индикатор загрузки
    repliesDiv.innerHTML = '<div style="text-align: center; color: #3498db; padding: 20px;">Загрузка ответов...</div>';
    repliesDiv.style.display = 'block';

    fetch(`api.php?action=get_replies&parent_id=${id}`)
        .then(r => {
            console.log('Response status:', r.status);
            return r.text().then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON');
                }
            });
        })
        .then(data => {
            console.log('Processing replies data:', data);

            // ОЧИЩАЕМ контейнер
            repliesDiv.innerHTML = '';

            if (data && data.length > 0) {
                data.forEach(reply => {
                    console.log('Processing reply:', reply);

                    // Проверяем, может ли пользователь редактировать/удалять этот ответ
                    const canEditDelete = isAdmin || reply.user_id == currentUserId;

                    // Создаем элемент для ответа
                    const replyElement = document.createElement('div');
                    replyElement.className = 'reply-message';
                    replyElement.id = `msg-${reply.id}`;
                    replyElement.setAttribute('data-user-vote', reply.user_vote || 0);
                    replyElement.style.cssText =
                        'margin-left: 40px; border-left: 3px solid #3498db; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 20px;';

                    // Заполняем содержимое
                    replyElement.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; color: #3498db;">${escapeHtml(reply.username)}</span>
                            <span style="color: #95a5a6; font-size: 0.9em;">${reply.created_at_formatted || formatDate(reply.created_at)}</span>
                        </div>
                        <div class="msg-text" data-id="${reply.id}" style="margin-bottom: 15px; white-space: pre-line;">${escapeHtml(reply.message)}</div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button class="upvote-btn" onclick="vote(${reply.id}, 'up')" style="background: none; border: none; cursor: pointer; color: ${reply.user_vote == 1 ? '#16a085' : '#bdc3c7'}; font-size: 1.2em;">
                                <i class="fas fa-thumbs-up"></i>
                            </button>
                            <span class="score" style="font-weight: bold; color: #34495e;">${reply.score || 0}</span>
                            <button class="downvote-btn" onclick="vote(${reply.id}, 'down')" style="background: none; border: none; cursor: pointer; color: ${reply.user_vote == -1 ? '#c0392b' : '#bdc3c7'}; font-size: 1.2em;">
                                <i class="fas fa-thumbs-down"></i>
                            </button>
                            
                            ${canEditDelete ? `
                            <button class="edit-btn" onclick="editMessage(${reply.id})" style="background: none; border: none; cursor: pointer; color: #f39c12; margin-left: 10px;">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <button class="delete-btn" onclick="deleteMessage(${reply.id})" style="background: none; border: none; cursor: pointer; color: #e74c3c; margin-left: 10px;">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                            ` : ''}
                            
                            <button onclick="toggleReply(${reply.id})" style="background: none; border: none; cursor: pointer; color: #3498db; margin-left: 10px;">
                                <i class="fas fa-reply"></i> Ответить
                            </button>
                        </div>
                        
                        <div id="reply-form-${reply.id}" style="display:none; margin-top:15px;">
                            <textarea id="reply-text-${reply.id}" placeholder="Ваш ответ..." style="width:100%; min-height:80px; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
                            <button onclick="submitReply(${reply.id})" style="margin-top:10px; padding:8px 16px; background:#3498db; color:white; border:none; border-radius:6px; cursor:pointer;">Отправить</button>
                        </div>
                    `;

                    repliesDiv.appendChild(replyElement);
                });
            } else {
                repliesDiv.innerHTML =
                    '<div style="text-align: center; color: #7f8c8d; padding: 20px;">Ответов пока нет</div>';
            }

            // Обновляем текст кнопки
            showButton.textContent = showButton.textContent.replace('Показать', 'Скрыть');
            console.log('Replies loaded successfully');
        })
        .catch(error => {
            console.error('Ошибка загрузки ответов:', error);
            repliesDiv.innerHTML =
                '<div style="color: #e74c3c; padding: 10px; text-align: center;">Ошибка загрузки ответов: ' + error
                .message + '</div>';
        });
}

// Функция для экранирования HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Функция для форматирования даты
function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

// Зависимые селекты
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

window.addEventListener('load', () => {
    if (document.getElementById('city-select').value) loadCompanies();
    if (document.getElementById('company-select').value) loadOffices();
});
</script>

<?php require 'footer.php'; ?>