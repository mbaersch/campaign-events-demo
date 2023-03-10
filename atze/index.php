<?php
error_reporting(0);
/******************************************************************************************
* Beispielhafter Endpunkt für den Empfang und die Verarbeitung von Website Events, welche *
* zur Vermessung von Eintritten mit Kampagnenparametern oder Erreichen von Zielen dienen. *                                            * 
* VERSION 2: Speicherung Eintritte und Conversions in separaten Tabellen                  * 
******************************************************************************************/

/********************************** SETUP START  **********************************/

//Laufen Fingerprints mit einem wechselnden Hash ab? Antwort: Idealerweise ja ;) 
$fingerprintExpires = true;

//Dateinamen für SQLite DB hier angeben 
$sqlitefile = "storage/conversiondata.db";

//Optional: Conversions auch am Server definieren und abweichende Namen und Werte einsetzen   
$goals = [ 
  "danke.html" => "Offline Conversion Import DE, 10.0", 
  "thank-you.html" => "Offline Conversion Import EN, 22.5",
];

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
if (isset($_GET["a"])) $type = "A"; 
else if (isset($_GET["z"])) $type = "Z"; 
else $type = null;

//Hier kann und sollte ggf. noch sinnvolle weitere Absicherung hinzugefügt 
//werden wie Prüfung des Referrers, Parameter o. Ä.   
if ($type !== null) {

  //URL der vermessenen Seite steckt im Referrer  
  $url = $_SERVER['HTTP_REFERER'];
  //... oder wird optional als Parameter "u" übergeben (z. B. zu Testzwecken)
  if (!isset($url) || $url === "") $url = urldecode($_GET["u"]);   

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
    $saltfile = "saltcache/#salt";
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
  $day = date("Y-m-d");
  
  //Verbindung mit der DB
  $init = !file_exists($sqlitefile);
  $db = new SQLite3($sqlitefile);

  //Das checken und anlegen der Tabelle hier muss man streng genommen rauswerfen und die DB lokal erzeugen und hochladen - 
  //wir lassen es hier nun zu Demozwecken einfach drin. Den Call kann und sollte man sich im Echtbetrieb allerdigs sparen. Auch ist diese
  //Struktur der DB nur ein Beispiel mit wenigen Feldern und dem Event als Objekt in einem Datenfeld - das ist nicht ideal für alle denkbaren 
  //Arten von Abfragen und sollte daher nach eigenem Bedarf angepasst werden. Infos zu DB und Struktur siehe https://www.sqlite.org/docs.html  
  if ($init === true) {
    $db-> exec("CREATE TABLE IF NOT EXISTS sessions(
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      received TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      session_date TEXT, 
      gclid TEXT, 
      hashValue TEXT NOT NULL DEFAULT 'none',
      landingpage TEXT)");

    $db-> exec("CREATE TABLE IF NOT EXISTS conversions(
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      received TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      conv_date TEXT, 
      conv_name TEXT, 
      conv_value TEXT, 
      hashValue TEXT NOT NULL DEFAULT 'none',
      goalpage TEXT)");

  } 

  //Neue Zeile in die DB einfügen und Verbindung trennen
  if ($type === "A") {
    $gclid = substr($url, strpos($url, "gclid=")+6);  
    $db-> exec("INSERT INTO sessions(session_date, gclid, hashValue, landingpage) 
                VALUES ('$day', '$gclid', '$hashValue', '$url')");
  } else {
    //Name und Wert der Conversion bestimmen...
    $conv_name = "Offline Conversion";
    $conv_value = 0;
    foreach ($goals as $k => $v) {
      if (strpos($url, $k) !== false) {
        $v = explode(",", $v.",");
        $conv_name = trim($v[0]);
        $conv_value = trim($v[1]);
        break;
      }
    }
    $db-> exec("INSERT INTO conversions(conv_date, conv_name, conv_value, hashValue, goalpage) 
                VALUES ('$day', '$conv_name', '$conv_value', '$hashValue', '$url')");
  }

  $db->close();

  //Geschafft. Pixel als Ergebnis:
  header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time())); // direkt
  header('Content-Type: image/gif');
  echo(hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b'));
} else {
  //Aufruf war nicht gueltig oder vollstaendig
  header('HTTP/1.0 403 Forbidden');
}
?>