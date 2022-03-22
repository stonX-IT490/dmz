#!/bin/bash

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
git clone https://github.com/stonX-IT490/rabbitmq-common.git rabbitmq-dmzHost
git clone https://github.com/stonX-IT490/rabbitmq-common.git rabbitmq-webDmzHost
cp ../config.dmzHost.php rabbitmq-dmzHost/config.php
cp ../config.webDmzHost.php rabbitmq-webDmzHost/config.php
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
30 9 * * 1-5 /usr/bin/php -f $pwd/news.php > $pwd/news.log 2>&1"

echo "$crontab" > crontab.temp

crontab -r
crontab crontab.temp
rm crontab.temp

# Setup Central Logging
git clone https://github.com/stonX-IT490/logging.git ~/logging
cd ~/logging
chmod +x deploy.sh
./deploy.sh
cd ~/

# Email Push
git clone https://github.com/stonX-IT490/pushNotification.git ~/pushNotification
cd ~/pushNotification
chmod +x deploy.sh
./deploy.sh
cd ~/
