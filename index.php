<?php
/* Arietta	
 * Wi-fi
 * Web
 * Config
 */
	
	function waitdiv($t,$location){
	echo"
	<html>
	<link rel='stylesheet' type='text/css' href='css/theme.css' media='all' />
	
	<body onload=\"setTimeout(function(){window.location='$location';}, $t * 1000); \">
	<div name='waitdiv'id='waitdiv' class='messagebox'>
	<br><span class='blue button'> Arietta in riavvio pazienza....... </span>
	</div>
	</body>
	</html>
	";	
		
	}

	error_reporting(E_ALL); //debug
	ini_set('display_errors', '1');

	include "config.php";//file di configurazione
	$op=@$_GET['op'];
	$storagedir="gcode";

	$password=@$_COOKIE[md5("password")];
if ($password==$conf_password||$conf_enablepassword==""){
	switch($op){
					
		case "rescan":
			//cpvtr_exec("StratoBoot");
			shell_exec("/sbin/iwconfig wlan0 mode Managed &&
						/sbin/ifup wlan0 &&
						/sbin/iwlist wlan0 scan | grep -i 'essid'>/tmp/list.txt");
		

			sleep(5);	
			header("location:index.php");
			break;
			
		case "update":
			waitdiv(60,"index.php");		
			//salvo la configurazione
			$c="<?php\n";
			$confenablepassword=@$_POST['confenablepassword'];
			$c.="\$"."conf_enablepassword=\"$confenablepassword\";\n";			
			$confpassword=md5($_POST['confpassword']);			
			$c.="\$"."conf_password=\"$confpassword\";\n";

			$confmasteraddress=$_POST['confmasteraddress'];
			$c.="\$"."conf_masteraddress=\"$confmasteraddress\";\n";
			$confmasterssid=$_POST['confmasterssid'];
			$c.="\$"."conf_masterssid=\"$confmasterssid\";\n";
			$confmasterchannel=$_POST['confmasterchannel'];
			$c.="\$"."conf_masterchannel=\"$confmasterchannel\";\n";

			$confenablerouter=@$_POST['confenablerouter'];
			$c.="\$"."conf_enablerouter=\"$confenablerouter\";\n";		
			$confrouterhostname=@$_POST['confrouterhostname'];
			$c.="\$"."conf_routerhostname=\"$confrouterhostname\";\n";						
			$confrouterssid=$_POST['confrouterssid'];			
			$c.="\$"."conf_routerssid=\"$confrouterssid\";\n";
			$confroutersecuritytype=$_POST['confroutersecuritytype'];			
			$c.="\$"."conf_routersecuritytype=\"$confroutersecuritytype\";\n";
			$confroutersecuritykey=$_POST['confroutersecuritykey'];			
			$c.="\$"."conf_routersecuritykey=\"$confroutersecuritykey\";\n";

			$confrouterconfig=$_POST['confrouterconfig'];			
			$c.="\$"."conf_routerconfig=\"$confrouterconfig\";\n";
			$confrouterip=$_POST['confrouterip'];			
			$c.="\$"."conf_routerip=\"$confrouterip\";\n";
			$confroutermask=$_POST['confroutermask'];			
			$c.="\$"."conf_routermask=\"$confroutermask\";\n";
			$confroutergateway=$_POST['confroutergateway'];			
			$c.="\$"."conf_routergateway=\"$confroutergateway\";\n";

			$confrouterdnsmanual=@$_POST['confrouterdnsmanual'];
			$c.="\$"."conf_routerdnsmanual=\"$confrouterdnsmanual\";\n";			
			$confrouterdns=$_POST['confrouterdns'];			
			$c.="\$"."conf_routerdns=\"$confrouterdns\";\n";
			$confrouterdns1=$_POST['confrouterdns1'];			
			$c.="\$"."conf_routerdns1=\"$confrouterdns1\";\n";

			$c.="?>\n";
			$fp=fopen("config.php","w");
			fwrite($fp,$c);
			fclose($fp);
			
			//applico la configurazione
			
			if($confenablerouter==""){
				$c="auto lo\n";
				$c.="iface lo inet loopback\n";
				$c.="\n";
				$c.="pre-up modprobe g_ether\n";
				$c.="auto usb0\n";
				$c.="iface usb0 inet static\n";
				$c.="address 192.168.10.10\n";
				$c.="netmask 255.255.255.0\n";
				$c.="\n";
				$c.="auto wlan0\n";
				$c.="iface wlan0 inet static\n"; 
				$c.="address $confmasteraddress\n";
				$c.="netmask 255.255.255.0\n";
				$fp=fopen("/etc/network/interfaces","w");
				fwrite($fp,$c);
				fclose($fp);
			
				$c="interface=wlan0\n";
				$c.="driver=nl80211\n";
				$c.="ssid=$confmasterssid\n";
				$c.="channel=$confmasterchannel";				
				$fp=fopen("/etc/hostapd.conf","w");
				fwrite($fp,$c);
				fclose($fp);
				
				$c="ddns-update-style none;\n";
				$c.="option domain-name \"strato.bt\";\n";
				$c.="option domain-name-servers $confmasteraddress;\n";
				$c.="\n";
				$c.="default-lease-time 600;\n";
				$c.="max-lease-time 7200;\n";
				$c.="\n";
				$c.="authoritative;\n";
				$c.="\n";
				$c.="log-facility local7;\n";
				$c.="\n";
				
				$c.="subnet ".substr($confmasteraddress,0,strrpos($confmasteraddress,".")).".0 netmask 255.255.255.0 {\n";
				$c.="	range ".substr($confmasteraddress,0,strrpos($confmasteraddress,".")).".50 ".substr($confmasteraddress,0,strrpos($confmasteraddress,".")).".100;\n";
				$c.="	option routers $confmasteraddress ;\n";
				$c.="}\n";

				$fp=fopen("/etc/dhcp/dhcpd.conf","w");
				fwrite($fp,$c);
				fclose($fp);
				
				$c="#!/bin/bash\n";
				$c.="/sbin/iwconfig wlan0 mode Managed\n";
				$c.="/sbin/iwlist wlan0 scan | grep -i 'essid'>/tmp/list.txt\n";
				$c.="/sbin/iwconfig wlan0 mode Master\n";
				$c.="/usr/sbin/hostapd /etc/hostapd.conf &\n";
				
				$fp=fopen("/usr/local/bin/wifiboot","w");
				fwrite($fp,$c);
				fclose($fp);
				chmod("/usr/local/bin/wifiboot",0777);
				
				//eseguo il comando
				shell_exec("reboot");
			}
			else{
				$c="$confrouterhostname\n";
				
				$fp=fopen("/etc/hostname","w");
				fwrite($fp,$c);
				fclose($fp);
				
				$c="auto lo\n";
				$c.="iface lo inet loopback\n";
				$c.="\n";
				$c.="pre-up modprobe g_ether\n";
				$c.="auto usb0\n";
				$c.="iface usb0 inet static\n";
				$c.="address 192.168.10.10\n";
				$c.="netmask 255.255.255.0\n";
				$c.="\n";
				$c.="auto wlan0\n";
				if($confrouterconfig=="manual"){
					$c.="iface wlan0 inet static\n"; 
					$c.="address $confrouterip\n";
					$c.="netmask $confroutermask\n";
					$c.="gateway $confroutergateway\n";
				}
				if($confrouterconfig=="dhcp"){
					$c.="iface wlan0 inet dhcp\n";
				}
				
				
				if($confroutersecuritytype=="nessuna"){
					$c.="wireless-essid \"$confrouterssid\"\n";
					$c.="wireless-mode managed\n"; 
				}
				if($confroutersecuritytype=="wep"){
					$c.="wireless-essid \"$confrouterssid\"\n";
					$c.="wireless-key $confroutersecuritykey\n";
				}
				if($confroutersecuritytype=="wpa")$c.="pre-up wpa_supplicant -iwlan0 -c /etc/wpa_supplicant.conf -B\n";
				
				$fp=fopen("/etc/network/interfaces","w");
				fwrite($fp,$c);
				fclose($fp);
				
				$c="network={\n";
				$c.="	ssid=\"$confrouterssid\"\n";
				$c.="	psk=\"$confroutersecuritykey\"\n";
				$c.="	key_mgmt=WPA-PSK\n";
				$c.="	proto=WPA\n";
				$c.="}\n";
				
				$fp=fopen("/etc/wpa_supplicant.conf","w");
				fwrite($fp,$c);
				fclose($fp);
				
				if($confrouterdnsmanual!="")
					$c="nameserver $confrouterdns \nnameserver $confrouterdns1 \n";
				else
					$c="nameserver $confroutergateway";
					
				$fp=fopen("/etc/resolv.conf","w");
				fwrite($fp,$c);
				fclose($fp);

				$c="#!/bin/bash\n";
				$c.="/sbin/iwconfig wlan0 mode Managed\n";
				$c.="/sbin/iwlist wlan0 scan | grep -i 'essid'>/tmp/list.txt\n";
				
				$fp=fopen("/usr/local/bin/wifiboot","w");
				fwrite($fp,$c);
				fclose($fp);
				chmod("/usr/local/bin/wifiboot",0777);
				
				//eseguo il comando
				shell_exec("reboot");
				
			}
			die();
	}
				
}
// setting del cookie

$password=@$_POST['password'];
if ($password!=""){
		setcookie(md5("password"),md5($password));
		header("location:index.php");
} 
?>
<html>
	<head>
	<title>Arietta web wifi config</title>
	<meta name="robots" content="" >
	<meta name="generator" content="" >
	<meta name="keywords" content="arietta web wifi config" >
	<meta name="description" content="" >
	<meta name="MSSmartTagsPreventParsing" content="true" >
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="distribution" content="global" >
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >
	<meta http-equiv="Resource-Type" content="document" >
	<link rel="stylesheet" type="text/css" href="theme.css" media="all" />
	<script>
			
	function autozoom(){
		if (window.innerHeight > window.innerWidth){
			document.body.style.zoom = window.innerWidth/400;		
		}
		else{
			document.body.style.zoom = window.innerHeight/640;
		}
	}
	</script>
	
	
	</head>
	<body onload="autozoom();">
		
<?php
if($conf_enablepassword!=""){
	$password=@$_COOKIE[md5("password")];
	if ($password!=$conf_password ){
		echo"<div class='messagebox'>
	<form action='index.php' method='post'>
	password 
	<input name='password' type='password' class='text'>
	<input type='submit' class='button orange' value='entra'>
	</form>
	</div>";
	die();
	}
}
?>		
		<div id='divtextpage' name='divtextpage'>
			<span id='tabsetup' name='tabsetup' style='display:inline;' >
				<h1>Arietta Web Wifi Config</h1><br><br>				
				<br><br>
				<form action='index.php?op=update' method='post' onsubmit="alert('deve riavviare la arietta: attendere');">
					abilita password 
					 <label class="switch switch-blue" >
					  <input type="checkbox" class="switch-input" name="confenablepassword" id="confenablepassword"  <?php if ($conf_enablepassword!="")echo"checked"?> onchange="if(confenablepassword.checked)confSpassword.style.display='inline';else confSpassword.style.display='none';" >
					  <span class="switch-label" data-on="on" data-off="off"></span>
					  <span class="switch-handle"></span>
					</label>					
					<br>	
					<p id='confSpassword' name='confSpassword'>password <input type='password' name='confpassword' style='width:300px'><br> 
					conferma password <input type='password' name='conf2password' style='width:300px'></p>
					<script>if(confenablepassword.checked)confSpassword.style.display='inline';else confSpassword.style.display='none';</script>
					<hr>
					
					<span id='confSmaster' name='confSmaster'>
						address <input type='text' name='confmasteraddress' value='<?=@$conf_masteraddress;?>' style='width:200px;'><br>  
						ssid <input type='text' name='confmasterssid' value='<?=@$conf_masterssid;?>'><br> 
						channel	<input type='text' name='confmasterchannel' value='<?=@$conf_masterchannel;?>' style='width:100px;'><br>
					</span>
					
					connetti strato 3d al router 
					 <label class="switch switch-blue" >
					  <input type="checkbox" class="switch-input" name="confenablerouter" id="confenablerouter" <?php if (@$conf_enablerouter!="")echo"checked"?> onchange="if(confenablerouter.checked){confSrouter.style.display='inline';confSmaster.style.display='none';}else{ confSrouter.style.display='none';confSmaster.style.display='inline';}" >
					  <span class="switch-label" data-on="on" data-off="off"></span>
					  <span class="switch-handle"></span>
					</label>					
					<br>
					<span id='confSrouter' name='confSrouter'>
						hostname <input type='text' name='confrouterhostname' value='<?=@$conf_routerhostname;?>'><br><br>
						ssid <select name='confrouterssid' style='width:400px'>
							<option><?=$conf_routerssid;?></option>
							<?php $list=@file('/tmp/list.txt');	foreach($list as $el)echo"<option>".substr($el,27,-2)."</option>"; ?>
						</select><br><br>
						sicurezza <select name='confroutersecuritytype' >
							<option><?=@$conf_routersecuritytype;?></option>
							<option>nessuna</option>
							<option>wep</option>
							<option>wpa</option>
						</select><br><br>
						<span class='button blue' onclick="location='index.php?op=rescan';">cerca</span>
						<br><br>
						password
						<input type='password' name='confrouterhiddensecuritykey' style='display:inline; width:400px;' value='<?=@$conf_routersecuritykey;?>' onchange="confroutersecuritykey.value=confrouterhiddensecuritykey.value;" >
						<input type='text' name='confroutersecuritykey' style='display:none; width:400px' value='<?=@$conf_routersecuritykey;?>' onchange="confrouterhiddensecuritykey.value=confroutersecuritykey.value;" ><br>
						 <label class="switch switch-blue" >
						  <input type="checkbox" class="switch-input" name="confroutervisiblesecuritykey" id="confroutervisiblesecuritykey" onchange="if(confroutervisiblesecuritykey.checked){confrouterhiddensecuritykey.style.display='none';confroutersecuritykey.style.display='inline';}else{ confrouterhiddensecuritykey.style.display='inline';confroutersecuritykey.style.display='none';}">
						  <span class="switch-label" data-on="on" data-off="off"></span>
						  <span class="switch-handle"></span>
						</label>
						mostra password<br>
						<br>
						configurazione <select name='confrouterconfig' id='confrouterconfig' onchange="if(confrouterconfig.value=='dhcp')confrouternetwork.style.display='none'; else confrouternetwork.style.display='inline';">
							<option><?=@$conf_routerconfig;?></option>
							<option>dhcp</option>
							<option>manual</option>
						</select><br><br>
						<span id='confrouternetwork' name='confrouternetwork'>
							ip <input type='text' name='confrouterip' style='width:180px' value='<?=@$conf_routerip;?>' > <br>
							mask <input type='text' name='confroutermask' style='width:180px' value='<?=@$conf_routermask;?>' > <br> 
							gateway <input type='text' name='confroutergateway' style='width:180px' value='<?=@$conf_routergateway;?>' > <br>
						</span>
						<script>if(confrouterconfig.value=='dhcp')confrouternetwork.style.display='none'; else confrouternetwork.style.display='inline';</script>
						 dns manuali 
						<label class="switch switch-blue" >
						  <input type="checkbox" class="switch-input" name="confrouterdnsmanual" id="confrouterdnsmanual"  <?php if (@$conf_routerdnsmanual!="")echo"checked"?> onchange="if(confrouterdnsmanual.checked){confSrouterdnsmanual.style.display='inline';}else{ confSrouterdnsmanual.style.display='none';}" >
						  <span class="switch-label" data-on="on" data-off="off"></span>
						  <span class="switch-handle"></span>
						</label>
						<br>
						<span id='confSrouterdnsmanual' name='confSrouterdnsmanual'>
							dns <input type='text' name='confrouterdns' style='width:180px' value='<?=@$conf_routerdns;?>' > <br>
							<input type='text' name='confrouterdns1' style='width:180px' value='<?=@$conf_routerdns1;?>' > <br>
						</span>	
						<script>if(confrouterdnsmanual.checked){confSrouterdnsmanual.style.display='inline';}else{ confSrouterdnsmanual.style.display='none';}</script>
					</span>
					<script>if(confenablerouter.checked){confSrouter.style.display='inline';confSmaster.style.display='none';}else{ confSrouter.style.display='none';confSmaster.style.display='inline';}</script>
				<br><span style='float:right;right:0px'><input type='submit' class='button red' value='applica' onclick="if(confpassword.value!=conf2password.value){alert('le password non coincidono');return false;}"></span>				
							<br>
				</form>
			</span>
		</div>	
	</body>
</html>
