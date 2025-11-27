</main>
<footer style="background:var(--header-bg); color:var(--header-text); text-align:center; padding:20px; margin-top:40px;">
    <div class="container">
        &copy; <?= date('Y') ?> better. Все права защищены.
    </div>
</footer>

<style>
    /* Simple and reliable footer solution */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1 0 auto;
        width: 100%;
    }

    footer {
        flex-shrink: 0;
        background: var(--header-bg);
        color: var(--header-text);
        text-align: center;
        padding: 20px;
        width: 100%;
    }

    /* Мобильная адаптивность для футера */
    @media (max-width: 768px) {
        footer {
            padding: 15px;
            font-size: 0.9em;
        }
    }
    
    @media (max-width: 480px) {
        footer {
            padding: 12px;
            font-size: 0.85em;
        }
    }
</style>

<script>
// Управление темной темой
const toggleSwitch = document.querySelector('#checkbox');
const currentTheme = localStorage.getItem('theme');

// Устанавливаем текущую тему
if (currentTheme) {
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    if (currentTheme === 'dark') {
        toggleSwitch.checked = true;
        updateThemeIcon('dark');
    }
}

// Функция переключения темы
function switchTheme(e) {
    if (e.target.checked) {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        updateThemeIcon('dark');
    } else {
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        updateThemeIcon('light');
    }
}

// Функция обновления иконки темы
function updateThemeIcon(theme) {
    const themeIcon = document.querySelector('.theme-icon i');
    if (theme === 'dark') {
        themeIcon.className = 'fas fa-sun';
    } else {
        themeIcon.className = 'fas fa-moon';
    }
}

// Слушатель события для переключателя
toggleSwitch.addEventListener('change', switchTheme);

// Автоматическое определение системной темы
if (!currentTheme) {
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    if (prefersDarkScheme.matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
        toggleSwitch.checked = true;
        updateThemeIcon('dark');
        localStorage.setItem('theme', 'dark');
    }
}
</script>
</body>
</html>