# DEMO: First Party Conversion Tracking
**Beispiel-Implementierung für First Party Kampagnentraffic- und Conversionvermessung mit JS, PHP und SQlite** 

**Meet A.T.Z.E.**: Der Beispielcode in diesem Repo zeigt einen minimalen Code zum Tagging von Webseiten und einen Endpunkt zum Empfang und Verarbeitung von Eintritten auf Kampagnen-Landingpages (Start, Anfang oder "A") und Conversions (Ziel oder "Z") in Form von Events. *"A to Z Events"* sozusagen. Oder kurz: A.T.Z.E. ;) Die Demo basiert auf der [First Party Tracking Demo](https://github.com/mbaersch/first-party-demo), reduziert auf minimale Erhebung von Daten im Browser und entsprechend reduziertem Endpunkt mit Fingerprint-Hashfunktion zur Referenzierung von Eintritten und Conversions. 

![Last Update](https://img.shields.io/github/last-commit/mbaersch/campaign-events-demo) ![Top](https://img.shields.io/github/languages/top/mbaersch/campaign-events-demo)

--- 

## Quick Setup 
- Endpunktcode in `atze/index.php` konfigurieren (siehe Kommentare)
- Endpunktdaten (index.php + Unterordner) aus `atze` in einen Ordner auf dem eigenen Webserver bereitstellen (mit einem anderen Namen, wer will schon einen *atze* Ordner?)
- Code aus `atze-tag.js` anpassen (siehe unten) und in alle Seiten (oder zumindest Landingpages und Ziel-Seiten) implementieren
- Sicherheits- und Anpassungshinweise unten beachten
- Exportdateien auf Basis der Datenbank erstellen (siehe Beispielcode in `sql/example-conversion-export.sql`) oder Vorgang automatisieren; Hinweise dazu siehe unten

### Tag Code anpassen
Der Beispielcode nutzt zwei RegEx Muster zur Erkennung von 
- **Landingpage Eintitten** aus Kampagnen anhand der Click IDs (hier exemplarisch: `gclid` und `wbraid`, es können aber auch andere Bedingungen definiert werden). Dazu dient die Anweisung `a=/(&|\?)(gclid|wbraid)=.+/` 
- **Zielseiten-Aufrufen**. Die Erkennung findet sich in der Zuweisung `z=/(danke|thank-you)\.html/` am Ende der zweiten Zeile des Beispiel-Tag-Codes. Hinweis: Es müssen anderenfalls Events auf anderem Weg manuell ausgelöst werden, wenn es keine eindeutigen Ziel-URLs gibt)  

Ebenso wird direkt am Anfang in der Anweisung `e="/atze/"` die URL des Endpunkts bestimmt, der vermutlich in einem anderen Ordner (siehe oben) bersitgestellt wird. 

## Keine fertige Lösung!
Der hier bereitgestellte Code benötigt i. d. R. eine Menge Anpassung und individuelle Ergänzung, um auf einer Live-Website betrieben zu werden. 

**Einige Punkte**: 
- Anpassung von Endpunkt-URL und Definitionen für Kampagnenparameter und Ziel-URLs
- Benennung von Dateien und Ordnern
- Individualisierung von Code: 
  - Entfernen von DB-Initcode
  - Entfernen von Code für nicht genutzte Optionen
  - Austausch von Beispielwerten
 - Optimierung: Speichern von "Z" Events nur, wenn ein "A" Event gefunden wird 
  
- Absicherung von Ordnern
  - (besserer) Schutz von Salt-Cache und Datenbank-Ordner
  - Schutz von Conversion-Export-Datei
- Validierung am Endpunkt  

## Das musst Du noch selbst bauen
- Abrufen von Conversions für Export analog zu Matomo etc. als "Service-URL" bereitstellen (SQL Beispiel-Code zum Abruf anbei)
  - Manuell mittels Download der SQLite DB und Abfrage mit [DB Browser for SQLite](https://sqlitebrowser.org/)
  - Oder: Abruf per URL mittels PHP und Caching des Ergebnisses, anschließend Löschen oder Markieren exportierter Daten
  - Oder: Export und ggf. Bereinigung als Cron Job einrichten 
- Löschen veralteter Daten zur Reduktion / Kontrolle der Datenbank (als Cron Job o. Ä., siehe oben)
  - Optional: Löschen älterer Daten (mit ausreichend Abstand zum Abruf bestehender Conversions) bei Ablauf des alten Hashs 
- Weitere Auswertungen: 
  - Zum Beispiel Statistik zu CR, Wiederholungsrate, “Conversions: Paid vs. Other” etc. per (geschützter) PHP Report-Datei o. Ä.
