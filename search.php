<?php
$page_title = "Поиск сообщений";
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// === ПОИСК И ФИЛЬТРЫ ===
$query = trim($_GET['q'] ?? '');
$city_id = $_GET['city'] ?? '';
$company_id = $_GET['company'] ?? '';
$type_id = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';

$where = ["m.parent_id IS NULL"];
$params = [];

if (!empty($query)) {
    $where[] = "(m.message LIKE ? OR u.username LIKE ?)";
    $search_term = "%$query%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($city_id) {
    $where[] = "m.city_id = ?";
    $params[] = $city_id;
}
if ($company_id) {
    $where[] = "m.company_id = ?";
    $params[] = $company_id;
}
if ($type_id) {
    $where[] = "m.type_id = ?";
    $params[] = $type_id;
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Определяем порядок сортировки
switch ($sort) {
    case 'date_new':
        $order_by = 'm.created_at DESC';
        break;
    case 'date_old':
        $order_by = 'm.created_at ASC';
        break;
    case 'upvotes':
        $order_by = 'm.upvotes DESC, m.created_at DESC';
        break;
    case 'score':
        $order_by = '(m.upvotes - m.downvotes) DESC, m.created_at DESC';
        break;
    case 'relevance':
    default:
        // Если есть поисковый запрос, сортируем по релевантности
        if (!empty($query)) {
            $order_by = "
                CASE 
                    WHEN m.message LIKE ? THEN 3
                    WHEN u.username LIKE ? THEN 2
                    ELSE 1
                END DESC,
                (m.upvotes - m.downvotes) DESC,
                m.created_at DESC
            ";
            $params[] = "%$query%";
            $params[] = "%$query%";
        } else {
            $order_by = 'm.created_at DESC';
        }
        break;
}

// ЗАПРОС СООБЩЕНИЙ
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

// Функция renderMessage такая же как в index.php
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

    // Подсветка поискового запроса
    global $query;
    $highlighted_message = $msg['message'];
    $highlighted_username = $msg['username'];
    
    if (!empty($query)) {
        $highlighted_message = preg_replace("/(" . preg_quote($query, '/') . ")/i", '<mark style="background:#ffeaa7; color: #000;">$1</mark>', htmlspecialchars($msg['message']));
        $highlighted_username = preg_replace("/(" . preg_quote($query, '/') . ")/i", '<mark style="background:#ffeaa7; color: #000;">$1</mark>', htmlspecialchars($msg['username']));
    } else {
        $highlighted_message = nl2br(htmlspecialchars($msg['message']));
        $highlighted_username = htmlspecialchars($msg['username']);
    }

    echo '
    <div class="message" id="msg-' . $msg['id'] . '" data-user-vote="' . $user_vote . '" style="margin-left: ' . $indent . 'px; border-left:3px solid var(--primary-color); padding:20px; background:var(--card-bg); border-radius:12px; box-shadow:var(--shadow); margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span style="font-weight:bold; color:var(--primary-color);">' . $highlighted_username . '</span>
            <span style="color:var(--secondary-color); font-size:0.9em;">' . date('d.m.Y H:i', strtotime($msg['created_at'])) . '</span>
        </div>
        
        <div class="msg-text" data-id="' . $msg['id'] . '" style="margin-bottom:15px; color:var(--text-color);">' . $highlighted_message . '</div>
        
        <div style="color:var(--secondary-color); font-size:0.9em; margin-bottom:10px;">
            <i class="fas fa-map-marker-alt" style="margin-right:5px;"></i>' . htmlspecialchars($msg['city_name'] ?? 'Не указан') . ' &middot; 
            <i class="fas fa-building" style="margin-right:5px;"></i>' . htmlspecialchars($msg['company_name'] ?? 'Не указана') . ' &middot; 
            <i class="fas fa-home" style="margin-right:5px;"></i>' . htmlspecialchars($msg['office_address'] ?? 'Не указан') . ' &middot; 
            <i class="fas fa-tag" style="margin-right:5px;"></i>' . htmlspecialchars($msg['type_name'] ?? 'Не указан') . '
        </div>
        
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="upvote-btn" onclick="vote(' . $msg['id'] . ', \'up\')" style="background:none; border:none; cursor:pointer; color:' . ($user_vote === 1 ? '#16a085' : 'var(--secondary-color)') . '; font-size:1.2em;"><i class="fas fa-thumbs-up"></i></button>
            <span class="score" style="font-weight:bold; color:var(--text-color);">' . ($msg['upvotes'] - $msg['downvotes']) . '</span>
            <button class="downvote-btn" onclick="vote(' . $msg['id'] . ', \'down\')" style="background:none; border:none; cursor:pointer; color:' . ($user_vote === -1 ? '#c0392b' : 'var(--secondary-color)') . '; font-size:1.2em;"><i class="fas fa-thumbs-down"></i></button>
            
            ' . ($is_admin || $msg['user_id'] == $current_user_id ? '
            <button class="edit-btn" onclick="editMessage(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:var(--warning-color); margin-left:10px;"><i class="fas fa-edit"></i> Редактировать</button>
            <button class="delete-btn" onclick="deleteMessage(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:var(--danger-color); margin-left:10px;"><i class="fas fa-trash"></i> Удалить</button>
            ' : '') . '
            
            ' . ($depth === 0 ? '<button onclick="toggleReply(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:var(--primary-color); margin-left:10px;"><i class="fas fa-reply"></i> Ответить</button>' : '') . '
        </div>
        
        ' . ($depth === 0 ? '
        <div id="reply-form-' . $msg['id'] . '" style="display:none; margin-top:15px;">
            <textarea id="reply-text-' . $msg['id'] . '" placeholder="Ваш ответ..." style="width:100%; min-height:80px; padding:10px; border:1px solid var(--border-color); border-radius:8px; background:var(--card-bg); color:var(--text-color);"></textarea>
            <button onclick="submitReply(' . $msg['id'] . ')" style="margin-top:10px; padding:8px 16px; background:var(--primary-color); color:white; border:none; border-radius:6px; cursor:pointer;">Отправить</button>
        </div>' : '') . '

        <div>
            <button id="show-replies-' . $msg['id'] . '" onclick="loadReplies(' . $msg['id'] . ')" style="background:none; border:none; cursor:pointer; color:var(--primary-color); margin-top:10px;">' . ($replies_count > 0 ? 'Показать ответы (' . $replies_count . ')' : 'Нет ответов') . '</button>
            <div id="replies-' . $msg['id'] . '" style="display:none; margin-top:15px;"></div>
        </div>
    </div>';
}
?>

