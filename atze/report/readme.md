# Hinweise zum Conversion-Export
Dieses Script dient zur Demonstration verschiedener Vorgänge wie dem täglichen Auslesen von Conversions "Z" anhand von URLs mit einem Wert und einem Conversion-Namen, wenn zuvor ein Eintritt "A" mit einer gclid erfolgt ist.

**HINWEIS**: Deine Fassung sollte *unbedingt* hinter einem .htpasswd Schutz liegen! 

## Verwendung
Nach einem Setup (siehe Kommentare im Script) kann mit dem Parameter `"action=xxx"` beim Aufruf gesteuert werden, welche Vorgänge stattfinden sollen. 

### Aktionen 
- *"export"* fuehrt eine Abfrage der Conversions des gestrigen Tages aus und liefert eine CSV Datei mit dem Ergebnis. Wahlweise kann eine mnimal formatierte Anzeige im Browser erfolgen (siehe "Weitere Parameter") 
- *"report"* zeigt eine exemplarische einfache Übersicht der Inhalte der SQLite DB an - hier muss ausgebaut werden, wenn mehr passieren soll  

### Weitere Parameter
- *"showresult=1"* aktiviert die Ausgabe im Browser. Standard ist Ausgabe als Export-File
- *"cleanup=1"* loescht aeltere Daten aus der DB vor dem Abruf von Conversions (s.u.)    
