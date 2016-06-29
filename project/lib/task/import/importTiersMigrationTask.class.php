<?php

class importTiersMigrationTask extends sfBaseTask
{

  protected $_insee = null;

  protected function configure()
  {
     $this->addArguments(array(
       new sfCommandArgument('file', sfCommandArgument::REQUIRED, 'My import from file'),
     ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'civa'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace        = 'import';
    $this->name             = 'TiersMigration';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [importTiers3|INFO] task does things.
Call it with:

  [php symfony importTiers3|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $nb_not_use = 0;

        $societes = array();

        $lines = file($arguments['file']);

        foreach ($lines as $a) {
            $db2Tiers = new Db2Tiers(explode(',', preg_replace('/"/', '', preg_replace('/[^"]+$/', '', $a))));

            if($db2Tiers->get(Db2Tiers::COL_NO_STOCK) == $db2Tiers->get(Db2Tiers::COL_MAISON_MERE)) {
                $societes[$db2Tiers->get(Db2Tiers::COL_NO_STOCK)] = array();
            }
        }

        foreach ($lines as $a) {
            $db2Tiers = new Db2Tiers(explode(',', preg_replace('/"/', '', preg_replace('/[^"]+$/', '', $a))));

            if(array_key_exists($db2Tiers->get(Db2Tiers::COL_MAISON_MERE), $societes)) {
                continue;
            }

            $societes[$db2Tiers->get(Db2Tiers::COL_NO_STOCK)] = array();
        }

        foreach ($lines as $a) {
            $db2Tiers = new Db2Tiers(explode(',', preg_replace('/"/', '', preg_replace('/[^"]+$/', '', $a))));

            if(array_key_exists($db2Tiers->get(Db2Tiers::COL_NO_STOCK), $societes)) {
                if(isset($societes[$db2Tiers->get(Db2Tiers::COL_NO_STOCK)][$db2Tiers->get(Db2Tiers::COL_NO_STOCK)]) && $this->getInfos($societes[$db2Tiers->get(Db2Tiers::COL_NO_STOCK)][$db2Tiers->get(Db2Tiers::COL_NO_STOCK)], Db2Tiers::COL_CVI) && $db2Tiers->get(Db2Tiers::COL_CVI)) {
                    $societes[$db2Tiers->get(Db2Tiers::COL_NUM)."SPECIAL"][$db2Tiers->get(Db2Tiers::COL_NUM)."SPECIAL"][] = $db2Tiers;
                } else {
                    $societes[$db2Tiers->get(Db2Tiers::COL_NO_STOCK)][$db2Tiers->get(Db2Tiers::COL_NO_STOCK)][] = $db2Tiers;
                }
                continue;
            }

            if(array_key_exists($db2Tiers->get(Db2Tiers::COL_MAISON_MERE), $societes)) {
                $societes[$db2Tiers->get(Db2Tiers::COL_MAISON_MERE)][$db2Tiers->get(Db2Tiers::COL_NO_STOCK)][] = $db2Tiers;
                continue;
            }
        }

        ksort($societes, SORT_NUMERIC);

        foreach($societes as $numSoc => $etablissements) {
            ksort($societes, SORT_NUMERIC);
            if(count($etablissements) == 1) {
                //continue;
            }

            $tiers = $etablissements[$numSoc];

            $societe = $this->importSociete($tiers);

            $num = 1;
            $etablissement = $this->importEtablissement($societe, $tiers, sprintf("%02d", $num));
            $num++;

            foreach($etablissements as $numEt => $tiers) {
                if($numSoc == $numEt) {
                    continue;
                }

                $etablissement = $this->importEtablissement($societe, $tiers, sprintf("%02d", $num));
                $num++;
            }

            echo "------------------------\n";
        }
    }

    protected function importSociete($tiers) {
        $identifiantSociete = $this->getInfos($tiers, Db2Tiers::COL_CVI) ? $this->getInfos($tiers, Db2Tiers::COL_CVI): "C".$this->getInfos($tiers, Db2Tiers::COL_CIVABA);

        if(!$identifiantSociete) {
            return;
        }

        $societe = SocieteClient::getInstance()->find("SOCIETE-".$identifiantSociete);

        if($societe) { return $societe; }

        if(!$societe) {
            $societe = new Societe();
            $societe->setIdentifiant($identifiantSociete);
            $societe->setTypeSociete(SocieteClient::TYPE_OPERATEUR);
            $societe->constructId();
        }

        $societe->setRaisonSociale(preg_replace('/ +/', ' ', trim($this->getInfos($tiers, Db2Tiers::COL_INTITULE). ' '.$this->getInfos($tiers, Db2Tiers::COL_NOM_PRENOM))));
        $societe->setSiret($this->getInfos($tiers, Db2Tiers::COL_SIRET));

        $societe->setAdresse($this->getInfos($tiers, Db2Tiers::COL_ADRESSE_SIEGE));
        $societe->setCodePostal($this->getInfos($tiers, Db2Tiers::COL_CODE_POSTAL_SIEGE));
        $societe->setCommune($this->getInfos($tiers, Db2Tiers::COL_COMMUNE_SIEGE));
        $societe->setInsee($this->getInfos($tiers, Db2Tiers::COL_INSEE_SIEGE));
        $societe->setPays("FR");
        $societe->setTelephoneBureau($this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRO) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRO) ) : null);
        $societe->setTelephonePerso($this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRIVE) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRIVE) ) : null);
        $societe->setFax($this->getInfos($tiers, Db2Tiers::COL_FAX) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_FAX) ) : null);
        $societe->setEmail($this->getInfos($tiers, Db2Tiers::COL_EMAIL));
        $societe->save();

        echo $societe->_id." (".$societe->getRaisonSociale().")\n";

        return $societe;
    }

    protected function importEtablissement($societe, $tiers, $num)
    {
        $identifiantEtablissement = $societe->getIdentifiant().$num;

        //echo $identifiantEtablissement;
        if($etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$identifiantEtablissement, acCouchdbClient::HYDRATE_JSON)) { return $etablissement; }

        $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$identifiantEtablissement);

        if(!$etablissement) {
            $etablissement = new Etablissement();
            $etablissement->setIdSociete($societe->_id);
            $etablissement->setIdentifiant($identifiantEtablissement);
            $etablissement->setFamille($tiers[0]->getFamille());
            $etablissement->constructId();
        }

        $societe->pushContactAndAdresseTo($etablissement);

        $etablissement->setIntitule($this->getInfos($tiers, Db2Tiers::COL_INTITULE));
        $etablissement->setNom(preg_replace('/ +/', ' ', trim($this->getInfos($tiers, Db2Tiers::COL_NOM_PRENOM))));
        $etablissement->setNumInterne($this->getInfos($tiers, Db2Tiers::COL_CIVABA));
        $etablissement->setCvi($this->getInfos($tiers, Db2Tiers::COL_CVI));
        $etablissement->setNoAccises($this->getInfos($tiers, Db2Tiers::COL_NO_ASSICES));
        $etablissement->setFamille($tiers[0]->getFamille());

        $etablissement->setAdresse($this->getInfos($tiers, Db2Tiers::COL_ADRESSE_SIEGE));
        $etablissement->setCodePostal($this->getInfos($tiers, Db2Tiers::COL_CODE_POSTAL_SIEGE));
        $etablissement->setCommune($this->getInfos($tiers, Db2Tiers::COL_COMMUNE_SIEGE));
        $etablissement->setInsee($this->getInfos($tiers, Db2Tiers::COL_INSEE_SIEGE));
        $etablissement->setPays("FR");
        $etablissement->setTelephoneBureau($this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRO) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRO) ) : null);
        $etablissement->setTelephonePerso($this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRIVE) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_TELEPHONE_PRIVE) ) : null);
        $etablissement->setFax($this->getInfos($tiers, Db2Tiers::COL_FAX) ? sprintf('%010d',$this->getInfos($tiers, Db2Tiers::COL_FAX) ) : null);
        $etablissement->setEmail($this->getInfos($tiers, Db2Tiers::COL_EMAIL));
        $etablissement->save();

        echo $etablissement->_id." (".$etablissement->getFamille().")\n";

        return $etablissement;
    }

    protected function getInfos($tiers, $key) {
        foreach($tiers as $t) {
            if($t->get($key)) {

                return $t->get($key);
            }
        }

        return null;
    }

    protected function resolveIdentifiantSociete($etablissements) {
        $printed = false;
        foreach($etablissements as $tiers) {
            foreach($tiers as $t) {
                if($t->get(Db2Tiers::COL_CVI)) {
                    //echo (($printed) ? "PLUSIEURS" : "").$t->get(Db2Tiers::COL_CVI)."\n";
                    $printed = true;
                }
            }
        }
    }

}
