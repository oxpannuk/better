<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Гостевая книга' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/logo.png">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
        body { background:#f8f9fa; color:#333; line-height:1.6; }
        header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: #3498db;
            text-decoration: none;
        }
        .logo i { margin-right: 8px; }
        .nav-links {
            display: flex;
            gap: 25px;
            list-style: none;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: #3498db; }
        .auth-links a {
            color: #ecf0f1;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-links a:hover { color: #3498db; }
        .auth-links .username {
            color: #3498db;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .nav { flex-direction: column; gap: 15px; text-align: center; }
            .nav-links { flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo"><i class="fas fa-book"></i> better.</a>
                <ul class="nav-links">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="about.php">О нас</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                    <li><a href="help.php">Помощь</a></li>
                </ul>
                <div class="auth-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php" style="margin-left:10px; color:#f39c12; font-weight:bold;">Админка</a>
                        <?php endif; ?>
                        <a href="logout.php">[Выйти]</a>
                    <?php else: ?>
                        <a href="login.php">Вход</a> / <a href="register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="container" style="margin:30px auto; max-width:1200px;">