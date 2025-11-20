# coachtechフリマ
## 環境構築
### Dockerビルド
1. `git clone git@github.com:matudairatora/furima.git`
2. `docker-compose up -d --build`
#### Laravel環境構築
1. `docker-compose exec php bash`
2. `composer install`
3. .env.exampleファイルから.envを作成し、環境変数を変更
4. .envに以下の環境変数を追加
    ``` text
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel_db
    DB_USERNAME=laravel_user
    DB_PASSWORD=laravel_pass

    MAIL_MAILER=smtp
    MAIL_HOST=mailhog
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=hello@example.com
    MAIL_FROM_NAME="${APP_NAME}"

    ```
5. `composer require stripe/stripe-php:17.0.0`
6. `php artisan key:generate`
7. `php artisan migrate:fresh`
8. `php artisan db:seed`
9. `php artisan storage:link`
10. `exit`
11. `brew install mailhog`
12. `brew services start mailhog`
12. `sudo chmod -R 777 *`

### 一般ユーザー
1.  ユーザー名:`出品者A`
    email:`user1@example.com`
    password:`user1syuppin`
-   郵便番号：`100-0001`
    住所：`東京都千代田区千代田1-1`
    建物：`テックハイツ101`

2.  ユーザー名:`出品者B`
    email:`user2@example.com`
    password:`user2syuppin`
-   郵便番号：`530-0001`
    住所：`大阪府大阪市北区梅田2-2-2`
    建物：`グランフロント大阪202`

3.  ユーザー名:`購入専用ユーザー`
    email:`user3@example.com`
    password:`user3kounyu`
-   郵便番号：`810-0001`
    住所：`福岡県福岡市中央区天神3-3-3`
    建物：`博多ビル303`


### PHPunitテスト
1.  `php artisan config:clear`
-   2~7は、飛ばして、8に行っても大丈夫です。
2.  `php artisan test tests/Feature/AuthTest.php`
3.  `php artisan test tests/Feature/ItemTest.php`
4.  `php artisan test tests/Feature/InteractionTest.php`
5.  `php artisan test tests/Feature/PurchaseTest.php`
6.  `php artisan test tests/Feature/ProfileTest.php`
7.  `php artisan test tests/Feature/EmailVerificationTest.php`
8.  `php artisan test`

### 使用技術
- PHP 8.0
- Laravel 10.0
- MySQL 8.0
- mailhog v1.0.1
- Fortify
- stripe 17.0

### ER図
- ![ER図](src/public/img/ER図.png)
### URL
- 開発環境 http://localhost/
- phpMyAdmin http://localhost:8080/
- MailHog http://localhost:8025