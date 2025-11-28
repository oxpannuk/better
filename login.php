<?php
session_start();
$page_title = "Вход в аккаунт";

// обработка POST запроса
if ($_POST) {
    require_once 'db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        header("Location: index.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

// редирект
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// подключение header.php
require 'header.php';
?>

<style>
.login-container {
    max-width: 420px;
    margin: 20px auto;
    background: var(--card-bg);
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-color);
}

.form-input {
    width: 100%;
    padding: 14px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    font-size: 16px;
    background: var(--card-bg);
    color: var(--text-color);
}

.submit-btn {
    width: 100%;
    background: var(--primary-color);
    color: white;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

.submit-btn:hover {
    background: var(--hover-color);
}

.auth-links {
    text-align: center;
    margin-top: 25px;
    color: var(--secondary-color);
}

.error-message {
    color: var(--danger-color);
    background: var(--card-bg);
    padding: 15px;
    border-radius: 8px;
    margin: 15px auto;
    text-align: center;
    border: 1px solid var(--danger-color);
}

/* адаптив */
@media (max-width: 768px) {
    .login-container {
        margin: 15px 10px;
        padding: 20px;
    }

    .form-input {
        padding: 12px;
        font-size: 16px;
    }

    h2 {
        font-size: 1.4em;
        margin-bottom: 20px;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 15px;
        margin: 10px 5px;
    }

    .form-input {
        padding: 10px;
    }

    .submit-btn {
        padding: 12px;
        font-size: 16px;
    }
}
</style>

<h2 style="text-align:center; margin-bottom:25px; color:var(--text-color);">Вход в аккаунт</h2>

<?php if (isset($error)): ?>
<div class="error-message">
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="login-container">
    <form method="POST">
        <div class="form-group">
            <label class="form-label">Логин</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required
                minlength="3" maxlength="30" placeholder="Введите логин" autocomplete="username" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Пароль</label>
            <input type="password" name="password" required placeholder="Введите пароль" autocomplete="current-password"
                class="form-input">
        </div>
        <button type="submit" class="submit-btn">
            Войти
        </button>
    </form>
</div>

<div class="auth-links">
    Нет аккаунта?
    <a href="register.php" style="color:var(--primary-color); font-weight:600; text-decoration:none;">Зарегистрироваться
        →</a>
</div>

<?php require 'footer.php'; ?>