<?php
// includes/header.php
function renderHeader($title = "Samsung") {
    ?>
    <!DOCTYPE html>
    <html lang="uk">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header class="site-header">
            <div class="container">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div class="brand"><?php echo $title; ?></div>
                    <nav class="nav">
                        <ul>
                            <li><a href="index.html">Головна</a></li>
                            <li><a href="admin.php">Адмін</a></li>
                            <li><a href="smartphones.html">Смартфони</a></li>
                            <li><a href="house.html">Побутова техніка</a></li>
                            <li><a href="tv.html">Телевізори</a></li>
                            <li><a href="shares.html">Акції</a></li>
                            <li><a href="support.html">Підтримка</a></li>
                            <li><a href="about.html">Про нас</a></li>
                            <li><a href="survey.php">Анкета</a></li>
                            <li><a href="jokes.html">Анекдоти</a></li>
                            <li><a href="characters.html">Персонажі</a></li>
                            
                        </ul>
                    </nav>
                </div>
            </div>
        </header>

        <main class="container">
    <?php
}
?>