<!-- РЕЗУЛЬТАТЫ ПОИСКА -->
<div style="background:var(--card-bg); padding:30px; border-radius:12px; box-shadow:var(--shadow); margin-bottom:30px;">
    <h1 style="color:var(--text-color); margin-bottom:20px;">
        <i class="fas fa-search"></i> Результаты поиска
        <?php if (!empty($query)): ?>
            <span style="color:var(--secondary-color); font-size:0.8em;">по запросу: "<?= htmlspecialchars($query) ?>"</span>
        <?php endif; ?>
    </h1>

    <div style="color:var(--secondary-color); margin-bottom:25px;">
        Найдено сообщений: <strong><?= count($messages) ?></strong>
    </div>

    <!-- ФОРМА ПОИСКА И ФИЛЬТРОВ -->
    <form method="GET" style="margin-bottom:25px;">
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: end;">
            <div>
                <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--text-color);">Поиск по сообщениям</label>
                <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                       placeholder="Введите текст для поиска..." 
                       style="width:100%; padding:12px; border:2px solid var(--primary-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
            </div>
            <button type="submit" style="padding:12px 24px; background:var(--primary-color); color:white; border:none; border-radius:8px; cursor:pointer; font-size:16px;">
                <i class="fas fa-search"></i> Искать
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top:20px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500; color:var(--text-color);">Город</label>
                <select name="city" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; background:var(--card-bg); color:var(--text-color);">
                    <option value="">Все города</option>
                    <?php
                    $cities = $pdo->query("SELECT id, name FROM cities ORDER BY name")->fetchAll();
                    foreach ($cities as $c) {
                        $sel = $c['id'] == $city_id ? 'selected' : '';
                        echo "<option value=\"{$c['id']}\" $sel>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500; color:var(--text-color);">Компания</label>
                <select name="company" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; background:var(--card-bg); color:var(--text-color);">
                    <option value="">Все компании</option>
                    <?php
                    $companies = $pdo->query("SELECT id, name FROM companies ORDER BY name")->fetchAll();
                    foreach ($companies as $comp) {
                        $sel = $comp['id'] == $company_id ? 'selected' : '';
                        echo "<option value=\"{$comp['id']}\" $sel>{$comp['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500; color:var(--text-color);">Тип</label>
                <select name="type" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; background:var(--card-bg); color:var(--text-color);">
                    <option value="">Все типы</option>
                    <?php
                    $types = $pdo->query("SELECT id, name FROM suggestion_types ORDER BY id")->fetchAll();
                    foreach ($types as $t) {
                        $sel = $t['id'] == $type_id ? 'selected' : '';
                        echo "<option value=\"{$t['id']}\" $sel>{$t['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:500; color:var(--text-color);">Сортировка</label>
                <select name="sort" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:6px; background:var(--card-bg); color:var(--text-color);">
                    <option value="relevance" <?= $sort == 'relevance' ? 'selected' : '' ?>>По релевантности</option>
                    <option value="score" <?= $sort == 'score' ? 'selected' : '' ?>>По рейтингу</option>
                    <option value="upvotes" <?= $sort == 'upvotes' ? 'selected' : '' ?>>По лайкам</option>
                    <option value="date_new" <?= $sort == 'date_new' ? 'selected' : '' ?>>По дате (новые)</option>
                    <option value="date_old" <?= $sort == 'date_old' ? 'selected' : '' ?>>По дате (старые)</option>
                </select>
            </div>
        </div>
    </form>

    <!-- КНОПКА СБРОСА -->
    <?php if (!empty($query) || $city_id || $company_id || $type_id): ?>
    <div style="text-align:center; margin-top:15px;">
        <a href="search.php" style="color:var(--danger-color); text-decoration:none; font-size:14px;">
            <i class="fas fa-times"></i> Сбросить поиск и фильтры
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- РЕЗУЛЬТАТЫ -->
<div id="messages-list">
    <?php if (count($messages) > 0): ?>
        <?php foreach ($messages as $msg): ?>
            <?php renderMessage($msg, $user_id, $is_admin); ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align:center; padding:50px; background:var(--card-bg); border-radius:12px; box-shadow:var(--shadow);">
            <i class="fas fa-search" style="font-size:48px; color:var(--secondary-color); margin-bottom:20px;"></i>
            <h3 style="color:var(--secondary-color); margin-bottom:15px;">Сообщения не найдены</h3>
            <p style="color:var(--secondary-color);">Попробуйте изменить поисковый запрос или фильтры</p>
            <a href="index.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:var(--primary-color); color:white; text-decoration:none; border-radius:6px;">
                <i class="fas fa-arrow-left"></i> Вернуться на главную
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Подключаем тот же JavaScript что и в index.php -->
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
                upBtn.style.color = newVote === 1 ? '#16a085' : 'var(--secondary-color)';
                downBtn.style.color = newVote === -1 ? '#c0392b' : 'var(--secondary-color)';

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
        'width:100%; min-height:120px; padding:12px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);';

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Сохранить';
    saveBtn.style.cssText =
        'margin-top:10px; padding:10px 20px; background:var(--success-color); color:white; border:none; border-radius:6px; cursor:pointer;';

    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Отмена';
    cancelBtn.style.cssText =
        'margin-top:10px; margin-left:10px; padding:10px 20px; background:var(--secondary-color); color:white; border:none; border-radius:6px; cursor:pointer;';

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
                document.getElementById(`reply-text-${id}`).value = '';
                toggleReply(id);
                location.reload(); // Перезагружаем для обновления результатов
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестно'));
            }
        })
        .catch(error => {
            console.error('Ошибка ответа:', error);
            alert('Ошибка сети при отправке ответа');
        });
}

