#!/bin/bash

read -p "Which cluster? (prod, qa, dev) " cluster

rabbit_ip="broker"
check=$( getent hosts | grep -e broker )

if [ "$check" == "" ]; then
  if [ $cluster == "dev" ]; then
    echo "10.4.90.102 broker" | sudo tee -a /etc/hosts
  fi

  if [ $cluster == "qa" ]; then
    echo "10.4.90.152 broker" | sudo tee -a /etc/hosts
  fi

  if [ $cluster == "prod" ]; then
    echo "10.4.90.52 broker" | sudo tee -a /etc/hosts
    echo "10.4.90.62 broker" | sudo tee -a /etc/hosts
  fi
fi

# Update repos
sudo apt update

# Do full upgrade of system
sudo apt full-upgrade -y

# Remove leftover packages and purge configs
sudo apt autoremove -y --purge

# Install required packages
sudo apt install -y ufw wget unzip php-bcmath php-amqp php-curl php-json php-cli php-zip php-mbstring inotify-tools

# Setup firewall
sudo ufw --force enable
sudo ufw allow ssh
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Install zerotier
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release
curl -s https://install.zerotier.com | sudo bash

# Setup rabbitmq listener
mkdir news
cd rabbit
git clone git@github.com:stonX-IT490/rabbitmq-common.git rabbitmq-dmzHost
cd rabbitmq-dmzHost
./deploy.sh
cd ..
git clone git@github.com:stonX-IT490/rabbitmq-common.git rabbitmq-webDmzHost
cd rabbitmq-webDmzHost
./deploy.sh
cd ..

rabbitDmzHost="<?php

\$config = [
  'host' => '$rabbit_ip',
  'port' => 5672,
  'username' => 'dmz',
  'password' => 'stonx_dmz',
  'vhost' => 'dmzHost'
];

?>"

rabbitWebDmzHost="<?php

\$config = [
  'host' =>'$rabbit_ip',
  'port' => 5672,
  'username' => 'dmz',
  'password' => 'stonx_dmz',
  'vhost' => 'webDmzHost'
];

?>"

echo "$rabbitDmzHost" > rabbitmq-dmzHost/config.php
echo "$rabbitWebDmzHost" > rabbitmq-webDmzHost/config.php

cd ..

pwd=`pwd`'/rabbit'
serviceNewsHost="[Unit]
Description=News RabbitMQ Consumer Listener
[Service]
Type=simple
Restart=always
ExecStart=/usr/bin/php -f $pwd/newsListener.php
[Install]
WantedBy=multi-user.target"

echo "$serviceNewsHost" > rmq-news.service

sudo cp rmq-news.service /etc/systemd/system/
sudo systemctl start rmq-news
sudo systemctl enable rmq-news

crontab="*/2 9-16 * * 1-5 /usr/bin/php -f $pwd/stockData.php > $pwd/stockData.log 2>&1
30 9 * * 1-5 /usr/bin/php -f $pwd/news.php > $pwd/news.log 2>&1
30 9 * * 1-5 /usr/bin/php -f $pwd/forexAPI.php > $pwd/forex.log 2>&1"

echo "$crontab" > crontab.temp

crontab -r
crontab crontab.temp
rm crontab.temp


# Install check-prod1 in systemd
pwd=`pwd`

serviceCheckProd1="[Unit]
Description=Check if dmz-prod1 is up

[Service]
Type=simple
Restart=always
ExecStart=$pwd/check.sh

[Install]
WantedBy=multi-user.target"

if [ $cluster == "prod" ]; then
  read -p "Which host? (prod1, prod2) " vm_type
  if [ $vm_type == "prod2" ]; then
    echo "$serviceCheckProd1" > check-prod1.service
    sudo cp check-prod1.service /etc/systemd/system/
    sudo systemctl start check-prod1
    sudo systemctl enable check-prod1
  fi
fi


# Setup Central Logging
git clone git@github.com:stonX-IT490/logging.git ~/logging
cd ~/logging
git pull
chmod +x deploy.sh
./deploy.sh
cd ~/

# Email Push
git clone git@github.com:stonX-IT490/pushNotification.git ~/pushNotification
cd ~/pushNotification
chmod +x deploy.sh
./deploy.sh
cd ~/
