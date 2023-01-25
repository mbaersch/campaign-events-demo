# DEMO: First Party Conversion Tracking
Beispiel-Implementierung für First Party Kampagnentraffic- und Conversionvermessung mit JS, PHP und SQlite. 

## Meet A.T.Z.E.
Der Beispielcode in diesem Repo zeigt einen minimalen Code zum Tagging von Webseiten und einen Endpunkt zum Empfang und Verarbeitung von Eintritten auf Kampagnen-Landingpages (Start, Anfang oder "A") und Conversions (Ziel oder "Z") in Form von Events. *"A to Z Events"* sozusagen. Oder kurz: A.T.Z.E. ;) 

## Keine fertige Lösung!
Der hier bereitgestellte Code benötigt i. d. R. eine Menge Anpassung und individuelle Ergänzung, um auf einer Live-Website betrieben zu werden. 

**Einige Punkte**: 
- Anpassung von Endpunkt-URL und Definitionen für Kampagnenparameter und Ziel-URLs
- Benennung von Dateien und Ordnern
- Individualisierung von Code: 
  - Entfernen von DB-Initcode
  - Entfernen von Code für nicht genutzte Optionen
  - Austausch von Beispielwerten
- Absicherung von Ordnern
  - Schutz von Salt-Cache und Datenbank-Ordner
  - Schutz von Conversion-Export-Datei
Validierung am Endpunkt  
