<?php
exec("sed -i '7s/false/true/g' www/wpdemo/Config.php");
exec("sed -i '8s/true/false/g' www/wpdemo/Config.php");
exec('docker exec wpdemo sh -c "php wpdemo/wpdemo.phar --purge --config=wp-config.php"');
$I = new AcceptanceTester($scenario);
$I->wantTo('test generating demo instances on the spot');
$I->amOnPage('http://localhost:8080/wp-login.php');
$I->see('Username or Email');
$I->fillField('#user_login', 'root');
$I->fillField('#user_pass', 'pass');
$I->click('#wp-submit');
$I->see('Dashboard');
