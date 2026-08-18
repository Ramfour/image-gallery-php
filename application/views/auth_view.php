<?php 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <main class="login-main">
        <form class="login-card" method="post" action="/?url=auth/login">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" required>

            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required>

            <label class="login-card__remember">
                <input type="checkbox" name="remember"> Запомнить меня
            </label>

            <button type="submit">Войти</button>
        </form>
        <nav class="login-links">
            <a href="#" class="login-links__forgot">Забыли пароль?</a>
            <a href="/?url=auth/register" class="login-links__register">У вас нет аккаунта?</a>
            <?php if (isset($error)): ?>
                <a style="color: red;"><?= htmlspecialchars($error) ?></a>
            <?php endif; ?>
        </nav>
    </main>
    <footer>

    </footer>
</body>
</html>