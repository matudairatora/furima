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
    ```
5. `php artisan key:generate`
6. `php artisan migrate:fresh`
7. `php artisan db:seed`
8. `php artisan storage:link`
9. `exit`
10. `sudo chmod -R 777 *`
### 使用技術
- PHP 8.0
- Laravel 10.0
- MySQL 8.0

### ER図
- ![ER図](image/ER.png)
### URL
- 開発環境 http://localhost/
- phpMyAdmin http://localhost:8080/