# Лог разработки — 25.9 Image Gallery

Учебный проект в рамках курса SkillFactory PHP, модуль 25.9.
Задача: галерея изображений с авторизацией, загрузкой фото и комментариями.

---

## Шаг 1 — Основа: MVC каркас

За основу взят готовый MVC-скелет из модуля 24.5.1 (`mvc-visit-card-site`).
Скопированы: роутер, core-классы (Controller, Model, View), bootstrap, структура папок.

Лишние файлы от визитки удалены:
- `controller_about.php`, `controller_contacts.php`
- `about_view.php`, `contacts_view.php`, `view_portfolio.php`
- `phpinfo.php`

---

## Шаг 2 — БД на VM

PostgreSQL 14 поставлен на Ubuntu VM:
```bash
sudo apt install postgresql php8.1-pgsql -y
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"
sudo -u postgres psql -c "CREATE DATABASE \"image-gallery-php\";"
```

Три таблицы созданы через DBeaver:

```sql
create table users(
    id serial primary key,
    login varchar(30) not null unique,
    password_hash varchar(255) not null
);

create table images(
    id serial primary key,
    filename varchar(255) not null unique,
    user_id int references users(id) on delete cascade,
    created_at timestamp not null default now()
);

create table comments(
    id serial primary key,
    image_id int references images(id) on delete cascade,
    user_id int references users(id) on delete cascade,
    text text not null,
    created_at timestamp not null default now()
);
```

`ON DELETE CASCADE` — при удалении фото комментарии удаляются автоматически на уровне БД.

---

## Шаг 3 — db.php: подключение PDO

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

`ERRMODE_EXCEPTION` — при ошибке SQL бросает исключение вместо тихого `false`.
`FETCH_ASSOC` — `fetchAll()` возвращает строки только по именам колонок, без дублирования по индексу.

---

## Шаг 4 — config.php

Константы вынесены в корень проекта (требование задания на 5 баллов):

```php
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('DB_HOST', 'localhost');
```

---

## Шаг 5 — Исправления в core-классах

### route.php
Оригинальный роутер брал из URL только имя контроллера, action всегда был `index`.
URL вида `/?url=auth/login` не вызывал `action_login()`.

Исправление — парсинг URL через `explode('/', $routes)`:
```php
$parts = explode('/', $routes);
$controller_name = $parts[0];       // 'auth'
$action_name = $parts[1] ?? 'index'; // 'login'
```

### View.php
Метод `generate()` принимал `$data` но не передавал его в шаблон.
Исправление — `extract($data)` разворачивает массив в переменные:
```php
if (is_array($data)) {
    extract($data); // ['error' => 'текст'] → $error = 'текст'
}
```

### Controller.php
Добавлены:
- `protected $pdo` — PDO берётся из `$GLOBALS['pdo']` (создаётся один раз в bootstrap)
- `requireAuth()` — редирект на `/auth` если пользователь не залогинен

**Проблема которая возникла:** изначально PDO подключался через `require_once` внутри конструктора.
Но `bootstrap.php` уже подключал `db.php` через `require_once`, поэтому повторный вызов возвращал `true` вместо PDO объекта → `Call to a member function query() on bool`.

Решение: PDO создаётся один раз в `bootstrap.php` как `$pdo`, в Controller берётся через `$GLOBALS['pdo']`.

---

## Шаг 6 — Авторизация

Файлы: `controller_auth.php`, `auth_view.php`, `register_view.php`

**action_register() — POST /?url=auth/register:**
1. Проверка совпадения паролей
2. `SELECT` — проверка что логин не занят
3. `password_hash($password, PASSWORD_BCRYPT)` — хеширование пароля
4. `INSERT INTO users ... RETURNING id` — PostgreSQL-специфичный синтаксис получения id новой записи
5. Сохранение `$_SESSION['user_id']` и `$_SESSION['login']`
6. Редирект на главную

