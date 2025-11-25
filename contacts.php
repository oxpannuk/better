<?php $page_title = "Контакты"; require 'header.php'; ?>
<h1>Контакты</h1>
<p>Свяжитесь с нами для вопросов, предложений или поддержки. Мы всегда рады помочь!</p>

<h2>Наши контакты</h2>
<ul style="list-style:none; padding:0;">
    <li><i class="fas fa-envelope" style="color:#3498db; margin-right:10px;"></i>Email: <a href="mailto:support@better.local">support@better.local</a></li>
    <li><i class="fas fa-phone" style="color:#3498db; margin-right:10px;"></i>Телефон: +7 800 303 35  (пн-пт, 9:00-18:00)</li>
    <li><i class="fas fa-map-marker-alt" style="color:#3498db; margin-right:10px;"></i>Адрес: г. Омск, ул. Гагарина, 10 </li>
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


<h2>Мы в соцсетях</h2>
<p>Присоединяйтесь к нам в социальных сетях, чтобы быть в курсе новостей, обновлений платформы и полезных советов по улучшению офисной жизни. Подписывайтесь, комментируйте и делитесь идеями!</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; max-width: 800px; margin: 0 auto; text-align: center;">
    <a href="https://twitter.com/better_local" target="_blank" style="text-decoration: none; color: #333;">
        <i class="fab fa-twitter" style="font-size: 48px; color: #1DA1F2;"></i>
        <p>Twitter<br><small>Новости и обновления</small></p>
    </a>
    <a href="https://facebook.com/better.local" target="_blank" style="text-decoration: none; color: #333;">
        <i class="fab fa-facebook" style="font-size: 48px; color: #1877F2;"></i>
        <p>Facebook<br><small>Сообщество</small></p>
    </a>
    <a href="https://t.me/better_local" target="_blank" style="text-decoration: none; color: #333;">
        <i class="fab fa-telegram-plane" style="font-size: 48px; color: #0088cc;"></i>
        <p>Telegram<br><small>Канал с уведомлениями</small></p>
    </a>
    <a href="https://vk.com/better_local" target="_blank" style="text-decoration: none; color: #333;">
        <i class="fab fa-vk" style="font-size: 48px; color: #4c75a3;"></i>
        <p>VK<br><small>Группа для обсуждений</small></p>
    </a>
</div>

<h2>Наша локация</h2>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2294.567890123!2d73.3773804!3d54.9873657!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x43aafe0d0f0f0f0f%3A0x0!2zNTTCsDU5JzE0LjUiTiA3M8KwMjInMzguNiJF!5e0!3m2!1sru!2sru!4v1700000000" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe><grok-card data-id="e945af" data-type="citation_card"></grok-card>

<?php require 'footer.php'; ?>