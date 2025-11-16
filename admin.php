<?php
$page_title = "Админ-панель";
require 'header.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: index.php");
    exit;
}

// === AJAX: Удаление ===
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => true]);
    exit;
}

// === AJAX: Обновление ===
if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['id']) && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $stmt = $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?");
    $stmt->execute([$message, $_POST['id']]);
    echo json_encode([
        'success' => true,
        'message' => nl2br(htmlspecialchars($message))
    ]);
    exit;
}

// === Все сообщения ===
$stmt = $pdo->query("
    SELECT m.*, u.username 
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
");
$messages = $stmt->fetchAll();
?>

<h1>Админ-панель: Модерация</h1>
<p style="color:#7f8c8d; margin-bottom:20px;">Всего сообщений: <strong><?= count($messages) ?></strong></p>

<div id="admin-messages">
    <?php foreach ($messages as $msg): ?>
        <div class="admin-message" data-id="<?= $msg['id'] ?>" style="background:#fff; padding:18px; margin-bottom:16px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-left:4px solid #e67e22;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <strong style="color:#e67e22;"><?= htmlspecialchars($msg['username']) ?></strong>
                    <span style="color:#95a5a6; font-size:0.9em; margin-left:10px;"><?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <div style="font-size:1.3em;">
                    <span class="admin-edit" style="cursor:pointer; color:#f39c12; margin-right:16px;" title="Редактировать">Edit</span>
                    <span class="admin-delete" style="cursor:pointer; color:#e74c3c;" title="Удалить">Delete</span>
                </div>
            </div>
            <div class="admin-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div id="admin-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:14px; width:90%; max-width:500px; box-shadow:0 10px 30px rgba(0,0,0,0.3); text-align:center;">
        <h3 style="margin-bottom:15px; color:#2c3e50;">Удалить сообщение?</h3>
        <p style="color:#7f8c8d; margin-bottom:20px;">Это действие нельзя отменить.</p>
        <button id="confirm-admin-delete" style="background:#e74c3c; color:white; padding:10px 24px; border:none; border-radius:8px; cursor:pointer;">Удалить</button>
        <button id="cancel-admin-delete" style="background:#95a5a6; color:white; padding:10px 24px; border:none; border-radius:8px; cursor:pointer; margin-left:10px;">Отмена</button>
    </div>
</div>

<style>
    .admin-message.editing { border-left: 4px solid #27ae60; }
    .admin-edit-controls { margin-top:12px; display:flex; gap:8px; }
    .admin-edit-controls button { padding:6px 14px; border:none; border-radius:6px; cursor:pointer; font-size:0.9em; }
    .save-btn { background:#27ae60; color:white; }
    .cancel-btn { background:#95a5a6; color:white; }
</style>

<script>
let adminEditItem = null;
let adminDeleteId = null;

document.getElementById('admin-messages').addEventListener('click', function(e) {
    const item = e.target.closest('.admin-message');
    if (!item) return;

    if (e.target.classList.contains('admin-edit')) {
        const textDiv = item.querySelector('.admin-text');
        const currentText = textDiv.textContent.trim();
        adminEditItem = item;

        textDiv.innerHTML = `
            <textarea class="admin-input" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:16px; margin-bottom:8px;">${currentText}</textarea>
            <div class="admin-edit-controls">
                <button class="save-btn">Сохранить</button>
                <button class="cancel-btn">Отмена</button>
            </div>
        `;
        item.classList.add('editing');
    }

    if (e.target.classList.contains('save-btn')) {
        const textarea = item.querySelector('.admin-input');
        const newMessage = textarea.value.trim();
        const id = item.dataset.id;

        if (!newMessage) {
            alert('Сообщение не может быть пустым');
            return;
        }

        fetch('admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&id=${id}&message=${encodeURIComponent(newMessage)}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                item.querySelector('.admin-text').innerHTML = data.message;
                item.classList.remove('editing');
            }
        });
    }

    if (e.target.classList.contains('cancel-btn')) {
        const original = item.querySelector('.admin-input').value;
        item.querySelector('.admin-text').innerHTML = original.replace(/\n/g, '<br>');
        item.classList.remove('editing');
    }

    if (e.target.classList.contains('admin-delete')) {
        adminDeleteId = item.dataset.id;
        document.getElementById('admin-modal').style.display = 'flex';
    }
});

document.getElementById('confirm-admin-delete').onclick = function() {
    fetch('admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete&id=' + adminDeleteId
    })
    .then(() => location.reload());
};

document.getElementById('cancel-admin-delete').onclick = function() {
    document.getElementById('admin-modal').style.display = 'none';
};
</script>

<?php require 'footer.php'; ?>