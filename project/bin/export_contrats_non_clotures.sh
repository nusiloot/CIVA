#!/bin/bash

. bin/config.inc

echo "campagne;date de saisie;id couchdb;numéro de visa;statut;type;identifiant proprietaire;raison sociale proprietaire;date validation proprietaire"

curl -s http://$COUCHDBDOMAIN:$COUCHDBPORT/$COUCHDBBASE/_design/VRAC/_view/tous |grep -E "(VALIDE|ENLEVEMENT)" | sed -r 's/\]//g' | sed 's/\[//g' | sed 's/{//g' | sed 's/}//g' |cut -d "," -f 1,2,4,5,6,7,9,10,11,13,14,15,17,18,19,20,23,25 | sed 's/"id"://' | sed 's/"value":"role":"mandataire","raison_sociale"://' |sed 's/"date_validation"://' | sed 's/"raison_sociale"://g' | sed 's/"date_validation"://g' | sed 's/"date"://g' | sed 's/"numero_visa"://' |sed 's/"type_contrat"://' | grep '"is_proprietaire":1' |sed 's/"key"://' | sed 's/"mandataire":"identifiant"://' | sed 's/"value":"role":"mandataire","soussignes":"vendeur":"identifiant"://' |sed 's/"value":"role":"acheteur","soussignes":"vendeur":"identifiant"://' | sed 's/"value":"role":"vendeur","soussignes":"vendeur":"identifiant"://' | sed 's/"acheteur":"identifiant"://' | sed 's/"vendeur":"identifiant"://' | awk -F ',' '{ if($2 == $5) { raison_sociale = $6; date_validation=$7 } if($2 == $8) { raison_sociale= $9; date_validation=$10 } if($2 == $11) { raison_sociale = $12; date_validation = $13 }  print $3 ";" $15 ";" $1 ";" $16 ";" $4 ";" $17 ";" $2 ";" raison_sociale ";" date_validation }' | sed 's/ETABLISSEMENT-//' | sed 's/ACHAT-//' | sed 's/null//g' | sort -r