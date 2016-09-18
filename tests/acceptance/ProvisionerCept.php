<?php

exec("sed -i '8s/false/true/g' www/wpdemo/Config.php");
exec('docker exec wpdemo sh -c "php wpdemo/wpdemo.phar --purge --config=wp-config.php"');
exec('docker exec wpdemo sh -c "php wpdemo/wpdemo.phar --populate --config=wp-config.php"');

$I = new AcceptanceTester($scenario);
$I->wantTo('manually get a demo session and login to the Wordpress Dashboard');
$I->amOnPage('http://localhost:8080/wpdemo/provisioner.php');
$I->amOnPage('http://localhost:8080/wp-login.php');
$I->see('Username or Email');
$I->fillField('#user_login', 'root');
$I->fillField('#user_pass', 'pass');
$I->click('#wp-submit');
$I->see('Dashboard');
