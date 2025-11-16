<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// === Компании по городу ===
if ($_GET['action'] === 'get_companies' && isset($_GET['city_id'])) {
    $city_id = (int)$_GET['city_id'];
    $stmt = $pdo->prepare("SELECT id, name FROM companies WHERE city_id = ? ORDER BY name");
    $stmt->execute([$city_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// === Офисы по компании ===
if ($_GET['action'] === 'get_offices' && isset($_GET['company_id'])) {
    $company_id = (int)$_GET['company_id'];
    $stmt = $pdo->prepare("SELECT id, name, address FROM offices WHERE company_id = ? ORDER BY name");
    $stmt->execute([$company_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// === ГОЛОСОВАНИЕ ===
if ($_POST['action'] === 'vote' && isset($_POST['message_id']) && isset($_POST['vote'])) {
    $message_id = (int)$_POST['message_id'];
    $vote = $_POST['vote'] === 'up' ? 1 : -1;

    $pdo->prepare("DELETE FROM message_votes WHERE message_id = ? AND user_id = ?")->execute([$message_id, $user_id]);
    $pdo->prepare("INSERT INTO message_votes (message_id, user_id, vote) VALUES (?, ?, ?)")
          ->execute([$message_id, $user_id, $vote]);

    $up = $pdo->query("SELECT COUNT(*) FROM message_votes WHERE message_id = $message_id AND vote = 1")->fetchColumn();
    $down = $pdo->query("SELECT COUNT(*) FROM message_votes WHERE message_id = $message_id AND vote = -1")->fetchColumn();

    $pdo->prepare("UPDATE messages SET upvotes = ?, downvotes = ? WHERE id = ?")
          ->execute([$up, $down, $message_id]);

    echo json_encode([
        'success' => true,
        'score' => $up - $down
    ]);
    exit;
}

// === УДАЛЕНИЕ ===
if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn();

    if ($is_admin || $owner_id == $user_id) {
        $pdo->prepare("DELETE FROM messages WHERE id = ? OR parent_id = ?")->execute([$id, $id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// === РЕДАКТИРОВАНИЕ ===
if ($_POST['action'] === 'update' && isset($_POST['id']) && isset($_POST['message'])) {
    $id = (int)$_POST['id'];
    $message = trim($_POST['message']);

    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn();

    if ($message && ($is_admin || $owner_id == $user_id)) {
        $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?")->execute([$message, $id]);
        echo json_encode(['success' => true, 'message' => nl2br(htmlspecialchars($message))]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>