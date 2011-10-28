<?php

class exportComptesCsvCask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('tiers_type', sfCommandArgument::REQUIRED, 'Type du tiers : Recoltant|MetteurEnMarche|Acheteur'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'civa'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
        ));

        $this->namespace = 'export';
        $this->name = 'comptes-csv';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [setTiersPassword|INFO] task does things.
Call it with:

  [php symfony maintenanceExportTiersGammaTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        ini_set('memory_limit', '512M');
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tiers = sfCouchdbManager::getClient($arguments['tiers_type'])->getAll(sfCouchdbClient::HYDRATE_JSON);

        $csv = new ExportCsv(array(
                    "login" => "Login",
                    "statut" => "Statut",
                    "mot_de_passe" => "Code de création",
                    "cvi" => "Numéro CVI",
                    "civaba" => "Numéro CIVABA",
                    "siret" => "Numéro Siret",
                    "qualite" => "Qualité",
                    "civilite" => "Civilité",
                    "nom" => "Nom Prénom",
                    "adresse" => "Adresse",
                    "code postal" => "Code postal",
                    "commune" => "Commune"
                ));

        $validation = array(
            "login" => array("required" => true, "type" => "string"),
            "statut" => array("required" => true, "type" => "string"),
            "mot_de_passe" => array("required" => true, "type" => "string"),
            "cvi" => array("required" => false, "type" => "string"),
            "civaba" => array("required" => false, "type" => "string"),
            "siret" => array("required" => false, "type" => "string"),
            "qualite" => array("required" => false, "type" => "string"),
            "civilite" => array("required" => false, "type" => "string"),
            "nom" => array("required" => true, "type" => "string"),
            "adresse" => array("required" => false, "type" => "string"),
            "code postal" => array("required" => false, "type" => "string"),
            "commune" => array("required" => false, "type" => "string")
        );

        foreach ($tiers as $t) {
            if (count($t->compte) == 0) {
                $this->logSection($t->cvi, "COMPTE VIDE", null, 'ERROR');
                continue;
            }
            foreach ($t->compte as $id_compte) {
                $compte = sfCouchdbManager::getClient()->retrieveDocumentById($id_compte, sfCouchdbClient::HYDRATE_JSON);
                if ($compte) {
                    $intitule = $t->intitule;
                    $nom = $t->nom;
                    $adresse = $t->siege;
                    if (!$adresse->adresse && isset($t->exploitant)) {
                        if ($t->exploitant->nom) {
                            $intitule = $t->exploitant->sexe;
                            $nom = $t->exploitant->nom;
                        }
                        $adresse = $t->exploitant;
                    }

                    if (substr($compte->mot_de_passe, 0, 6) == "{TEXT}") {
                        $mot_de_passe = preg_replace('/^\{TEXT\}/', "", $compte->mot_de_passe);
                    } else {
                        $mot_de_passe = "Compte déjà créé";
                    }
                    
                    try {
                        $csv->add(array(
                            "login" => $compte->login,
                            "statut" => $compte->statut,
                            "mot_de_passe" => $mot_de_passe,
                            "cvi" => $this->getTiersField($t, 'cvi'),
                            "civaba" => $this->getTiersField($t, 'civaba'),
                            "siret" => $this->getTiersField($t, 'siret'),
                            "qualite" => $this->getTiersField($t, 'qualite'),
                            "civilite" => $intitule,
                            "nom" => $nom,
                            "adresse" => $adresse->adresse,
                            "code postal" => $adresse->code_postal,
                            "commune" => $adresse->commune
                         ), $validation);
                    } catch (Exception $exc) {
                        $this->logSection($t->cvi, $exc->getMessage(), null, 'ERROR');
                    }
                } else {
                    $this->logSection($t->cvi, "COMPTE INEXISTANT", null, 'ERROR');
                }
            }
        }

        echo $csv->output(false);
    }

    protected function getTiersField($tiers, $field, $default = null) {
        $value = $default;
        if (isset($tiers->{$field})) {
            $value = $tiers->{$field};
        }
        return $value;
    }

}
