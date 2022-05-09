#!/bin/bash
MACHINE=10.4.90.53
while :
do
  exec 3>/dev/tcp/${MACHINE}/22
  if [ $? -eq 0 ]; then
    systemctl stop cron
  else
    systemctl start cron
  fi
done
