<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="container py-4" style="max-width:500px;">
    <h1 class="h4 mb-4">Загрузить фото</h1>
    <form method="post" action="/?url=gallery/upload" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="image" class="form-label">Файл (JPEG, PNG, GIF, макс. 5 МБ)</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Загрузить</button>
    </form>
    <a href="/?url=gallery" class="d-block mt-3 text-center">← Назад в галерею</a>
</div>
