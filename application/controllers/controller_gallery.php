<?php
/** @see Controller */
class Controller_Gallery extends Controller {

    // GET /?url=gallery — показать галерею
    public function action_index() {
        $stmt = $this->pdo->query(
            'SELECT images.*, users.login FROM images
             JOIN users ON images.user_id = users.id
             ORDER BY images.created_at DESC'
        );
        $images = $stmt->fetchAll();
        $this->view->generate('gallery_view.php', 'template_view.php', ['images' => $images]);
    }

    // GET  /?url=gallery/view&id=N  — страница фото + комментарии
    // POST /?url=gallery/view&id=N  — добавить комментарий
    public function action_view() {
        $image_id = (int)($_GET['id'] ?? 0);
        if (!$image_id) {
            header('Location: /?url=gallery');
            exit;
        }

        // загружаем фото
        $stmt = $this->pdo->prepare(
            'SELECT images.*, users.login FROM images
             JOIN users ON images.user_id = users.id
             WHERE images.id = ?'
        );
        $stmt->execute([$image_id]);
        $image = $stmt->fetch();
        if (!$image) {
            header('Location: /?url=gallery');
            exit;
        }

        // добавление комментария
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user_id'])) {
            $text = trim($_POST['text'] ?? '');
            if ($text === '') {
                $error = 'Комментарий не может быть пустым';
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO comments (image_id, user_id, text) VALUES (?, ?, ?)'
                );
                $stmt->execute([$image_id, $_SESSION['user_id'], $text]);
                header('Location: /?url=gallery/view&id=' . $image_id);
                exit;
            }
        }

        // загружаем комментарии
        $stmt = $this->pdo->prepare(
            'SELECT comments.*, users.login FROM comments
             JOIN users ON comments.user_id = users.id
             WHERE comments.image_id = ?
             ORDER BY comments.created_at ASC'
        );
        $stmt->execute([$image_id]);
        $comments = $stmt->fetchAll();

        $this->view->generate('image_view.php', 'template_view.php', [
            'image'    => $image,
            'comments' => $comments,
            'error'    => $error,
        ]);
    }

    // POST /?url=gallery/deletecomment — удалить свой комментарий
    public function action_deletecomment() {
        $this->requireAuth();

        $comment_id = (int)($_POST['comment_id'] ?? 0);
        $image_id   = (int)($_POST['image_id'] ?? 0);

        if ($comment_id) {
            // только свой комментарий
            $stmt = $this->pdo->prepare(
                'DELETE FROM comments WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$comment_id, $_SESSION['user_id']]);
        }

        header('Location: /?url=gallery/view&id=' . $image_id);
        exit;
    }

    // POST /?url=gallery/delete — удаление фото (только владелец)
    public function action_delete() {
        $this->requireAuth();

        $image_id = (int)($_POST['image_id'] ?? 0);
        if (!$image_id) {
            header('Location: /?url=gallery');
            exit;
        }

        // проверяем что это фото именно этого пользователя
        $stmt = $this->pdo->prepare('SELECT filename, user_id FROM images WHERE id = ?');
        $stmt->execute([$image_id]);
        $image = $stmt->fetch();

        if (!$image || $image['user_id'] != $_SESSION['user_id']) {
            header('Location: /?url=gallery');
            exit;
        }

        // удаляем файл с диска
        $filepath = UPLOAD_PATH . $image['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // удаляем запись из БД (комментарии удалятся каскадно)
        $stmt = $this->pdo->prepare('DELETE FROM images WHERE id = ?');
        $stmt->execute([$image_id]);

        header('Location: /?url=gallery');
        exit;
    }

    // GET  /?url=gallery/upload — форма загрузки
    // POST /?url=gallery/upload — обработка загрузки
    public function action_upload() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = $_FILES['image'] ?? null;

            // базовые проверки
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Ошибка загрузки файла';
            } elseif ($file['size'] > MAX_FILE_SIZE) {
                $error = 'Файл превышает максимальный размер ' . (MAX_FILE_SIZE / 1024 / 1024) . ' МБ';
            } elseif (!in_array($file['type'], ALLOWED_TYPES)) {
                $error = 'Допустимые форматы: JPEG, PNG, GIF';
            } else {
                // уникальное имя файла
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_', true) . '.' . $ext;
                $dest     = UPLOAD_PATH . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $stmt = $this->pdo->prepare(
                        'INSERT INTO images (filename, user_id) VALUES (?, ?)'
                    );
                    $stmt->execute([$filename, $_SESSION['user_id']]);
                    header('Location: /?url=gallery');
                    exit;
                } else {
                    $error = 'Не удалось сохранить файл на сервере';
                }
            }

            $this->view->generate('upload_view.php', 'template_view.php', ['error' => $error]);
            return;
        }

        $this->view->generate('upload_view.php', 'template_view.php');
    }
}
