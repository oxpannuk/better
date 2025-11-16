<?php
$page_title = "Вход в аккаунт";
require 'header.php';

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user'; // ← НОВАЯ СТРОКА
        header("Location: index.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>

<h2>Вход в аккаунт</h2>

<?php if ($error): ?>
    <div style="color:#e74c3c; background:#fadad8; padding:12px; border-radius:8px; margin:15px 0; text-align:center;">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" style="max-width:400px; margin:0 auto;">
    <div style="margin-bottom:15px;">
        <label style="display:block; margin-bottom:5px; font-weight:500;">Логин</label>
        <input type="text" name="username" placeholder="Введите логин" required 
               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
    </div>
    <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:5px; font-weight:500;">Пароль</label>
        <input type="password" name="password" placeholder="Введите пароль" required 
               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:16px;">
    </div>
    <button type="submit" 
            style="width:100%; background:#27ae60; color:white; padding:14px; border:none; border-radius:8px; font-size:16px; cursor:pointer; font-weight:bold;">
        Войти
    </button>
</form>

<p style="text-align:center; margin-top:20px;">
    Нет аккаунта? <a href="register.php" style="color:#3498db; text-decoration:none;">Зарегистрироваться</a>
</p>

<?php require 'footer.php'; ?>