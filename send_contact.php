<?php
$page_title = "Отправка сообщения";
require 'header.php';

//  обработка формы 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    //  валидация
    $errors = [];
    if (empty($name)) $errors[] = "Укажите ваше имя";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Укажите корректный email";
    if (empty($message)) $errors[] = "Напишите сообщение";

    if (empty($errors)) {
        $success = true;
    }
}
?>

<div
    style="max-width:600px; margin:0 auto; background:var(--card-bg); padding:30px; border-radius:12px; box-shadow:var(--shadow);">

    <?php if (isset($success) && $success): ?>
        <div style="text-align:center; padding:20px;">
            <i class="fas fa-check-circle" style="font-size:48px; color:var(--success-color); margin-bottom:20px;"></i>
            <h2 style="color:var(--success-color); margin-bottom:15px;">Сообщение отправлено!</h2>
            <p style="color:var(--text-color); margin-bottom:10px;">Спасибо, <strong><?= $name ?></strong>! Ваше сообщение
                успешно отправлено.</p>
            <p style="color:var(--secondary-color); margin-bottom:25px;">Мы ответим вам на email:
                <strong><?= $email ?></strong> в ближайшее время.
            </p>

            <div style="margin-top:30px;">
                <a href="contacts.php"
                    style="background:var(--primary-color); color:white; padding:12px 24px; border-radius:8px; text-decoration:none; margin-right:10px;">
                    <i class="fas fa-arrow-left"></i> Вернуться к контактам
                </a>
                <a href="index.php"
                    style="background:var(--secondary-color); color:white; padding:12px 24px; border-radius:8px; text-decoration:none;">
                    <i class="fas fa-home"></i> На главную
                </a>
            </div>
        </div>

    <?php elseif (isset($errors) && !empty($errors)): ?>
        <div style="text-align:center; padding:20px;">
            <i class="fas fa-exclamation-triangle"
                style="font-size:48px; color:var(--warning-color); margin-bottom:20px;"></i>
            <h2 style="color:var(--warning-color); margin-bottom:15px;">Ошибка отправки</h2>

            <div
                style="background:var(--card-bg); border:1px solid var(--danger-color); border-radius:8px; padding:15px; margin-bottom:25px;">
                <ul style="text-align:left; color:var(--danger-color); margin:0; padding-left:20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <a href="contacts.php"
                style="background:var(--primary-color); color:white; padding:12px 24px; border-radius:8px; text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Вернуться к форме
            </a>
        </div>

    <?php else: ?>
        <div style="text-align:center; padding:20px;">
            <i class="fas fa-envelope" style="font-size:48px; color:var(--primary-color); margin-bottom:20px;"></i>
            <h2 style="color:var(--text-color); margin-bottom:15px;">Отправка сообщения</h2>
            <p style="color:var(--secondary-color);">Эта страница должна открываться только после отправки формы.</p>

            <div style="margin-top:30px;">
                <a href="contacts.php"
                    style="background:var(--primary-color); color:white; padding:12px 24px; border-radius:8px; text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Вернуться к контактам
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require 'footer.php'; ?>