// ЗАГРУЗКА ОТВЕТОВ
function loadReplies(id) {
    const repliesDiv = document.getElementById(`replies-${id}`);
    const showButton = document.getElementById(`show-replies-${id}`);

    if (repliesDiv.style.display === 'block') {
        repliesDiv.style.display = 'none';
        showButton.textContent = showButton.textContent.replace('Скрыть', 'Показать');
        return;
    }

    repliesDiv.innerHTML = '<div style="text-align: center; color: var(--primary-color); padding: 20px;">Загрузка ответов...</div>';
    repliesDiv.style.display = 'block';

    fetch(`api.php?action=get_replies&parent_id=${id}`)
        .then(r => r.text().then(text => JSON.parse(text)))
        .then(data => {
            repliesDiv.innerHTML = '';

            if (data && data.length > 0) {
                data.forEach(reply => {
                    const canEditDelete = isAdmin || reply.user_id == currentUserId;

                    const replyElement = document.createElement('div');
                    replyElement.className = 'reply-message';
                    replyElement.id = `msg-${reply.id}`;
                    replyElement.setAttribute('data-user-vote', reply.user_vote || 0);
                    replyElement.style.cssText =
                        'margin-left: 40px; border-left: 3px solid var(--primary-color); padding: 20px; background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 20px;';

                    // Подсветка поиска в ответах
                    let highlighted_reply_message = reply.message;
                    let highlighted_reply_username = reply.username;
                    
                    if (!empty($query)) {
                        highlighted_reply_message = reply.message.replace(
                            new RegExp("(" + $query + ")", "gi"), 
                            '<mark style="background:#ffeaa7; color: #000;">$1</mark>'
                        );
                        highlighted_reply_username = reply.username.replace(
                            new RegExp("(" + $query + ")", "gi"), 
                            '<mark style="background:#ffeaa7; color: #000;">$1</mark>'
                        );
                    }

                    replyElement.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; color: var(--primary-color);">${highlighted_reply_username}</span>
                            <span style="color: var(--secondary-color); font-size: 0.9em;">${reply.created_at_formatted || reply.created_at}</span>
                        </div>
                        <div class="msg-text" data-id="${reply.id}" style="margin-bottom: 15px; white-space: pre-line; color: var(--text-color);">${highlighted_reply_message}</div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button class="upvote-btn" onclick="vote(${reply.id}, 'up')" style="background: none; border: none; cursor: pointer; color: ${reply.user_vote == 1 ? '#16a085' : 'var(--secondary-color)'}; font-size: 1.2em;">
                                <i class="fas fa-thumbs-up"></i>
                            </button>
                            <span class="score" style="font-weight: bold; color: var(--text-color);">${reply.score || 0}</span>
                            <button class="downvote-btn" onclick="vote(${reply.id}, 'down')" style="background: none; border: none; cursor: pointer; color: ${reply.user_vote == -1 ? '#c0392b' : 'var(--secondary-color)'}; font-size: 1.2em;">
                                <i class="fas fa-thumbs-down"></i>
                            </button>
                            
                            ${canEditDelete ? `
                            <button class="edit-btn" onclick="editMessage(${reply.id})" style="background: none; border: none; cursor: pointer; color: var(--warning-color); margin-left: 10px;">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <button class="delete-btn" onclick="deleteMessage(${reply.id})" style="background: none; border: none; cursor: pointer; color: var(--danger-color); margin-left: 10px;">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                            ` : ''}
                        </div>
                    `;

                    repliesDiv.appendChild(replyElement);
                });
            } else {
                repliesDiv.innerHTML =
                    '<div style="text-align: center; color: var(--secondary-color); padding: 20px;">Ответов пока нет</div>';
            }

            showButton.textContent = showButton.textContent.replace('Показать', 'Скрыть');
        })
        .catch(error => {
            console.error('Ошибка загрузки ответов:', error);
            repliesDiv.innerHTML =
                '<div style="color: var(--danger-color); padding: 10px; text-align: center;">Ошибка загрузки ответов</div>';
        });
}
</script>

<?php require 'footer.php'; ?>