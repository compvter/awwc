istruzioni per installare 

bisogna essere root 
prima un po di pacchetti

apt-get update
apt-get upgrade
apt-get install ntp
apt-get install hostapd
apt-get install dhcp3-server

scompatta il pacchetto in /usr/share

modificare /etc/rc.local aggiungendo 2 linee prima di exit

/usr/local/bin/wifiboot &
/usr/local/bin/awwc.sh &
