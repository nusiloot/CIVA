#!/bin/bash

. bin/task.inc

DESCRIPTION="Export CSV des déclarations de Stocks pour la période $DS_PERIODE"

. bin/task_start.inc

mkdir -m 777 $TASK_DIR/tmp > /dev/null

EXPORT_FILE=tmp/export_ds.csv
echo "$DS_PERIODE"
echo "$TASK_DIR/$EXPORT_FILE"
bash bin/export_ds_csv.sh "$DS_PERIODE" > $TASK_DIR/$EXPORT_FILE

echo "[Télécharger le fichier]($TASK_URL/$EXPORT_FILE)"