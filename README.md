Uebusaito
==============

Framework for create fast and secure website, microservice and api for the web.

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
| Page comment |
| Microservice (Deploy, api, qunit, selenium and cron) |
| Integration with: Slack, line |
| Extend with module system |

| Elements: |
|:---|
| System info |
| Payment |
| Page |
| User |
| Module |
| Role |
| Setting |
| Slack |
| Line |
| Microservice |
| - Deploy |
| - Api |
| - Qunit |
| - Selenium (Chrome, Firefox) |
| - Cron |

| Library: |
|:---|
| Symfony - https://symfony.com/ |

## Instructions:
1) Copy files on your server.

2) Write on terminal:

        cd /home/user_1/www/website/symfony_fw
        
        sudo cp .env.dist .env
        
        sudo nano .env

3) Modify:

        APP_ENV=dev
        
        DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

4) Save, close the file and write on terminal:

        sudo cp /config/packages/framework.yaml.dist /config/packages/framework.yaml
        
        sudo nano /config/packages/framework.yaml
        
5) In "session:" modify:

        save_path: '%kernel.project_dir%/var/sessions/%kernel.environment%'
        
        name: new_name

6) Save, close the file and write on terminal:

        sudo cp /src/Config.php.dist /src/Config.php
        
        sudo nano /src/Config.php

7) Change variables for your system setting.

8) Save, close the file and write on terminal:

        sudo rm -rf vendor var/cache composer.lock
        
        sudo php -d memory_limit=-1 /usr/local/bin/composer install --no-plugins --no-scripts
        
        sudo chmod 775 ../
        
        sudo find ../ -type d -exec chown user_1:www-data {} \; -exec chmod 775 {} \;
        
        sudo find ../ -type f -not -name "sess_*" -exec chown user_1:www-data {} \; -exec chmod 664 {} \;
        
        sudo find ../ -name "*.sh" -exec chmod 774 {} \;
	        
        sudo -u www-data php bin/console cache:clear --no-warmup --env=dev

7) For admin login use <b>"cimo, Password1"</b>.

<b>By CIMO - https://reinventsoftware.org</b>

Supported By:

![Image of supporter](https://avatars0.githubusercontent.com/u/878437?s=200&v=4)
