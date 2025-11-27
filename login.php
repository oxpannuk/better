<?php
$page_title = "Вход в аккаунт";
require 'header.php';

// Если уже авторизован — сразу на главную
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_POST) {
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
?>

<h2 style="text-align:center; margin-bottom:30px; color:var(--text-color);">Вход в аккаунт</h2>

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
               placeholder="Введите логин" autocomplete="username"
               style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
    </div>

    <div style="margin-bottom:25px;">
        <label style="display:block; margin-bottom:8px; font-weight:600; color:var(--text-color);">Пароль</label>
        <input type="password" name="password" required
               placeholder="Введите пароль" autocomplete="current-password"
               style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; font-size:16px; background:var(--card-bg); color:var(--text-color);">
    </div>

    <button type="submit" 
            style="width:100%; background:var(--primary-color); color:white; padding:15px; border:none; border-radius:8px; 
                   font-size:17px; font-weight:bold; cursor:pointer; transition:background 0.3s;">
        Войти
    </button>
</form>

<div style="text-align:center; margin-top:30px; color:var(--secondary-color);">
    Нет аккаунта? 
    <a href="register.php" style="color:var(--primary-color); font-weight:600; text-decoration:none;">Зарегистрироваться →</a>
</div>

<?php require 'footer.php'; ?>