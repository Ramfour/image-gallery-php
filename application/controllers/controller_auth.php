<?php
/** @see Controller */
class Controller_Auth extends Controller {

    // GET /?url=auth — показать форму логина/регистрации
    // POST /?url=auth — обработать форму
    public function action_index() {
        $this->view->generate('auth_view.php', 'template_view.php');
    }

    // POST /?url=auth/login
    public function action_login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?url=auth');
            exit;
        }

        $login    = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        // 1. Ищем пользователя
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE login = ?');
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        // 2. Проверяем пароль (одно сообщение для обоих случаев — защита от перебора)
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->view->generate('auth_view.php', 'template_view.php', [
                'error' => 'Неверный логин или пароль'
            ]);
            return;
        }

        // 3. Сохраняем сессию
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['login']   = $user['login'];

        // 4. "Запомнить меня"
        if (!empty($_POST['remember'])) {
            $token = hash('sha256', $user['id'] . $_SERVER['REMOTE_ADDR'] . 'SECRET_KEY');
            setcookie('remember_token', $user['id'] . ':' . $token, time() + 30 * 24 * 3600, '/');
        }

        header('Location: /');
        exit;
    }

    // GET /?url=auth/register — показать форму регистрации
    // POST /?url=auth/register — обработать регистрацию
    public function action_register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login    = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';

            // 1. Совпадают ли пароли
            if ($password !== $confirm) {
                $this->view->generate('register_view.php', 'template_view.php', [
                    'error' => 'Пароли не совпадают'
                ]);
                return;
            }

            // 2. Занят ли логин
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE login = ?');
            $stmt->execute([$login]);
            if ($stmt->fetch()) {
                $this->view->generate('register_view.php', 'template_view.php', [
                    'error' => 'Логин уже занят'
                ]);
                return;
            }

            // 3. Сохраняем нового пользователя
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare(
                'INSERT INTO users (login, password_hash) VALUES (?, ?) RETURNING id'
            );
            $stmt->execute([$login, $hash]);
            $id = $stmt->fetchColumn();

            // 4. Авторизуем сразу после регистрации
            $_SESSION['user_id'] = (int)$id;
            $_SESSION['login']   = $login;

            header('Location: /');
            exit;
        }

        $this->view->generate('register_view.php', 'template_view.php');
    }

    // GET /?url=auth/logout
    public function action_logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
}
