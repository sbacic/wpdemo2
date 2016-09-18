<?php
exec("sed -i '7s/true/false/g' www/wpdemo/Config.php");
exec("sed -i '8s/true/false/g' www/wpdemo/Config.php");
exec('docker exec wpdemo sh -c "php wpdemo/wpdemo.phar --purge --config=wp-config.php"');
$I = new AcceptanceTester($scenario);
$I->wantTo('get an error message when there are no free demo instances');
$I->amOnPage('http://localhost:8080');
$I->see('No free demo sessions available');
