#!/bin/bash

read -p "Which cluster? (prod, qa, dev) " cluster

rabbit_ip="10.4.90.102"

if [ $cluster == "qa" ]; then
  rabbit_ip="10.4.90.152"
fi

if [ $cluster == "prod" ]; then
  rabbit_ip="broker"
  echo "10.4.90.52 broker" | sudo tee -a /etc/hosts
  echo "10.4.90.62 broker" | sudo tee -a /etc/hosts
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

# Setup Central Logging
git clone git@github.com:stonX-IT490/logging.git ~/logging
cd ~/logging
chmod +x deploy.sh
./deploy.sh
cd ~/

# Email Push
git clone git@github.com:stonX-IT490/pushNotification.git ~/pushNotification
cd ~/pushNotification
chmod +x deploy.sh
./deploy.sh
cd ~/
