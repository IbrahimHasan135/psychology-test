@echo off
set "PATH=%~dp0.local-tools\node;C:\xampp3\php;%PATH%"
cd /d "%~dp0"
echo Terminal lokal NovaBase: PHP, Node dan npm siap.
echo Jalankan: php artisan serve --host=127.0.0.1 --port=8001
echo Composer: php -d extension=zip composer.phar install
cmd /k
