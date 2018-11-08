#!/bin/bash

gitCloneUrl=https://username:password@github.com/cimo/Uebusaito.git
gitClonePath=/home/user_1/www/project_folder
userGitScript=user_1
userWebScript=user_1:www-data
rootWebPath=/home/user_1/www/project_folder

#sudo git config --global core.mergeoptions --no-edit
sudo git config --global user.email "email"
sudo git config --global user.name "username"

echo "Git shell"
read -p "1) Clone - 2) Pull: - 3) Reset: > " gitChoice

if [ ! -z $gitChoice ]
then
    if [ $gitChoice -eq 1 ]
    then
        sudo -u $userGitScript git clone $gitCloneUrl $gitClonePath
    elif [ $gitChoice -eq 2 ]
    then
        read -p "Insert branch name: > " branchNameA branchNameB

        if [ ! -z "$branchNameA $branchNameB" ]
        then
                cd $gitClonePath
                sudo -u $userGitScript git pull --no-edit $gitCloneUrl $branchNameA $branchNameB
        else
                echo "Empty value, please restart!"
        fi
    elif [ $gitChoice -eq 3 ]
    then
        cd $gitClonePath
        sudo -u $userGitScript git fetch --all
        sudo -u $userGitScript git reset --hard
    fi
    echo "Settings project in progress, please wait..."

    sudo chown -R $userWebScript $rootWebPath
    sudo find $rootWebPath -type d -exec chmod 775 {} \;
    sudo find $rootWebPath -type f -exec chmod 664 {} \;

    echo "Finito ciao ciao =D"
else
    echo "Empty value, please restart!"
fi
