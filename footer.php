</main>
<footer style="background:#2c3e50; color:white; text-align:center; padding:20px; margin-top:40px;">
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
    /* Основной контент растягивается */
    width: 100%;
}

footer {
    flex-shrink: 0;
    /* Футер не сжимается */
    background: #2c3e50;
    color: white;
    text-align: center;
    padding: 20px;
    width: 100%;
}
</style>
</body>

</html>