#!/bin/bash

echo "_id;type;db2/num;db2/no_stock;cvi;civaba;cvi_acheteur;intitule;no_accises;nom;commune;declaration_commune;declaration_insee;siren;siret;telephone;fax;qualite;categorie;cave_cooperative;web;exploitant/adresse;exploitant/code_postal;exploitant/commune;exploitant/date_naissance;exploitant/nom;exploitant/sexe;exploitant/telephone;siege/adresse;siege/code_postal;siege/commune;siege/insee_commune"

cat $1 | sed 's/* ()//g' | cut -d ";" -f 1,2,3,4,5,6,7,8,9,10,11,12,13,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40 | grep '*'