<?php $page_title = "Контакты"; require 'header.php'; ?>

<style>
.contacts-container {
    max-width: 800px;
    margin: 0 auto;
}

.contact-list {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.contact-list li {
    margin-bottom: 15px;
    padding: 10px 0;
}

.contact-form {
    max-width: 600px;
    margin: 0 auto 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-color);
    font-weight: 500;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--card-bg);
    color: var(--text-color);
    font-size: 16px;
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.submit-btn {
    background: var(--primary-color);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
}

.social-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 20px;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.social-item {
    text-decoration: none;
    color: var(--text-color);
    padding: 15px;
    border-radius: 8px;
    transition: transform 0.3s;
}

.social-item:hover {
    transform: translateY(-5px);
}

.social-icon {
    font-size: 40px;
    color: var(--primary-color);
    margin-bottom: 10px;
}

.social-text small {
    color: var(--secondary-color);
}

/* адаптив */
@media (max-width: 768px) {
    .contacts-container {
        padding: 0 10px;
    }

    .social-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .social-icon {
        font-size: 35px;
    }

    h1 {
        font-size: 1.6em;
    }

    h2 {
        font-size: 1.3em;
    }
}

@media (max-width: 480px) {
    .social-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .social-item {
        padding: 12px;
    }

    .form-input,
    .form-textarea {
        padding: 10px;
    }

    .submit-btn {
        width: 100%;
        padding: 14px;
    }
}
</style>

<div class="contacts-container">
    <h1>Контакты</h1>
    <p>Свяжитесь с нами для вопросов, предложений или поддержки. Мы всегда рады помочь!</p>

    <h2>Наши контакты</h2>
    <ul class="contact-list">
        <li><i class="fas fa-envelope" style="color:var(--primary-color); margin-right:10px;"></i>Email: <a
                href="mailto:support@better.local" style="color:var(--primary-color);">support@better.local</a></li>
        <li><i class="fas fa-phone" style="color:var(--primary-color); margin-right:10px;"></i>Телефон: +7 800 303 35
            (пн-пт, 9:00-18:00)</li>
        <li><i class="fas fa-map-marker-alt" style="color:var(--primary-color); margin-right:10px;"></i>Адрес: г. Омск,
            ул. Гагарина, 10 </li>
    </ul>

    <h2>Форма обратной связи</h2>
    <form method="POST" action="send_contact.php" class="contact-form">
        <div class="form-group">
            <label class="form-label">Ваше имя:</label>
            <input type="text" name="name" required class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Ваш email:</label>
            <input type="email" name="email" required class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Сообщение:</label>
            <textarea name="message" rows="5" required class="form-textarea"></textarea>
        </div>
        <button type="submit" class="submit-btn">Отправить</button>
    </form>

    <h2>Мы в соцсетях</h2>
    <p style="color:var(--text-color); margin-bottom: 20px;">Присоединяйтесь к нам в социальных сетях, чтобы быть в
        курсе новостей, обновлений платформы и полезных советов по улучшению офисной жизни. Подписывайтесь,
        комментируйте и делитесь идеями!</p>

    <div class="social-grid">
        <a href="https://twitter.com/better_local" target="_blank" class="social-item">
            <i class="fab fa-twitter social-icon"></i>
            <div class="social-text">
                <p>Twitter<br><small>Новости и обновления</small></p>
            </div>
        </a>
        <a href="https://facebook.com/better.local" target="_blank" class="social-item">
            <i class="fab fa-facebook social-icon"></i>
            <div class="social-text">
                <p>Facebook<br><small>Сообщество</small></p>
            </div>
        </a>
        <a href="https://t.me/better_local" target="_blank" class="social-item">
            <i class="fab fa-telegram-plane social-icon"></i>
            <div class="social-text">
                <p>Telegram<br><small>Канал с уведомлениями</small></p>
            </div>
        </a>
        <a href="https://vk.com/better_local" target="_blank" class="social-item">
            <i class="fab fa-vk social-icon"></i>
            <div class="social-text">
                <p>VK<br><small>Группа для обсуждений</small></p>
            </div>
        </a>
    </div>

    <h2>Наша локация</h2>
    <div style="border-radius: 8px; overflow: hidden;">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2294.567890123!2d73.3773804!3d54.9873657!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x43aafe0d0f0f0f0f%3A0x0!2zNTTCsDU5JzE0LjUiTiA3M8KwMjInMzguNiJF!5e0!3m2!1sru!2sru!4v1700000000"
            width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<?php require 'footer.php'; ?>