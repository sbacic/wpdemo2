<?php
// Here you can initialize variables that will be available to your tests
$host = exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' wpdemo_db");
exec("docker exec wpdemo sh -c 'chmod +777 -Rf /var/www/html'");
exec('composer build -q');
exec('rm -rf www/wpdemo');
exec('mkdir -p www/wpdemo');
exec('cp build/wpdemo.phar www/wpdemo/wpdemo.phar');
exec("mysql -uroot -pexample -h$host -e 'DROP DATABASE wordpress; CREATE DATABASE wordpress; USE wordpress;' wordpress");
exec("mysql -uroot -pexample -h$host wordpress < dumps/wordpress.sql");
exec("www/wpdemo/wpdemo.phar --setup --config=www/wp-config.php --host=$host");
exec("mysql -uroot -pexample -h$host -e 'DROP DATABASE wordpress; CREATE DATABASE wordpress; USE wordpress;' wordpress");
exec("sed -i 's/15/1/' www/wpdemo/Config.php");
