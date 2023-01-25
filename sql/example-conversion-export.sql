select 
   substr(pageUrl, 
   -- Google Click ID extrahieren, hier exemplarisch nur gclid
   instr(pageUrl, "gclid=")+6) as "Google Click ID", 
   -- Name der Conversion wie in Google Ads definiert hier eintragen
   "Import Conversion Name hier" as "Conversion Name", 
   -- der Zeitpunkt der Conversion ist im Prinzip gelogen, weil es der Zeitpunkt 
   -- des Klicks und nicht des Abschlusses ist. Bleibt egal, solange Hash täglich ausläuft
   timeStamp as "Conversion Time",
  -- konstanten Wert der Conversion einfügen, wenn gewünscht (oder am Endpunkt anhand 
  -- der URL bestimmen und zusätzlich in DB schreiben bzw. anhand Ziel URL per SQL bestimmen
   42.0 as "Conversion Value", 
   "EUR" as "Conversion Currency"
from 
  (select distinct timeStamp, hashValue, pageUrl from campaignEvents 
  -- Conversions des passenden Tages dedupliziert abrufen - funktioniert so ebenfalls nur,
  -- wenn ein Hash täglich wechselt. Bei kängeren Zeiträumen muss die Abfrage berücksichtigen, 
  -- dass ein neuer Klick des gleichen hashValue sonst eine frühere Conversion erneut zählt
  where hashValue in 
    ( select distinct hashValue from campaignEvents where eventType = "cnv" ) 
  -- hier wird nur gclid berücksichtigt. Wer andere IDs ebenfalls 
  -- zurückspielen / exportieren will, muss das Statement hier und oben anpassen
  and pageUrl like "%gclid=%"
  -- nur Klicks der letzten 14 Tage berücksichtigen
  and date(timeStamp) between date('now', '-15 days') and date('now', '-1 days') 
  ) 
  
-- Bereinigen? Brutale Methode:
-- delete from campaignEvents where date(timeStamp) between date('now', '-15 days') and date('now', '-1 days')
-- (Besser: Markieren als "processed" im eventType o. Ä. und separate Bereinigung in sep. Schritt oder Nutzen 
-- von Transactions beim Aufruf von Abfolgen per Kommandozeile, Crom Job, Script o. Ä.)
