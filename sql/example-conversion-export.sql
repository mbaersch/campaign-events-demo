/*ABRUF ALLER CONVERSIONS VON GESTERN - 
VERSION 2 MIT GETRENNTEN TABELLEN
*/
select distinct
    gclid as 'Google Click ID',
    conv_name as 'Conversion Name', 
    conv_date as 'Conversion Time',
    conv_value as 'Conversion Value',
    'EUR' as 'Conversion Currency'
from conversions left join sessions 
  on conversions.hashValue = sessions.hashValue
 where 
  conv_date = date('now','-1 day') and
  gclid is not null 