**action_login() — POST /?url=auth/login:**
1. `SELECT * FROM users WHERE login = ?`
2. `password_verify($password, $hash)` — проверка пароля против хеша
3. Одинаковое сообщение при неверном логине и неверном пароле — защита от перебора
4. Опциональная кука "запомнить меня": хеш от `user_id + IP + SECRET_KEY`

**action_logout():** `session_destroy()` + удаление куки + редирект

---

## Шаг 7 — Галерея и загрузка фото

Файлы: `controller_gallery.php`, `gallery_view.php`, `upload_view.php`

**action_index() — галерея:**
```sql
SELECT images.*, users.login FROM images
JOIN users ON images.user_id = users.id
ORDER BY images.created_at DESC
```
JOIN нужен чтобы показать логин автора рядом с фото.

**action_upload() — загрузка:**
1. Проверка `$_FILES['image']['error']` — коды ошибок PHP при загрузке
2. Проверка размера через `MAX_FILE_SIZE`
3. Проверка MIME-типа через `ALLOWED_TYPES`
4. `uniqid('img_', true)` — уникальное имя файла (без коллизий)
5. `move_uploaded_file()` — перемещает из `/tmp` в `uploads/`
6. `INSERT INTO images (filename, user_id)`

**action_delete() — удаление фото:**
1. `SELECT` — проверяем что `user_id` совпадает с `$_SESSION['user_id']`
2. `unlink()` — удаляет файл с диска
3. `DELETE FROM images WHERE id = ?` — комментарии удалятся каскадно

---

## Шаг 8 — Страница фото + комментарии

Файлы: `image_view.php`, `action_view()` и `action_deletecomment()` в `controller_gallery.php`

Комментарии привязаны к странице фото (`/?url=gallery/view&id=N`), а не в отдельный контроллер — роутер поддерживает только один уровень вложенности (`controller/action`), параметр `id` передаётся через `$_GET`.

**Добавление комментария:** POST на ту же страницу → INSERT → редирект (паттерн PRG — Post/Redirect/Get, предотвращает повторную отправку при F5).

**Удаление:** `DELETE FROM comments WHERE id = ? AND user_id = ?` — условие `user_id` гарантирует что нельзя удалить чужой комментарий, даже зная его `id`.

---

## Шаг 9 — Bootstrap 5 + навигация

`template_view.php` переписан с Bootstrap 5 CDN.
Навигация показывает разные пункты в зависимости от `$_SESSION['user_id']`:
- Не залогинен: «Войти», «Регистрация»
- Залогинен: имя пользователя, «Загрузить фото», «Выйти»

---

## Деплой на VM — проблемы и решения

### Проблема 1: относительные пути
На Windows `require_once '../config.php'` работал — PHP запускался из папки `application/`.
На nginx PHP запускается из корня сайта, путь ломался.

**Решение:** заменить все пути на абсолютные через `__DIR__` и `$_SERVER['DOCUMENT_ROOT']`.

### Проблема 2: регистр имён файлов
На Windows файловая система регистронезависимая: `model.php` и `Model.php` — одно и то же.
На Linux (Ubuntu) — разные файлы. `require_once 'core/model.php'` падал с "No such file".

**Решение:** привести все имена в `require_once` к точному регистру: `Model.php`, `View.php`, `Controller.php`.

### Проблема 3: PHP-FPM сокет
nginx-конфиг по умолчанию использует unix-сокет `/run/php/php8.1-fpm.sock`.
На этой VM PHP-FPM настроен на TCP: `listen = 127.0.0.1:9003`.

**Решение:** в nginx-конфиге заменить:
```nginx
# было
fastcgi_pass unix:/run/php/php8.1-fpm.sock;
# стало
fastcgi_pass 127.0.0.1:9003;
```

### Проблема 4: таблицы не созданы
БД `image-gallery-php` существовала, но таблицы в ней не были созданы (DBeaver был подключён к другой БД или изменения не сохранились).

**Решение:** создать таблицы вручную через `psql`:
```bash
sudo -u postgres psql -d image-gallery-php -c "CREATE TABLE IF NOT EXISTS users(...);"
```
