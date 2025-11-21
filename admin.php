<?php
$page_title = "Админ-панель";

/* === СЕССИЯ И БД СРАЗУ === */
session_start();
require_once 'db.php';

/* === ПРОВЕРКА АДМИНА === */
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Нет доступа']);
        exit;
    }
    header("Location: index.php");
    exit;
}

/* ===================================================================
   AJAX — удаление и редактирование сообщений (ДО ЛЮБОГО ВЫВОДА HTML!)
   =================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    if ($_POST['ajax_action'] === 'delete_message') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM messages WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_POST['ajax_action'] === 'update_message') {
        $id = (int)$_POST['id'];
        $message = trim($_POST['message'] ?? '');
        if ($message !== '') {
            $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?")->execute([$message, $id]);
            echo json_encode([
                'success' => true,
                'html' => nl2br(htmlspecialchars($message))
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

/* ===================================================================
   ОБЫЧНЫЕ POST — справочники
   =================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_city']) && trim($_POST['city_name'] ?? '')) {
        $pdo->prepare("INSERT INTO cities (name) VALUES (?)")->execute([trim($_POST['city_name'])]);
    }
    if (isset($_POST['delete_city'])) {
        $pdo->prepare("DELETE FROM cities WHERE id = ?")->execute([(int)$_POST['city_id']]);
    }

    if (isset($_POST['add_company']) && trim($_POST['company_name'] ?? '')) {
        $pdo->prepare("INSERT INTO companies (name) VALUES (?)")->execute([trim($_POST['company_name'])]);
    }
    if (isset($_POST['delete_company'])) {
        $pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([(int)$_POST['company_id']]);
    }

    if (isset($_POST['add_office'])) {
        $address = trim($_POST['office_address'] ?? '');
        $city_id = (int)($_POST['office_city_id'] ?? 0);
        $company_id = (int)($_POST['office_company_id'] ?? 0);
        if ($address && $city_id && $company_id) {
            $pdo->prepare("INSERT INTO offices (company_id, city_id, address) VALUES (?, ?, ?)")
                ->execute([$company_id, $city_id, $address]);
        }
    }
    if (isset($_POST['delete_office'])) {
        $pdo->prepare("DELETE FROM offices WHERE id = ?")->execute([(int)$_POST['office_id']]);
    }

    header("Location: admin.php");
    exit;
}

/* ===================================================================
   ДАННЫЕ ДЛЯ СТРАНИЦЫ
   =================================================================== */
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$offices = $pdo->query("
    SELECT o.id, o.address, c.name as city_name, comp.name as company_name
    FROM offices o
    JOIN cities c ON o.city_id = c.id
    JOIN companies comp ON o.company_id = comp.id
    ORDER BY c.name, comp.name, o.address
")->fetchAll(PDO::FETCH_ASSOC);

$messages = $pdo->query("
    SELECT m.id, m.message, m.created_at, u.username
    FROM messages m 
    JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* ===================================================================
   ТЕПЕРЬ ПОДКЛЮЧАЕМ ШАПКУ (после всей логики!)
   =================================================================== */
require 'header.php';
?>

<h1 style="margin-bottom: 30px; color:#2c3e50;">Админ-панель</h1>

<!-- ====================== УПРАВЛЕНИЕ СПРАВОЧНИКАМИ ====================== -->
<div style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); margin-bottom:50px;">
    <h2 style="color:#2c3e50; margin-bottom:25px;">Управление справочниками</h2>

    <!-- ГОРОДА -->
    <div style="margin-bottom:50px;">
        <h3 style="color:#3498db;">Города</h3>
        <form method="POST" style="margin:20px 0;">
            <input type="text" name="city_name" placeholder="Новый город" required style="padding:12px; width:300px; border-radius:6px; border:1px solid #ddd;">
            <button name="add_city" type="submit" style="padding:12px 24px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer;">Добавить</button>
        </form>

        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#ecf0f1;">
                <tr><th style="text-align:left;padding:15px;">ID</th><th style="text-align:left;padding:15px;">Название</th><th style="padding:15px;">Действие</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cities as $c): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:15px;"><?= $c['id'] ?></td>
                    <td style="padding:15px;"><?= htmlspecialchars($c['name']) ?></td>
                    <td style="padding:15px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="city_id" value="<?= $c['id'] ?>">
                            <button name="delete_city" type="submit" onclick="return confirm('Удалить город?')" style="background:#e74c3c;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- КОМПАНИИ -->
    <div style="margin-bottom:50px;">
        <h3 style="color:#3498db;">Компании</h3>
        <form method="POST" style="margin:20px 0;">
            <input type="text" name="company_name" placeholder="Новая компания" required style="padding:12px; width:400px; border-radius:6px; border:1px solid #ddd;">
            <button name="add_company" type="submit" style="padding:12px 24px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer;">Добавить</button>
        </form>

        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#ecf0f1;">
                <tr><th style="text-align:left;padding:15px;">ID</th><th style="text-align:left;padding:15px;">Название</th><th style="padding:15px;">Действие</th></tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $comp): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:15px;"><?= $comp['id'] ?></td>
                    <td style="padding:15px;"><?= htmlspecialchars($comp['name']) ?></td>
                    <td style="padding:15px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="company_id" value="<?= $comp['id'] ?>">
                            <button name="delete_company" type="submit" onclick="return confirm('Удалить компанию?')" style="background:#e74c3c;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ОФИСЫ -->
    <div>
        <h3 style="color:#3498db;">Офисы</h3>
        <form method="POST" style="margin:20px 0;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px,1fr)); gap:15px; margin-bottom:15px;">
                <select name="office_city_id" required>
                    <option value="">Город</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="office_company_id" required>
                    <option value="">Компания</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="office_address" placeholder="Адрес офиса" required style="padding:12px; border:1px solid #ddd; border-radius:6px;">
            </div>
            <button name="add_office" type="submit" style="padding:12px 28px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer;">Добавить офис</button>
        </form>

        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#ecf0f1;">
                <tr>
                    <th style="text-align:left;padding:15px;">ID</th>
                    <th style="text-align:left;padding:15px;">Адрес</th>
                    <th style="text-align:left;padding:15px;">Город</th>
                    <th style="text-align:left;padding:15px;">Компания</th>
                    <th style="text-align:center;padding:15px;">Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offices as $o): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:15px;"><?= $o['id'] ?></td>
                    <td style="padding:15px;"><?= htmlspecialchars($o['address']) ?></td>
                    <td style="padding:15px;"><?= htmlspecialchars($o['city_name']) ?></td>
                    <td style="padding:15px;"><?= htmlspecialchars($o['company_name']) ?></td>
                    <td style="padding:15px; text-align:center;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="office_id" value="<?= $o['id'] ?>">
                            <button name="delete_office" type="submit" onclick="return confirm('Удалить офис?')" style="background:#e74c3c;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- МОДЕРАЦИЯ СООБЩЕНИЙ -->
