# Hinweise zum Endpunkt
Die Datei `index.php` enthält den Code zum Empfang der Events vom Browser, die der Tag Code bei Eintritten mit Kampagnenparametern oder Erreichen einer Ziel-URL sendet. 

In `index-v1.php` findet sich eine Variante, die auch das Schreiben von einfachen Logs für den Start sowie eine vereinfachte Speicherung der Events in einer einzigen Tabelle beinhaltet - das macht allerdings den
Abruf der Conversions komplexer. Die neuere Fassung arbeitet daher mit zwei Tabellen für "Conversions" und "Sessions".  

Die Ordner `saltcache` zur Speicherung des Salt Werts für die Hash-Funktion (siehe unten) und `storage` enthalten lediglich `.htaccess` Dateien, die den öffentlichen Zugriff via HTTP verhindern. Hier entstehen bei Betrieb des Endpunkts die gespeicherte Salt Datei bzw. die Text-Logs und / oder SQLite DB. Je nach Setup von Dateinamen und Pfaden sind diese Ordner auf dem eigenen Server mit einem anderen Namen zu versehen; müssen aber i. d. R. durch Upload der `.htaccess` Dateien oder manuell angelegt werden, damit der Endpunkt seine Arbeit verrichten kann. 

## Die Hash Funktion
Da keine Cookies eingesetzt werden, dient ein Hash aus verschiedenen Daten dazu, eine Verbindung zwischen Eintritten und evtl. auftretenden Conversions in Form von Zielerreichungen herzustellen. 

Dabei werden neben konstanten Zeichenketten ("Pepper"; im Code gegen eigene zufällige Zeichenketten austauschen!) Angaben aus dem Request verwendet. Dies sind 
- IP Adresse (durch Kürzung anonymisiert)
- User Agent
- eine weitere Zeichenkette als Salt, die zufällig generiert in einer Datei gespeichert und beim ersten Abruf nach einem Tageswechsel erneuert wird

### Hinweis zur Lebensdauer des Salt Werts
Es ist unter bestimmten Bedingungen auch denkbar, eine längere Lebensdauer des Salt Wertes zu definieren oder diesen gar nicht auslaufen zu lassen, sondern nur durch die Bereinigung der Datenbank dafür zu sorgen, dass sich Attributionsfenster auch in den Daten schlie0en. Dabei ist aber zu beachten, dass die hier in der Demo verwendeten SQL Abfragen zur Generierung einer Export-Datei für Conversions diesem Umstand gerecht werden müssen (sie werden dadurch deutlich komplexer) - unabhängig von den Implikationen für den Datenschutz. Längere Lebensdauer (oder Austausch der Hash Werte durch Cookie Werte o. a. ) erfordert i. d. R. ein Ausspielen des Tag Codes, der auf Zustimmung angewiesen ist. 

### Begrenzte Trennschärfe, dafür Cross-Domain-fähig
Da eine IP Adresse (schon gar nicht gekürzt) eindeutig dazu geeignet ist, eine eindeutige Zuordnung zu erlauben (auch nicht in Kombibation mit einem ebenso unzuverlässigen User Agent), sind gleiche Hash Werte für unterschiedliche User der Website durchaus denkbar.

Auf der Haben-Seite wird auf diese Weise aber auch eine Messung unabhängig der Domain beim Einstieg und der Zielerreichung möglich. Da das Tag lediglich ein Image Tag nutzt, um den Request zu senden, sollten auch keine typischen Probleme im Weg stehen (wie CORS o. Ä.). 
