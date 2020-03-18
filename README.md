Uebusaito
==============

Framework for create secure fast and dynamic website.

You can create all type of websites (Personal, company, eCommerce, ...).

| Info: |
|:---|
| Cross browser and responsive (Chrome, Firefox, Edge, Opera, Safari) |
| Cross platform (Windows, Linux, Mac, Android, Ios) |
| Encrypt data |
| Dynamic multi language |
| Login, registration, recover password and profile |
| Multiple roles system |
| Search in website |
| Multi tab block |
| Credit and paypal payment |
| Upload file chunk system |
| Wysiwyg page creation (create page without code) |
| Page comments |
| Microservice (Deploy, api, unit test, selenium and cron) |
| Integration with: Slack, line |
| Extend with module system |

| Elements: |
|:---|
| System info |
| Payments |
| Pages |
| Users |
| Modules |
| Roles |
| Settings |
| Slack |
| Line |
| Microservice |
| - Deploy |
| - Api |
| - Unit test |
| - Selenium (Chrome, Firefox) |
| - Cron |

| Library: |
|:---|
| Symfony - https://symfony.com/ |

## Instructions:
1) Copy files on your server.

2) Write on terminal:

        cd /home/user_1/www/symfony_fw
        
        sudo nano .env

3) Modify:

        APP_ENV=dev
        
        DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

4) Save, close the file and write on terminal:

        sudo cp /config/packages/framework.yaml.dist /config/packages/framework.yaml
        
        sudo nano /config/packages/framework.yaml
        
5) In "session:" modify:

        name: new_name

6) Save, close the file and write on terminal:

        sudo cp /src/Config.php.dist /src/Config.php
        
        sudo nano /src/Config.php

7) Change variables for adapt the framework on your system.

8) Save, close the file and write on terminal:

        sudo rm -rf vendor var/cache composer.lock
        
        sudo composer install
        
        sudo composer update
        
        cd /home/user_1/www/symfony_fw
        
        sudo chmod 775 ../symfony_fw
        
        sudo find ../symfony_fw -type d -exec chown user_1:www-data {} \; -exec chmod 775 {} \;
        
        sudo find ../symfony_fw -type f -not -name "sess_*" -exec chown user_1:www-data {} \; -exec chmod 664 {} \;
        
        sudo find ../symfony_fw -name "*.sh" -exec chmod 774 {} \;
	        
        sudo -u www-data php bin/console cache:clear --no-warmup --env=dev

7) For admin login use <b>"cimo, Password1"</b>.

<b>By CIMO - https://reinventsoftware.org</b>
