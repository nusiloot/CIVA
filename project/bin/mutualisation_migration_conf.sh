. bin/config.inc

configurations=('CONFIGURATION-20070801' 'CONFIGURATION-20110801' 'CONFIGURATION-20120801' 'CONFIGURATION-20130801' 'CONFIGURATION-20140801' 'CONFIGURATION-20150801')


for configurationid in "${configurations[@]}"
do
    curl -X DELETE "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$configurationid"?rev=$(curl -sX GET "http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/$configurationid" | grep -Eo '"_rev":"[a-z0-9-]+"' | sed 's/"//g' | sed 's/_rev://')
done

php symfony import:configuration CONFIGURATION-20070801 data/configuration/2010
php symfony import:configuration CONFIGURATION-20110801 data/configuration/2011
php symfony import:configuration CONFIGURATION-20120801 data/configuration/2012
php symfony import:configuration CONFIGURATION-20130801 data/configuration/2013
php symfony import:configuration CONFIGURATION-20140801 data/configuration/2014
php symfony import:configuration CONFIGURATION-20150801 data/configuration/2015
