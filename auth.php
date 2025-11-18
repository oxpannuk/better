<?php
// auth.php обработчик регистрации и входа 
session_start();
require_once 'db.php';

if ($_POST['action'] ?? '' === 'register') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 6) {
        die('Логин минимум 3 символа, пароль — 6+');
    }

    // Проверка, что такой логин уже есть
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        die('Этот логин уже занят');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
    $stmt->execute([$username, $hash]);

    // Можно сразу авторизовать после регистрации
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';

    header("Location: index.php");
    exit;
}