<?php
$page_title = "Регистрация";
require 'header.php';

// Если пользователь уже авторизован — сразу кидает на главную
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    if (strlen($username) < 3) {
        $error = 'Логин должен быть не менее 3 символов';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $error = 'Логин может содержать только буквы, цифры, _ и -';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        // Проверяем, существует ли уже такой пользователь
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Этот логин уже занят';
        } else {
            // Регистрация
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hash]);

            // Автологин после регистрации
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';

            header("Location: index.php");
            exit;
        }
    }
}
?>

<h2 style="text-align:center; margin-bottom:30px; color:var(--text-color);">Создание аккаунта</h2>

<?php if ($error): ?>
    <div style="color:var(--danger-color); background:var(--card-bg); padding:15px; border-radius:8px; margin:20px auto; max-width:420px; text-align:center; border:1px solid var(--danger-color);">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" style="max-width:420px; margin:0 auto; background:var(--card-bg); padding:30px; border-radius:12px; box-shadow:var(--shadow);">
    <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--text-color);">Логин</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
               required minlength="3" maxlength="30"
               placeholder="Придумайте логин" autocomplete="username"
               style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
    </div>

    <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--text-color);">Пароль</label>
        <input type="password" name="password" required minlength="6"
               placeholder="Минимум 6 символов" autocomplete="new-password"
               style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
    </div>

    <div style="margin-bottom:25px;">
        <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--text-color);">Повторите пароль</label>
        <input type="password" name="password_confirm" required minlength="6"
               placeholder="Введите пароль ещё раз" autocomplete="new-password"
               style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
    </div>

    <button type="submit" 
            style="width:100%; background:var(--primary-color); color:white; padding:15px; border:none; border-radius:8px; 
                   font-size:17px; font-weight:bold; cursor:pointer; transition:background 0.3s;">
        Зарегистрироваться
    </button>
</form>

<div style="text-align:center; margin-top:30px; color:var(--secondary-color);">
    Уже есть аккаунт? 
    <a href="login.php" style="color:var(--primary-color); font-weight:600; text-decoration:none;">Войти</a>
</div>

<?php require 'footer.php'; ?>