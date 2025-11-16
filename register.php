<?php
$page_title = "better — Регистрация";
require 'header.php';
?>
<h2>Регистрация</h2>
<form method="POST" action="auth.php">
    <input type="hidden" name="action" value="register">
    <div style="margin-bottom:15px;">
        <label>Логин</label>
        <input type="text" name="username" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
    </div>
    <div style="margin-bottom:15px;">
        <label>Пароль</label>
        <input type="password" name="password" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
    </div>
    <button type="submit" class="btn">Зарегистрироваться</button>
</form>
<?php require 'footer.php'; ?>