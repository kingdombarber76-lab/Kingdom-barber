# Deploying this Laravel project to cPanel

Follow these steps when uploading the project to a cPanel account.

1) Prepare files locally
 - Ensure `composer install --no-dev --optimize-autoloader` has been run locally or plan to run Composer on the server (some cPanel hosts provide Composer).
 - Build any frontend assets if needed.

2) Upload files to cPanel
 - Recommended: Upload the entire repository to a folder outside `public_html` (e.g. `~/laravel_app`) and copy the contents of the `public/` directory into `~/public_html`.
 - Alternative (if you must place the whole app inside `public_html`): keep files as-is but you'll need to edit `public_html/index.php` paths accordingly.

3) public_html/index.php
 - This repository includes `public_html/index.php` which attempts multiple relative paths for `vendor/autoload.php` and `bootstrap/app.php`.
 - If your layout differs, edit `public_html/index.php` and set the correct relative path to `vendor/autoload.php` and `bootstrap/app.php`.

4) Environment (.env)
 - Copy `.env.example.cpanel` to `.env` in your project root and set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `APP_URL` to the values provided by cPanel (or your hosting provider).
 - Generate an application key: run `php artisan key:generate` (if you have SSH access) or generate locally and paste into `.env`.

5) Database
 - Use cPanel's MySQL Databases to create a database and user. Assign the user to the database with ALL PRIVILEGES.
 - Import migrations: if you have SSH, run `php artisan migrate --force`. Otherwise export the database from your local environment and import via phpMyAdmin.

6) Permissions
 - Ensure `storage/` and `bootstrap/cache` are writable by the web server. Typical permissions:
   - `find storage -type d -exec chmod 0755 {} \;`
   - `find storage -type f -exec chmod 0644 {} \;`
   - `chmod -R 0755 bootstrap/cache`
 - If your host prevents `php artisan storage:link`, copy `storage/app/public` contents into `public_html/storage` instead and update references.

7) Composer and PHP version
 - Set PHP version to 8.1+ in cPanel's "Select PHP Version" or MultiPHP Manager.
 - If Composer is available via SSH, run `composer install --no-dev --optimize-autoloader` in the project root.

8) Queue, Scheduler, and Cron
 - If you use Laravel Scheduler, add a cron job: `* * * * * php /home/username/laravel_app/artisan schedule:run >> /dev/null 2>&1`.
 - For queue workers, use Supervisor if supported, or use `php artisan queue:work` via screen/cron.

9) Troubleshooting
 - If you see "Autoloader not found" adjust `public_html/index.php` paths.
 - Check `storage/logs/laravel.log` for runtime errors.

If you want, I can also: (a) adjust `public_html` further to include `agenda.html`, `config.js`, and other public assets; (b) create a small script to validate vendor/bootstrap paths automatically on first load.
