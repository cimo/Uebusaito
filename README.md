Uebusaito
==============

This is a open source cms with symfony framework

| Features |
|:---|
| Full responsive (smartphone, tablet, pc) |
| Multibrowser (Chrome, firefox, internet explorer, opera, safari) |
| Dynamic multilanguage |
| Registration (private / company) and recover password |
| Session check time |
| Credit and paypal payment |
| Extend with module system |

| Control panel |
|:---|
| Profile management |
| Payments management |
| Pages create and management |
| Users create and management |
| Modules create and management |
| Roles create and management |
| Settings management |

## Images
<img src="screenshots/1.jpg" width="200" alt="1"/>

## Instructions:
1) On linux, open terminal and write:

	sudo curl -LsS http://symfony.com/installer -o /usr/local/bin/symfony
	
	sudo chmod a+x /usr/local/bin/symfony
	
	sudo symfony new /YOUR_PATH/symfony_2.8_fw 2.8
	
	sudo chown -R YOUR_USER:www-data /YOUR_PATH/symfony_2.8_fw
	
	sudo chmod 775 /YOUR_PATH/symfony_2.8_fw

2) Download this git and copy <b>"symfony_2.8_fw"</b> content in <b>"/YOUR_PATH/symfony_2.8_fw"</b> (Replace all).

3) Insert in your mysql database <b>"/symfony_2.8_fw/src/ReinventSoftware/UebusaitoBundle/uebusaito.sql"</b>.

4) On linux, open terminal and write:

	cd /YOUR_PATH/symfony_2.8_fw
	
	sudo rm -R src/AppBundle
	
	sudo chmod 775 src
	
	sudo chmod 664 app/config/routing.yml
	
	sudo chmod 664 app/AppKernel.php
	
	sudo chmod 775 web/bundles
	
	sudo chmod -R 775 app/cache
	
	sudo chmod -R 775 app/logs
	
	sudo -u www-data php app/console cache:clear --env=dev
	
	sudo -u www-data php app/console cache:clear --env=prod
	
	sudo -u www-data php app/console assets:install --symlink --relative
	
	sudo -u www-data php app/console server:run YOUR_IP:80

5) Go on your browser and write <b>"https://YOUR_IP/symfony_2.8_fw/web/app_dev.php"</b>

6) For admin login use <b>"user_1, Password1"</b>.

<b>By CIMO - www.reinventsoftware.org</b>