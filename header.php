<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="ru" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Гостевая книга' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/logo.png">
    <style>
    /* CSS для темной темы */
    :root {
        --bg-color: #f8f9fa;
        --text-color: #333;
        --card-bg: #ffffff;
        --header-bg: #2c3e50;
        --header-text: #ffffff;
        --primary-color: #3498db;
        --secondary-color: #7f8c8d;
        --border-color: #ddd;
        --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        --hover-color: #2980b9;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
    }

    [data-theme="dark"] {
        --bg-color: #1a1a1a;
        --text-color: #e0e0e0;
        --card-bg: #2d2d2d;
        --header-bg: #1e2a3a;
        --header-text: #e0e0e0;
        --primary-color: #3498db;
        --secondary-color: #95a5a6;
        --border-color: #444;
        --shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        --hover-color: #2980b9;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif;
        transition: background-color 0.3s, color 0.3s, border-color 0.3s;
    }

    body {
        background: var(--bg-color);
        color: var(--text-color);
        line-height: 1.6;
        font-size: 16px;
    }

    header {
        background: var(--header-bg);
        color: var(--header-text);
        padding: 15px 0;
        box-shadow: var(--shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .logo {
        font-size: 1.5em;
        font-weight: bold;
        color: var(--primary-color);
        text-decoration: none;
        margin-bottom: 10px;
    }

    .logo i {
        margin-right: 8px;
    }

    .nav-links {
        display: flex;
        gap: 15px;
        list-style: none;
        flex-wrap: wrap;
    }

    .nav-links a {
        color: var(--header-text);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
        font-size: 0.9em;
    }

    .nav-links a:hover {
        color: var(--primary-color);
    }

    .auth-links a {
        color: var(--header-text);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9em;
    }

    .auth-links a:hover {
        color: var(--primary-color);
    }

    .auth-links .username {
        color: var(--primary-color);
        font-weight: bold;
        font-size: 0.9em;
    }

    .search-form {
        display: flex;
        margin: 10px 0;
        flex: 1;
        max-width: 400px;
        order: 0;
    }

    .search-input {
        flex: 1;
        padding: 8px 15px;
        border: 2px solid var(--primary-color);
        border-right: none;
        border-radius: 25px 0 0 25px;
        background: var(--card-bg);
        color: var(--text-color);
        outline: none;
        font-size: 14px;
        height: 38px;
    }

    .search-input::placeholder {
        color: var(--secondary-color);
    }

    .search-btn {
        background: var(--primary-color);
        color: white;
        border: 2px solid var(--primary-color);
        border-radius: 0 25px 25px 0;
        padding: 8px 16px;
        cursor: pointer;
        transition: background 0.3s;
        font-size: 13px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-btn:hover {
        background: var(--hover-color);
        border-color: var(--hover-color);
    }

    /* Переключатель темы */
    .theme-switch-wrapper {
        display: flex;
        align-items: center;
        margin-left: 15px;
    }

    .theme-switch {
        display: inline-block;
        height: 24px;
        position: relative;
        width: 50px;
    }

    .theme-switch input {
        display: none;
    }

    .slider {
        background-color: var(--secondary-color);
        bottom: 0;
        cursor: pointer;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        transition: .4s;
        border-radius: 24px;
    }

    .slider:before {
        background-color: white;
        bottom: 3px;
        content: "";
        height: 18px;
        left: 3px;
        position: absolute;
        transition: .4s;
        width: 18px;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: var(--primary-color);
    }

    input:checked+.slider:before {
        transform: translateX(26px);
    }

    .theme-icon {
        margin-left: 10px;
        color: var(--header-text);
        font-size: 1.1em;
    }

    /* Основной контент */
    main {
        padding: 20px 0;
    }

    /* Адаптив */
    @media (max-width: 768px) {
        .container {
            padding: 0 10px;
        }

        .nav {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .logo {
            font-size: 1.4em;
            margin-bottom: 5px;
        }

        .nav-links {
            gap: 10px;
            justify-content: center;
            margin: 5px 0;
        }

        .nav-links a {
            font-size: 0.85em;
        }

        .search-form {
            margin: 5px 0;
            max-width: 100%;
            order: 3;
            width: 100%;
        }

        .theme-switch-wrapper {
            margin-left: 0;
            margin-top: 5px;
        }

        .auth-links {
            font-size: 0.85em;
        }
    }

    @media (max-width: 480px) {
        .nav-links {
            gap: 8px;
        }

        .nav-links a {
            font-size: 0.8em;
        }

        .logo {
            font-size: 1.3em;
        }

        body {
            font-size: 14px;
        }
    }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo"><i class="fas fa-book"></i> better.</a>

                <form method="GET" action="search.php" class="search-form">
                    <input type="text" name="q" placeholder="Поиск сообщений..." class="search-input"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <ul class="nav-links">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="about.php">О нас</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                    <li><a href="help.php">Помощь</a></li>
                </ul>

                <div style="display: flex; align-items: center;">
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

                    <div class="theme-switch-wrapper">
                        <label class="theme-switch" for="checkbox">
                            <input type="checkbox" id="checkbox" />
                            <div class="slider"></div>
                        </label>
                        <span class="theme-icon">
                            <i class="fas fa-moon"></i>
                        </span>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    <main class="container">