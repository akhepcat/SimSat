To utilize this Web-PHP front-end:

1. `SimSat` script must be located at `/usr/local/SimSat/SimSat`
  * Most easily accomplished via `$ sudo cd /usr/local && git clone https://github.com/akhepcat/SimSat.git`
2. Install Apache2 and PHP5
  * `$ sudo apt-get install apache2 php5 libapache2-mod-php5`
3. Copy or move `index.php` to `/var/www/html` and verify it is readable/executable by the www-data user
4. Add the following lines to sudoers (run `$ visudo`)

```
Defaults        env_keep += "DELAY"
Defaults        env_keep += "LOSS"
Defaults        env_keep += "CORRUPT"
Defaults        env_keep += "RATE"
www-data ALL = NOPASSWD: /usr/local/SimSat/SimSat
```
