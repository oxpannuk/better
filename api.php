<?php
session_start();
error_reporting(0);                    // ← ЭТО ГЛАВНОЕ! Больше НИКАКИХ <br /><b>Warning
ini_set('display_errors', 0);          // ← И ЭТО! Никаких HTML-ошибок в JSON

require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// === Компании ===
if (($_GET['action'] ?? '') === 'get_companies') {
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// === Офисы ===
if (($_GET['action'] ?? '') === 'get_offices' && isset($_GET['company_id'])) {
    $stmt = $pdo->prepare("SELECT id, address FROM offices WHERE company_id = ? ORDER BY address");
    $stmt->execute([(int)$_GET['company_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// === Голосование ===
if (($_POST['action'] ?? '') === 'vote' && isset($_POST['message_id'], $_POST['vote'])) {
    $mid = (int)$_POST['message_id'];
    $vote = $_POST['vote'] === 'up' ? 1 : -1;

    $pdo->prepare("DELETE FROM message_votes WHERE message_id = ? AND user_id = ?")->execute([$mid, $user_id]);
    $pdo->prepare("INSERT INTO message_votes (message_id, user_id, vote) VALUES (?, ?, ?)")->execute([$mid, $user_id, $vote]);

    $up = $pdo->query("SELECT COUNT(*) FROM message_votes WHERE message_id = $mid AND vote = 1")->fetchColumn();
    $down = $pdo->query("SELECT COUNT(*) FROM message_votes WHERE message_id = $mid AND vote = -1")->fetchColumn();

    $pdo->prepare("UPDATE messages SET upvotes = ?, downvotes = ? WHERE id = ?")->execute([$up, $down, $mid]);

    echo json_encode(['success' => true, 'score' => $up - $down]);
    exit;
}

// === УДАЛЕНИЕ — 100% безопасно ===
if (($_POST['action'] ?? '') === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn() ?: 0;        // ← Никогда не false → нет Warning

    if ($is_admin || $owner_id == $user_id) {
        $pdo->prepare("DELETE FROM messages WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Нет прав']);
    }
    exit;
}

// === РЕДАКТИРОВАНИЕ — 100% безопасно ===
if (($_POST['action'] ?? '') === 'update' && isset($_POST['id'], $_POST['message'])) {
    $id = (int)$_POST['id'];
    $message = trim($_POST['message']);

    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn() ?: 0;

    if (!$message) {
        echo json_encode(['success' => false, 'error' => 'Пустое сообщение']);
        exit;
    }

    if ($is_admin || $owner_id == $user_id) {
        $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?")->execute([$message, $id]);
        echo json_encode([
            'success' => true,
            'message' => nl2br(htmlspecialchars($message))
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Нет прав']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);