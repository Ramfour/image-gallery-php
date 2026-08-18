<?php /** @var array $images */ ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Галерея</h1>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="/?url=gallery/upload" class="btn btn-primary">Загрузить фото</a>
        <?php else: ?>
            <a href="/?url=auth" class="btn btn-outline-primary">Войдите, чтобы загружать фото</a>
        <?php endif; ?>
    </div>

    <?php if (empty($images)): ?>
        <p class="text-muted">Пока нет ни одного изображения.</p>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($images as $img): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <a href="/?url=gallery/view&id=<?= $img['id'] ?>">
                            <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>"
                                 class="card-img-top"
                                 style="height:200px; object-fit:cover;"
                                 alt="<?= htmlspecialchars($img['filename']) ?>">
                        </a>
                        <div class="card-body p-2">
                            <small class="text-muted">
                                <?= htmlspecialchars($img['login']) ?> &mdash;
                                <?= date('d.m.Y H:i', strtotime($img['created_at'])) ?>
                            </small>
                        </div>
                        <?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
                            <div class="card-footer p-2 text-end">
                                <form method="post" action="/?url=gallery/delete"
                                      onsubmit="return confirm('Удалить фото?')">
                                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