<div style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
    <h2 style="color:#2c3e50; margin-bottom:25px;">Модерация сообщений</h2>
    <p style="color:#7f8c8d; margin-bottom:20px;">Всего сообщений: <strong><?= count($messages) ?></strong></p>

    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f0f2f5;">
            <tr>
                <th style="text-align:left;padding:15px;width:60px;">ID</th>
                <th style="text-align:left;padding:15px;width:140px;">Автор</th>
                <th style="text-align:left;padding:15px;width:160px;">Дата</th>
                <th style="text-align:left;padding:15px;">Сообщение</th>
                <th style="text-align:center;padding:15px;width:200px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr style="border-bottom:1px solid #eee;" data-id="<?= $msg['id'] ?>">
                <td style="padding:15px;font-family:monospace;"><?= $msg['id'] ?></td>
                <td style="padding:15px;"><?= htmlspecialchars($msg['username']) ?></td>
                <td style="padding:15px;color:#7f8c8d;"><?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?></td>
                <td style="padding:15px;" class="msg-text"><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                <td style="padding:15px;text-align:center;">
                    <button class="edit-btn" data-id="<?= $msg['id'] ?>" style="background:#f39c12;color:white;border:none;padding:10px 18px;border-radius:6px;cursor:pointer;margin:0 5px;">Редактировать</button>
                    <button class="delete-btn" data-id="<?= $msg['id'] ?>" style="background:#e74c3c;color:white;border:none;padding:10px 18px;border-radius:6px;cursor:pointer;margin:0 5px;">Удалить</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
        if (!confirm('Удалить сообщение и все ответы?')) return;

        const id = e.target.dataset.id;
        const row = e.target.closest('tr');

        fetch('admin.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ajax_action=delete_message&id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                row.remove();
            } else {
                alert('Ошибка удаления');
            }
        })
        .catch(() => alert('Ошибка сети'));
    }

    if (e.target.classList.contains('edit-btn')) {
        const id = e.target.dataset.id;
        const row = e.target.closest('tr');
        const cell = row.querySelector('.msg-text');
        const originalHTML = cell.innerHTML;
        const currentText = cell.textContent.trim();

        const textarea = document.createElement('textarea');
        textarea.value = currentText;
        textarea.style.cssText = 'width:100%; min-height:120px; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;';

        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Сохранить';
        saveBtn.style.cssText = 'margin-top:10px; padding:10px 20px; background:#27ae60; color:white; border:none; border-radius:6px; cursor:pointer;';

        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Отмена';
        cancelBtn.style.cssText = 'margin-top:10px; margin-left:10px; padding:10px 20px; background:#95a5a6; color:white; border:none; border-radius:6px; cursor:pointer;';

        cancelBtn.onclick = () => cell.innerHTML = originalHTML;

        saveBtn.onclick = () => {
            const newText = textarea.value.trim();
            if (!newText) return alert('Сообщение не может быть пустым');

            fetch('admin.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ajax_action=update_message&id=' + id + '&message=' + encodeURIComponent(newText)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    cell.innerHTML = data.html;
                } else {
                    alert('Ошибка');
                    cell.innerHTML = originalHTML;
                }
            })
            .catch(() => alert('Ошибка сети'));
        };

        cell.innerHTML = '';
        cell.appendChild(textarea);
        cell.appendChild(document.createElement('br'));
        cell.appendChild(saveBtn);
        cell.appendChild(cancelBtn);
    }
});
</script>

<?php require 'footer.php'; ?>