<main class="login-main">
    <form class="login-card" method="post" action="/?url=auth/register">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" required>

        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Повторите пароль</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <button type="submit">Зарегистрироваться</button>
    </form>
    <nav class="login-links">
        <a href="/?url=auth" class="login-links__login">Уже есть аккаунт?</a>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </nav>
</main>
