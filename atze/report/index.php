<?php
/********************************************************
 BEISPIEL: REPORTING / GOOGLE ADS CONVERSION EXPORT   
*********************************************************/

//Default: Daten von gestern in Export abrufen 
$yesterstring = date('Ymd', strtotime("-1 days"));

//Selbsterklaerend...
$conv_currency = "EUR";

//Datenbank - Pfad und Name. Anpassen an eigene Struktur!
$sqlitefile = "../storage/conversiondata.db";

$fmt = $_GET["action"];
if (!isset($fmt)) $fmt = "report";

//Ab welchem Alter in Tagen sollen bei einem Cleanup via "cleanup=1" Eintritte und Conversions
//aus der DB entfernt werden? Zur Verwendung &cleanup=1 an die URL anhaengen, um regelmaessig
//alte Daten aus der DB zu entfernen (Eintritte ausserhalb des Conversion-Fensters, 
//verarbeitete Conversions). Da die meisten Hoster aber die letzten Stellen einer IP aendern - 
//und weil IP Adressen ohnehin weder eindeutig noch langlebig sind -, ist längere Datenhaltung
//fuer Eintritte und Conversions selten erforderlich. Das mag bei Nutzung von Signalen aus dem
//Browser ander sein, wo bei Zustimmung auch Cookie zur Bildung eines Hash genutzt werden 
//koennen... bei Logfiles aber ist ein lsengerer Zeitraum kaum sinnvoll.  
$cleanup_days_a = 7;
$cleanup_days_z = 7;

/*********************************************************************************************/
function getResultsString($results, $delim) {
    $res = "";
    $ct = $results->numColumns();
    while($row=$results->fetchArray(SQLITE3_ASSOC)){
        for ($x = 0; $x < $ct; $x++) {
            $res .= $row[$results->columnName($x)];
            if ($x < $ct-1) $res .= $delim; else $res .= "\n"; 
        }
    }
    return $res;
}

function res_out($res, $filename) {
    if ($_GET["showresult"] !== "1") {
        header("Content-Disposition: attachment; filename=$filename");
        echo $res;
    } else 
    echo "<pre>\n".$res."</pre>";    
}

/***********************************************************/

if (!$sqlitefile || ($sqlitefile === "") || !file_exists($sqlitefile)) die("Keine Datenbank!"); 

$db = new SQLite3($sqlitefile);

if ($_GET["cleanup"] === "1") {
    //Altdaten (vor Auswertung) loeschen, wenn Parameter gesetzt 
    $db-> exec("delete from [sessiondata] where
                date < date('now', '-".$cleanup_days_a." days')");
    $db-> exec("delete from [conversiondata] where
                date < date('now', '-".$cleanup_days_z." days')");
}

//Abrufen der Conversions aus DB, wenn definiert
if ($fmt === "export") {

    $results = $db->query("select distinct
    gclid as 'Google Click ID',
    conv_name as 'Conversion Name', 
    conv_date as 'Conversion Time',
    conv_value as 'Conversion Value',
    '".$conv_currency."' as 'Conversion Currency'
    from conversions left join sessions 
        on conversions.hashValue = sessions.hashValue
    where 
        gclid is not null and
        conv_date = date('now','-1 day')");

    $res = "Google Click ID,Conversion Name,Conversion Time,Conversion Value,Conversion Currency\n";
    $res .= getResultsString($results, ",");
    //Ausgabe zum Download oder Browser
    res_out($res, "conversions.csv");
}

//Ausgabe Statistik
if ($fmt === "report") {
    $res = "Statistik-Beispiel\n==================\n\n".
    "Session Count\tConversion Count\tFirst Session Date\tLast Session Date\tLast Conversion Date\n";
    $results = $db->query("select count(distinct sessions.id) as 'Session Count',
    count(distinct conversions.id) as 'Conversion Count',
    min(session_date) as 'First Session Date', 
    max(session_date) as 'Last Session Date', 
    max(conv_date) as 'Last Conversion Date' 
    from conversions join sessions");
    //Hier beliebige weitere Abfragen hinzufuegen...
    $res .= getResultsString($results, "\t");
    //Ausgabe zum Download oder Browser
    res_out($res, "conversion_stats.csv");
} 

if ($db && $sqlitefile != "") $db->close();

?>