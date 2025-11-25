<?php $page_title = "Контакты"; require 'header.php'; ?>
<h1>Контакты</h1>
<p>Свяжитесь с нами для вопросов, предложений или поддержки. Мы всегда рады помочь!</p>

<h2>Наши контакты</h2>
<ul style="list-style:none; padding:0;">
    <li><i class="fas fa-envelope" style="color:#3498db; margin-right:10px;"></i>Email: <a href="mailto:support@better.local">support@better.local</a></li>
    <li><i class="fas fa-phone" style="color:#3498db; margin-right:10px;"></i>Телефон: +372 555 12345 (пн-пт, 9:00-18:00)</li>
    <li><i class="fas fa-map-marker-alt" style="color:#3498db; margin-right:10px;"></i>Адрес: ул. Примерная, 10, Таллин, Эстония</li>
</ul>

<h2>Форма обратной связи</h2>
<form method="POST" action="send_contact.php" style="max-width:600px; margin:0 auto;">
    <div style="margin-bottom:15px;">
        <label>Ваше имя:</label>
        <input type="text" name="name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
    </div>
    <div style="margin-bottom:15px;">
        <label>Ваш email:</label>
        <input type="email" name="email" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
    </div>
    <div style="margin-bottom:15px;">
        <label>Сообщение:</label>
        <textarea name="message" rows="5" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></textarea>
    </div>
    <button type="submit" style="background:#3498db; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer;">Отправить</button>
</form>

<p style="margin-top:20px;">* Примечание: Создайте файл send_contact.php для обработки формы (например, отправка email через mail() или PHPMailer).</p>

<h2>Мы в соцсетях</h2>
<ul style="list-style:none; padding:0; display:flex; justify-content:center; gap:20px;">
    <li><a href="https://twitter.com/better_local" target="_blank"><i class="fab fa-twitter" style="font-size:24px; color:#1DA1F2;"></i></a></li>
    <li><a href="https://facebook.com/better.local" target="_blank"><i class="fab fa-facebook" style="font-size:24px; color:#1877F2;"></i></a></li>
    <li><a href="https://instagram.com/better_local" target="_blank"><i class="fab fa-instagram" style="font-size:24px; color:#E4405F;"></i></a></li>
</ul>

<h2>Наша локация</h2>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2030.123456789!2d24.7535!3d59.4369!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNTnCsDI2JzEyLjgiTiAyNMKwNDUnMTIuNiJF!5e0!3m2!1sru!2sru!4v1234567890" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>

<?php require 'footer.php'; ?>