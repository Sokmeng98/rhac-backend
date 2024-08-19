## Development environment setup

```bash
$ composer install
$ cp .env.example .env
$ php artisan key:generate
$ php artisan migrate
$ php artisan db:seed
$ php artisan passport:install
$ php artisan serve --host=localhost --port=8000
```

## Deployment

### Make sure all php extension are installed

run `php -m` to see if all the modules are installed

<details>
<summary>
Modules:
</summary>

```txt
[PHP Modules]
bz2
calendar
Core
ctype
curl
date
dom
exif
FFI
fileinfo
filter
ftp
gd
gettext
hash
iconv
igbinary
json
libxml
mbstring
memcached
msgpack
mysqli
mysqlnd
openssl
pcntl
pcre
PDO
pdo_mysql
Phar
posix
readline
Reflection
session
shmop
SimpleXML
sockets
sodium
SPL
standard
sysvmsg
sysvsem
sysvshm
tokenizer
xml
xmlreader
xmlwriter
xsl
Zend OPcache
zip
zlib

[Zend Modules]
Zend OPcache
```

</details>

### Make sure the dependencies installed

```bash
composer install --optimize-autoloader --no-dev
```

We use screen to serve Laravel app.

```bash
screen -d -m php artisan serve --host 0.0.0.0
```

`screen` is often installed by default on Ubuntu.
If `screen` is not installed, run:

```bash
sudo apt install screen
```

### Troubleshoot

-   If the app cannot server media file or anything in storage, run

```bash
php artisan storage:link
```

---

Read [Laravel docs](https://laravel.com/docs/10.x/deployment) for more
