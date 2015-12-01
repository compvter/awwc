#cd /usr/share/awwc
/sbin/iwconfig wlan0 mode Managed 
/sbin/ifup wlan0 
/sbin/iwlist wlan0 scan | grep -i 'essid'>/tmp/list.txt
php -S 0.0.0.0:6500
