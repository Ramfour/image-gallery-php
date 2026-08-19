<?php

namespace App\Controllers;

use App\Core\Controller;

class ControllerGallery extends Controller
{
    // GET /?url=gallery — показать галерею
    public function actionIndex(): void
    {
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
    public function actionView(): void
    {
        $imageId = (int)($_GET['id'] ?? 0);
        if (!$imageId) {
            header('Location: /?url=gallery');
            exit;
        }

        // загружаем фото
        $stmt = $this->pdo->prepare(
            'SELECT images.*, users.login FROM images
             JOIN users ON images.user_id = users.id
             WHERE images.id = ?'
        );
        $stmt->execute([$imageId]);
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
                $stmt->execute([$imageId, $_SESSION['user_id'], $text]);
                header('Location: /?url=gallery/view&id=' . $imageId);
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
        $stmt->execute([$imageId]);
        $comments = $stmt->fetchAll();

        $this->view->generate('image_view.php', 'template_view.php', [
            'image'    => $image,
            'comments' => $comments,
            'error'    => $error,
        ]);
    }

    // POST /?url=gallery/deletecomment — удалить свой комментарий
    public function actionDeletecomment(): void
    {
        $this->requireAuth();

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $imageId   = (int)($_POST['image_id'] ?? 0);

        if ($commentId) {
            // только свой комментарий
            $stmt = $this->pdo->prepare(
                'DELETE FROM comments WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([$commentId, $_SESSION['user_id']]);
        }

        header('Location: /?url=gallery/view&id=' . $imageId);
        exit;
    }

    // POST /?url=gallery/delete — удаление фото (только владелец)
    public function actionDelete(): void
    {
        $this->requireAuth();

        $imageId = (int)($_POST['image_id'] ?? 0);
        if (!$imageId) {
            header('Location: /?url=gallery');
            exit;
        }

        // проверяем что это фото именно этого пользователя
        $stmt = $this->pdo->prepare('SELECT filename, user_id FROM images WHERE id = ?');
        $stmt->execute([$imageId]);
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
        $stmt->execute([$imageId]);

        header('Location: /?url=gallery');
        exit;
    }

    // GET  /?url=gallery/upload — форма загрузки
    // POST /?url=gallery/upload — обработка загрузки
    public function actionUpload(): void
    {
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
