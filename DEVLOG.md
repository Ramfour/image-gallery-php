# Лог разработки — 25.9 Image Gallery

## Что сделано

### 1. Структура проекта

Взят MVC каркас из `24.5.1-mvc-visit-card-site` как основа.
Скопированы: роутер, core-классы, bootstrap, структура папок controllers/models/views.

### 2. БД на VM

PostgreSQL 14 поставлен на Ubuntu VM:
```bash
sudo apt install postgresql php8.1-pgsql -y
```

Создана БД `image-gallery-php`, пользователь `postgres`, пароль `postgres`.

Три таблицы созданы через DBeaver:
- `users` — пользователи (id, login, password_hash)
- `images` — загруженные фото (id, filename, user_id, created_at)
- `comments` — комментарии к фото (id, image_id, user_id, text, created_at)

Внешние ключи с `ON DELETE CASCADE` — при удалении фото комментарии удаляются автоматически БД.

### 3. application/core/db.php — подключение PDO

```php
return new PDO(
    'pgsql:host=localhost;dbname=image-gallery-php',
    'postgres',
    'postgres',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
```

**Разбор параметров:**

`'pgsql:host=localhost;dbname=image-gallery-php'` — строка подключения (DSN):
- `pgsql:` — драйвер, говорит PDO что работаем с PostgreSQL
- `host=localhost` — сервер БД (на VM это локальный хост)
- `dbname=image-gallery-php` — имя базы данных

Два `'postgres'` — это разные вещи:
- первый `'postgres'` — **имя пользователя** PostgreSQL
- второй `'postgres'` — **пароль** этого пользователя (установили командой `ALTER USER postgres PASSWORD 'postgres'`)

Если бы пользователь был `gallery_user` с паролем `secret123`:
```php
return new PDO('pgsql:...', 'gallery_user', 'secret123', [...]);
```

**Атрибуты (четвёртый параметр — массив настроек):**

`PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
— режим обработки ошибок. Без этого если SQL-запрос упадёт,
PDO тихо вернёт `false` и ты будешь гадать почему пусто.
С этим флагом — выбрасывает исключение с текстом ошибки.
Пример: опечатка в имени таблицы → сразу видишь "table users2 does not exist".

`PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
— формат возвращаемых данных. По умолчанию `fetchAll()` возвращает
каждую строку дважды: и по имени колонки, и по числовому индексу:
```php
// без FETCH_ASSOC:
$row['login']  // есть
$row[0]        // тоже есть — дублирование
```
С `FETCH_ASSOC` — только по имени:
```php
$row['login']  // есть
$row[0]        // нет
```
Чище и экономит память при больших выборках.

---

## Следующие шаги

- [ ] Написать `config.php` (константы размера файлов, типов, пути)
- [ ] Авторизация: `controller_auth.php` + `login_view.php`
- [ ] Загрузка фото + галерея
- [ ] Комментарии
- [ ] Удаление фото и комментариев
- [ ] Bootstrap верстка
- [ ] Дамп БД в репозиторий
