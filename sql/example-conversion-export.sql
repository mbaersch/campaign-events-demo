select distinct
  gclid as 'Google Click ID',
  conv_name as 'Conversion Name', 
  (conv_date || ' 23:59:00') as 'Conversion Time',
  conv_value as 'Conversion Value',
  'EUR' as 'Conversion Currency'
from conversions left join sessions 
  on conversions.hashValue = sessions.hashValue
where 
  gclid is not null and
  conv_date = date('now','-1 day')