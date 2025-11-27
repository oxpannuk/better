<?php
session_start();

if (!file_exists('db.php')) {
    die(json_encode(['success' => false, 'error' => 'db.php not found']));
}
require_once 'db.php';

// Подавляем вывод ошибок, чтобы не портили JSON (логируем в error_log)
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// CORS headers for localhost
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0); // Handle preflight request
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? 'user') === 'admin';

// ----------------- КОМПАНИИ -----------------
if (isset($_GET['action']) && $_GET['action'] === 'get_companies') {
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ----------------- КОМПАНИИ В ГОРОДЕ -----------------
if ($_GET['action'] === 'get_companies' && !empty($_GET['city_id'])) {
    $city_id = (int)$_GET['city_id'];
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.id, c.name 
        FROM companies c
        JOIN offices o ON c.id = o.company_id 
        WHERE o.city_id = ? 
        ORDER BY c.name
    ");
    $stmt->execute([$city_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ----------------- ОФИСЫ -----------------
if (isset($_GET['action']) && $_GET['action'] === 'get_offices') {
    if (!empty($_GET['company_id']) && !empty($_GET['city_id'])) {
        $company_id = (int)$_GET['company_id'];
        $city_id = (int)$_GET['city_id'];
        $stmt = $pdo->prepare("SELECT id, address FROM offices WHERE company_id = ? AND city_id = ? ORDER BY address");
        $stmt->execute([$company_id, $city_id]);
    } elseif (!empty($_GET['company_id'])) {
        $company_id = (int)$_GET['company_id'];
        $stmt = $pdo->prepare("SELECT id, address FROM offices WHERE company_id = ? ORDER BY address");
        $stmt->execute([$company_id]);
    } else {
        echo json_encode([]);
        exit;
    }
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// ----------------- ГОЛОСОВАНИЕ -----------------
if (($_POST['action'] ?? '') === 'vote' && isset($_POST['message_id'], $_POST['vote'])) {
    $message_id = (int)$_POST['message_id'];
    $vote = $_POST['vote'];

    // Удаляем предыдущий голос пользователя всегда
    $pdo->prepare("DELETE FROM message_votes WHERE message_id = ? AND user_id = ?")->execute([$message_id, $user_id]);

    // Если не 'remove', ставим новый
    if ($vote !== 'remove') {
        $vote_val = $vote === 'up' ? 1 : -1;
        $pdo->prepare("INSERT INTO message_votes (message_id, user_id, vote) VALUES (?, ?, ?)")
            ->execute([$message_id, $user_id, $vote_val]);
    }

    // Пересчитываем upvotes и downvotes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM message_votes WHERE message_id = ? AND vote = 1");
    $stmt->execute([$message_id]);
    $up = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM message_votes WHERE message_id = ? AND vote = -1");
    $stmt->execute([$message_id]);
    $down = $stmt->fetchColumn();

    $pdo->prepare("UPDATE messages SET upvotes = ?, downvotes = ? WHERE id = ?")
        ->execute([$up, $down, $message_id]);

    echo json_encode(['success' => true, 'score' => $up - $down]);
    exit;
}

// ----------------- УДАЛЕНИЕ -----------------
if (($_POST['action'] ?? '') === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn();

    if ($owner_id === false) {
        echo json_encode(['success' => false, 'error' => 'Сообщение не найдено']);
        exit;
    }

    if ($is_admin || $owner_id == $user_id) {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Нет прав']);
    }
    exit;
}

// ----------------- РЕДАКТИРОВАНИЕ -----------------
if (($_POST['action'] ?? '') === 'update' && isset($_POST['id'], $_POST['message'])) {
    $id = (int)$_POST['id'];
    $message = trim($_POST['message']);

    $stmt = $pdo->prepare("SELECT user_id FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $owner_id = $stmt->fetchColumn();

    if ($message && ($is_admin || $owner_id == $user_id)) {
        $pdo->prepare("UPDATE messages SET message = ? WHERE id = ?")
            ->execute([$message, $id]);

        echo json_encode([
            'success' => true,
            'message' => nl2br(htmlspecialchars($message))
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Пустое сообщение или нет прав']);
    }
    exit;
}

// ----------------- ОТВЕТЫ -----------------
if (($_POST['action'] ?? '') === 'reply' && isset($_POST['parent_id'], $_POST['message'])) {
    $parent_id = (int)$_POST['parent_id'];
    $message = trim($_POST['message']);

    if ($message) {
        $pdo->prepare("INSERT INTO messages (user_id, message, parent_id) VALUES (?, ?, ?)")
            ->execute([$user_id, $message, $parent_id]);
        $new_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'new_id' => $new_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Пустое сообщение']);
    }
    exit;
}

// ----------------- ПОЛУЧЕНИЕ ОТВЕТОВ -----------------
if (($_GET['action'] ?? '') === 'get_replies' && isset($_GET['parent_id'])) {
    $parent_id = (int)$_GET['parent_id'];

    $stmt = $pdo->prepare("
        SELECT m.*, u.username, 
               (m.upvotes - m.downvotes) as score, 
               COALESCE((SELECT vote FROM message_votes WHERE message_id = m.id AND user_id = ?), 0) as user_vote,
               DATE_FORMAT(m.created_at, '%d.%m.%Y %H:%i') as created_at_formatted
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.parent_id = ? 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $parent_id]);
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($replies);
    exit;
}

// Если ничего не подошло
echo json_encode(['success' => false, 'error' => 'Unknown action']);