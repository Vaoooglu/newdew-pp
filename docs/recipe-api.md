# Recipe creation API

API создаёт записи существующего типа `recipe`. Он не создаёт новые термины таксономий и принимает только изображения и видео, уже загруженные в WordPress Media Library.

## Endpoint

```text
POST /wp-json/oxboxwise/v1/recipes
Content-Type: application/json
Authorization: Bearer <API_TOKEN>
```

## Настройка доступа

В WordPress откройте **ОБЩИЕ НАСТРОЙКИ сайта → API рецептов** и заполните:

- `API token` — случайная секретная строка длиной не менее 32 символов;
- `Автор рецептов API` — пользователь WordPress, от имени которого создаются записи.

Автор должен иметь права `edit_posts` и `upload_files`. Для запросов с `status: "publish"` ему также необходимо право `publish_posts`. Настройки хранятся в ACF Options и не находятся в Git. API не возвращает и не записывает token в лог.

## Поля запроса

| Поле | Тип | Обязательное | Назначение |
|---|---|---:|---|
| `title` | string | да | `post_title` рецепта |
| `content` | string | нет | `post_content`, описание приготовления; разрешён безопасный HTML |
| `featured_media_id` | integer | нет | ID существующего image attachment |
| `recipe_video_id` | integer | нет | ID существующего video attachment, сохраняется в ACF `recipe_video` |
| `recipe_category_ids` | integer[] | нет | IDs существующих terms `recipe_category` |
| `recipe_ingredient_ids` | integer[] | нет | IDs существующих terms `recipe_ingredient` |
| `tag_ids` | integer[] | нет | IDs существующих terms `post_tag` |
| `recipe_note` | string | нет | ACF `recipe_note` |
| `recipe_cooking_time` | string | нет | ACF `recipe_cooking_time` |
| `recipe_portions` | string | нет | ACF `recipe_portions` |
| `status` | `draft` или `publish` | нет | По умолчанию `draft` |
| `external_id` | string | нет | Идентификатор запроса для защиты от повторного создания |

API отклоняет неизвестные поля. Значение `external_id` может содержать латинские буквы, цифры, `.`, `_`, `:`, `-` и иметь длину до 191 символа.

## Пример запроса

```bash
curl --request POST 'https://example.com/wp-json/oxboxwise/v1/recipes' \
  --header 'Authorization: Bearer YOUR_SECRET_TOKEN' \
  --header 'Content-Type: application/json' \
  --data '{
    "title": "Паста с томатами",
    "content": "<p>Отварите пасту и приготовьте соус.</p>",
    "featured_media_id": 123,
    "recipe_video_id": 456,
    "recipe_category_ids": [12],
    "recipe_ingredient_ids": [3, 5],
    "tag_ids": [8],
    "recipe_note": "В следующий раз добавить больше базилика.",
    "recipe_cooking_time": "40 минут",
    "recipe_portions": "4",
    "status": "draft",
    "external_id": "telegram-123456-789"
  }'
```

## Успешный ответ

Новый рецепт возвращается с HTTP `201 Created`:

```json
{
  "success": true,
  "recipe_id": 789,
  "status": "draft",
  "slug": "",
  "permalink": "https://example.com/?post_type=recipe&p=789",
  "edit_url": "https://example.com/wp-admin/post.php?post=789&action=edit",
  "duplicate": false,
  "message": "Recipe created successfully."
}
```

Повторный запрос с тем же `external_id` не создаёт запись заново. API возвращает существующий рецепт с HTTP `200 OK` и `duplicate: true`.

## Ошибки

Ошибки данных, полученные после успешной аутентификации, имеют вид:

```json
{
  "success": false,
  "error": {
    "code": "invalid_recipe_video_id",
    "message": "recipe_video_id must reference a video attachment."
  }
}
```

Основные статусы:

- `400` — тело не является JSON-объектом или содержит неизвестные поля;
- `401` — отсутствует Bearer token;
- `403` — token неверен или у настроенного автора недостаточно прав;
- `404` — указанный term или media attachment не существует;
- `409` — запрос с таким `external_id` уже обрабатывается;
- `422` — неверный тип или значение поля;
- `500` — WordPress не смог завершить создание;
- `503` — API token или автор не настроены.

Ошибки, возвращаемые `permission_callback` до запуска обработчика, используют стандартную структуру WordPress REST API:

```json
{
  "code": "recipe_api_invalid_token",
  "message": "The supplied Bearer token is invalid.",
  "data": {
    "status": 403
  }
}
```

## Media workflow

Recipe endpoint не принимает multipart-файлы. Для загрузки в медиатеку добавлен отдельный защищённый endpoint:

```text
POST /wp-json/oxboxwise/v1/media
Authorization: Bearer <API_TOKEN>
Content-Type: multipart/form-data
```

Файл передаётся в поле `file`. Успешный ответ содержит `attachment_id`, `media_type`, `mime_type` и `url`.

Внешний backend при необходимости должен:

1. получить файл из Telegram;
2. загрузить его через `/oxboxwise/v1/media` как attachment в WordPress Media Library;
3. передать image attachment ID как `featured_media_id`;
4. передать video attachment ID как `recipe_video_id`.

Endpoint проверяет существование attachment и MIME-группу `image/*`. Для видео разрешены форматы, соответствующие ACF-полю рецепта: MP4/M4V, MOV, WebM и OGV. YouTube URL в модели рецепта не используется.

## Taxonomy terms

Бот может читать существующие terms без их автоматического создания:

```text
GET /wp-json/oxboxwise/v1/terms?taxonomy=recipe_category&page=1&per_page=20
Authorization: Bearer <API_TOKEN>
```

Допустимые taxonomy: `recipe_category`, `recipe_ingredient`, `post_tag`. Поддерживаются параметры `page`, `per_page` (до 100) и `search`.

## Встроенный Telegram webhook

Для Telegram-бота внешний backend не требуется. Тема принимает обновления напрямую:

```text
POST /wp-json/oxboxwise/v1/telegram/webhook
X-Telegram-Bot-Api-Secret-Token: <WEBHOOK_SECRET>
Content-Type: application/json
```

Настройки находятся в **ОБЩИЕ НАСТРОЙКИ сайта → Telegram-бот рецептов**:

- `Bot token` — токен от BotFather;
- `Webhook secret` — случайная строка длиной 32–256 символов; можно оставить пустым, чтобы WordPress сгенерировал её автоматически;
- `Разрешённые Telegram user ID` — allowlist пользователей бота;
- `Автор рецептов API` в блоке API рецептов — WordPress-пользователь, от имени которого загружаются файлы и создаются записи.

После сохранения настроек WordPress автоматически вызывает Telegram `setWebhook` и `setMyCommands`. Сайт должен иметь публичный HTTPS-сертификат, а исходящие HTTPS-запросы к `api.telegram.org` не должны блокироваться хостингом.

Команды бота:

- `/start` — справка;
- `/newrecipe` — новый рецепт;
- `/cancel` — отмена текущего диалога;
- `/id` — показать Telegram user ID (доступна до добавления пользователя в allowlist);
- `/skip` — пропустить необязательный шаг.

Состояние незавершённого диалога хранится в WordPress transients семь дней. Фото и видео скачиваются из Telegram, проходят проверку размера/MIME и сохраняются как attachments в медиатеке. Повторно доставленные Telegram update ID не обрабатываются дважды; `external_id` дополнительно защищает от повторного создания рецепта.
