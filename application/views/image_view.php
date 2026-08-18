<?php /** @var array $image */ /** @var array $comments */ /** @var string|null $error */ ?>

<div class="container py-4">
    <a href="/?url=gallery" class="btn btn-outline-secondary btn-sm mb-3">← Назад в галерею</a>

    <div class="text-center mb-4">
        <img src="/uploads/<?= htmlspecialchars($image['filename']) ?>"
             class="img-fluid rounded shadow"
             style="max-height:500px;"
             alt="<?= htmlspecialchars($image['filename']) ?>">
        <div class="mt-2 text-muted small">
            Загрузил: <strong><?= htmlspecialchars($image['login']) ?></strong>
            &mdash; <?= date('d.m.Y H:i', strtotime($image['created_at'])) ?>
        </div>

        <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $image['user_id']): ?>
            <form method="post" action="/?url=gallery/delete" class="mt-2"
                  onsubmit="return confirm('Удалить фото и все комментарии к нему?')">
                <input type="hidden" name="image_id" value="<?= $image['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Удалить фото</button>
            </form>
        <?php endif; ?>
    </div>

    <hr>

    <h5>Комментарии (<?= count($comments) ?>)</h5>

    <?php if (empty($comments)): ?>
        <p class="text-muted">Комментариев пока нет.</p>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($comments as $c): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong><?= htmlspecialchars($c['login']) ?></strong>
                            <span class="text-muted small ms-2">
                                <?= date('d.m.Y H:i', strtotime($c['created_at'])) ?>
                            </span>
                            <div><?= htmlspecialchars($c['text']) ?></div>
                        </div>
                        <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $c['user_id']): ?>
                            <form method="post" action="/?url=gallery/deletecomment" class="ms-2 flex-shrink-0">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="image_id"   value="<?= $image['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Удалить комментарий?')">✕</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <h6>Оставить комментарий</h6>
        <?php if ($error): ?>
            <div class="alert alert-danger py-1"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/?url=gallery/view&id=<?= $image['id'] ?>">
            <div class="mb-2">
                <textarea name="text" class="form-control" rows="3"
                          placeholder="Ваш комментарий..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Отправить</button>
        </form>
    <?php else: ?>
        <p class="text-muted"><a href="/?url=auth">Войдите</a>, чтобы оставить комментарий.</p>
    <?php endif; ?>
</div>
