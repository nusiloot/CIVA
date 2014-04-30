#!/bin/bash

. bin/config.inc

DESCRIPTION="Export CSV des ventes de moûts pour la DR $DR_CAMPAGNE"

. bin/task.inc

mkdir $TASK_DIR/tmp > /dev/null

EXPORT_FILE=tmp/export_drs_ventes_mouts.csv

bash bin/export_drs_ventes_mouts.sh $DR_CAMPAGNE > $TASK_DIR/$EXPORT_FILE

echo "Télécharger le fichier : $TASK_URL/$EXPORT_FILE"
