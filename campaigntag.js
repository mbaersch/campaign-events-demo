!function(l,a,z,e,r){e="/campaignping/"
a=/(gclid|wbraid)=.+/;z=/(danke|thank-you)\.html/
l=document.location.href;r=(l.match(a))?"a":(l.match(z))?"z":null
if(!r) return;(new Image()).src=e+"?"+r+"="+Math.random() 
}();