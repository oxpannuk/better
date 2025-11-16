<?php
// Никакой HTML — только выход и редирект
session_start();
session_destroy();
header("Location: login.php");
exit;