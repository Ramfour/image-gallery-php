<?php /** @var string $content_view */ ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <title>Главная</title>
</head>

<body>
<nav>
    <a href="/">Главная</a> |
    <a href="/?url=about">О нас</a> |
    <a href="/?url=contacts">Контакты</a>
</nav>
<hr>
<?php include 'application/views/' . $content_view; ?></body>

</html>