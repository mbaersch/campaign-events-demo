<?php
error_reporting(0);
/******************************************************************************************
* Beispielhafter Endpunkt für den Empfang und die Verarbeitung von Website Events, welche *
* zur Vermessung von Eintritten mit Kampagnenparametern oder Erreichen von Zielen dienen. *                                            * 
******************************************************************************************/

/********************************** SETUP START  **********************************/

//Laufen Fingerprints mit einem wechselnden Hash ab? 
$fingerprintExpires = true;

//Soll ein einfaches Logfile im Textformat erstellt werden? 
//das sollte freilich nur zu Debug-Zwecken eingesetzt werden, sonst leer lassen
$logfile = "storage/campaigndata.log";
//$logfile = "";

//Soll eine Sqlite Datenbank als First Party Ziel für Events genutzt werden, 
//hier einen beliebigen Dateinamen eintragen oder leer lassen 
$sqlitefile = "storage/campaigndata.sqlite";

/********************************** SETUP ENDE   **********************************/

function anonymizeIp($ip) {
  //IPV4/IPV6 
  return preg_replace(['/\.\d*$/','/[\da-f]*:[\da-f]*$/'],['.0','0000:0000'],$ip);
}

function generate_salt($length = 16) {
  return substr(str_shuffle(str_repeat($x='$%Â§&()=#*+-_0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

//Auslesen des Typs: Eintritte haben einen Zufallswert als Parameter "a", 
//Conversions als "z". Die URL wohnt im Referrer, der Rest in Headern
if (isset($_GET["a"])) $type = "entry"; 
else if (isset($_GET["a"])) $type = "conversion"; 
else $type = null;

//Hier kann und sollte ggf. noch sinnvolle weitere Absicherung hinzugefügt 
//werden wie Prüfung des Referrers, Parameter o. Ä.   
if ($type !== null) {

  //URL der vermessenen URL steckt im Referrer  
  $url = $_SERVER['HTTP_REFERER'];  

  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  if (! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
    $clientIp = anonymizeIp($_SERVER['REMOTE_ADDR']);
  else 
    $clientIp = anonymizeIp($_SERVER['HTTP_X_FORWARDED_FOR']);

  //Fingerprint erstellen aus (anonymer) IP und User Agent mit etwas Pfeffer und Salz  
  $in  = $clientIp . 'YZO@<dYKvR' . $userAgent . '0J?CJz5I';
  if ($fingerprintExpires === true) {
    //die einfache, aber nicht ideale Methode: 
    //Salt aus Datei lesen und in Session cachen. 
    //Die Cache-Datei sollte htaccess-Schutz geniessen! 
    $saltfile = "include/#salt";
    $salt = "";
    $saltinfo = file_get_contents($saltfile);
    if (($saltinfo !== false) && ($saltinfo != "")) 
      $saltarr = explode("\n", $saltinfo); 
    else 
      $saltarr = array();
    $salt_date = $saltarr[0];
  
    //Auslauf des Salts mit Tageswechsel 
    $now_date = date('d.m.Y');
    //Salt verwenden oder verwerfen und erneuern, wenn nicht vom gleichen Tag
    if ($salt_date === $now_date) $salt = $saltarr[1];
    if ($salt === "") {
      $salt = generate_salt();
      $saltinfo = "$now_date\n$salt";
      file_put_contents($saltfile, $saltinfo);
      $_SESSION['saltinfo'] = $saltinfo;
    }  
  } else 
    $salt = 's&6!l%aV<*MFy;~U';
    
  $hashValue = hash('md5', $in.$salt);
  
  //in lokales Log schreiben?
  if ($logfile != "") {
    if (!file_exists($logfile)) touch($logfile);
    file_put_contents($logfile, date(DATE_ATOM, time())."\t$hashValue\t$type\t$url\n", FILE_APPEND);
  }

  //Speicherung in lokaler SQlite DB?
  if ($sqlitefile != "") {

    $init = !file_exists($sqlitefile);

    //Verbindung mit der DB
    $db = new SQLite3($sqlitefile);

    //Das checken und anlegen der Tabelle hier muss man streng genommen rauswerfen und die DB lokal erzeugen und hochladen - 
    //wir lassen es hier nun zu Demozwecken einfach drin. Den Call kann und sollte man sich im Echtbetrieb allerdigs sparen. Auch ist diese
    //Struktur der DB nur ein Beispiel mit wenigen Feldern und dem Event als Objekt in einem Datenfeld - das ist nicht ideal für alle denkbaren 
    //Arten von Abfragen und sollte daher nach eigenem Bedarf angepasst werden. Infos zu DB und Struktur siehe https://www.sqlite.org/docs.html  
    if ($init === true) 
      $db-> exec("CREATE TABLE IF NOT EXISTS campaignEvents(
       id INTEGER PRIMARY KEY AUTOINCREMENT,
       timeStamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
       hashValue TEXT NOT NULL DEFAULT 'none',
       eventType TEXT DEFAULT 'none',
       pageUrl TEXT)");

    //Neue Zeile in die DB einfügen und Verbindung trennen
    $db-> exec("INSERT INTO campaignEvents(hashValue, eventType, pageUrl) VALUES ('$hashValue', '$type', '$url')");
    $db->close();
  }

  //Geschafft. Pixel als Ergebnis:
  header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time())); // direkt
  header('Content-Type: image/gif');
  echo(hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b'));
} else {
  //Aufruf war nicht gueltig oder vollstaendig
  header('HTTP/1.0 403 Forbidden');
}
?>