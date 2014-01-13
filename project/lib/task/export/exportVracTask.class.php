<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class exportDSCivaTask
 * @author mathurin
 */
class exportVracTask extends sfBaseTask
{

    protected function configure()
    {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('folderPath', sfCommandArgument::REQUIRED, 'folderPath'),
            new sfCommandArgument('date_begin', sfCommandArgument::REQUIRED, 'date'),
            new sfCommandArgument('date_end', sfCommandArgument::OPTIONAL, 'date'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'civa'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default')
        ));

        $this->namespace = 'export';
        $this->name = 'vrac';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [exportVrac|INFO] task does things.
Call it with:

  [php symfony export:vrac|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        set_time_limit(0);
        
        $configCepappctr = new Cepappctr();
        $date_begin = $arguments['date_begin'];
        $date_end = ($arguments['date_end'])? $arguments['date_end'] : date("Y-m-d", mktime(0, 0, 0, date('m'), date('d')-1, date('y'))); 
        $fin = ($arguments['date_end'])? $arguments['date_end'] : date("Y-m-d");
        $dates = array($date_begin, $date_end);
        $filenameHeader = str_replace('-', '', $date_begin).'-'.str_replace('-', '', $fin).'.';
        
        /*
         * CREATION
         */
        $types = array('C', 'M');
        foreach ($types as $type) {
        	$csvDecven = new ExportCsv();
	        $csvDdecvn = new ExportCsv();
			$contrats = VracContratsView::getInstance()->findForDb2Export($dates, $type);
	        foreach($contrats as $contrat) {
	            $valuesContrat = $contrat->value;
	            $isInCreation = ($valuesContrat[VracContratsView::VALUE_CREATION])? true : false;
	            unset($valuesContrat[VracContratsView::VALUE_CREATION]);
            	if ($type == 'C') {
            		$valuesContrat[VracContratsView::VALUE_TOTAL_VOLUME_ENLEVE] = $valuesContrat[VracContratsView::VALUE_TOTAL_VOLUME_PROPOSE];
            	}
	        	if ($type == 'C' && !$isInCreation) {
	            	continue;
	            }
	            $produits = VracProduitsView::getInstance()->findForDb2Export($contrat->value[VracContratsView::VALUE_NUMERO_ARCHIVE]);
	            $i = 0;
	            $dateRetiraison = $valuesContrat[VracContratsView::VALUE_DATE_CIRCULATION];
	            $dateRetiraisonTmp = null;
	            foreach ($produits as $produit) {
	            	$i++;
	            	if ($type == 'M' && !$produit->value[VracProduitsView::VALUE_DATE_CIRCULATION]) {
	            		continue;
	            	}
	            	$valuesProduit = $produit->value;
	            	$valuesProduit[VracProduitsView::VALUE_CODE_APPELLATION] = $this->getCodeAppellation($valuesProduit[VracProduitsView::VALUE_CODE_APPELLATION]);
	            	$valuesProduit[VracProduitsView::VALUE_CEPAGE] = $this->getCepage($valuesProduit[VracProduitsView::VALUE_CEPAGE]);
	            	$valuesProduit[VracProduitsView::VALUE_CODE_CEPAGE] = $configCepappctr->getOrdreMercurialeByPair($valuesProduit[VracProduitsView::VALUE_CODE_APPELLATION], $valuesProduit[VracProduitsView::VALUE_CEPAGE]);
	            	$valuesProduit[VracProduitsView::VALUE_NUMERO_ORDRE] = $i;
	            	$valuesProduit[VracProduitsView::VALUE_PRIX_UNITAIRE] = $valuesProduit[VracProduitsView::VALUE_PRIX_UNITAIRE] / 100;
	            	$valuesProduit[VracProduitsView::VALUE_TOP_MERCURIALE] = $this->getTopMercuriale($valuesProduit);
	            	if ($type == 'C') {
	            		$valuesProduit[VracProduitsView::VALUE_VOLUME_ENLEVE] = $valuesProduit[VracProduitsView::VALUE_VOLUME_PROPOSE];
	            		$valuesProduit[VracProduitsView::VALUE_DATE_CIRCULATION] = $valuesContrat[VracContratsView::VALUE_DATE_SAISIE];
	            	}
	            	if (!$dateRetiraisonTmp || ($valuesProduit[VracProduitsView::VALUE_DATE_CIRCULATION] && $valuesProduit[VracProduitsView::VALUE_DATE_CIRCULATION] < $dateRetiraisonTmp)) {
	            		$dateRetiraisonTmp = $valuesProduit[VracProduitsView::VALUE_DATE_CIRCULATION];
	            	}
	            	$csvDdecvn->add($valuesProduit);
	            }
	            $valuesContrat[VracContratsView::VALUE_DATE_CIRCULATION] = ($type == 'M' && $dateRetiraisonTmp)? $dateRetiraisonTmp : $dateRetiraison;
	            $csvDecven->add($valuesContrat);
	        	if ($type == 'C') {
	            	$c = VracClient::getInstance()->find($contrat->key);
	            	$c->date_export_creation = date('Y-m-d');
	            	$c->save();
	            }
	        }
	
	        $decven = $csvDecven->output();
	        $ddecvn = $csvDdecvn->output();
	        
	        $folderPath = $arguments['folderPath'];
	        $path_ddecvn = $folderPath.'/'.$filenameHeader.'DDECVN-'.$type;
	        $path_decven = $folderPath.'/'.$filenameHeader.'DECVEN-'.$type;
	        
	        $file_ddecvn = fopen($path_ddecvn, 'w');
	        fwrite($file_ddecvn, "\xef\xbb\xbf");
	        fclose($file_ddecvn);
	        
	        $file_decven = fopen($path_decven, 'w');
	        fwrite($file_decven, "\xef\xbb\xbf");
	        fclose($file_decven);
	        
	        file_put_contents($path_ddecvn, $ddecvn);        
	        file_put_contents($path_decven, $decven);
        }
        
        $modele_decven = array(
            "numero_archive" => null,
            "type_contrat" => null,
            "mercuriales" => null,
            "montant_cotisation" => null, 
            "montant_cotisation_paye" => null, 
            "mode_de_paiement" => null,
            "cvi_acheteur" => null,
            "type_acheteur" => null,
            "tca" => null,
            "cvi_vendeur" => null,
            "type_vendeur" => null,
            "numero_contrat" => null,
            "daa" => null,
            "date_arrivee" => null,
            "date_traitement" => null,
            "date_saisie" => null,
            "date_circulation" => null,
            "numero_courtier" => null, // numero tiers 90000...
            "reccod" => null,
            "total_volume_propose" => null,
            "total_volume_enleve" => null,
            "quantite_transferee" => null,
            "top_suppression" => null,
            "top_instance" => null,
            "nombre_contrats" => null,
            "heure_traitement" => null,
            "utilisateur" => null,
            "date_modif" => null,
            );
            
        $modele_ddecvn = array(
            "numero_archive" => null,
            "code_cepage" => null,
            "cepage" => null,
            "code_appellation" => null,
            "numero_ordre" => null, 
            "volume_propose" => null,
            "volume_enleve" => null,
            "prix_unitaire" => null, 
            "degre" => null,
            "top_mercuriale" => null,
            "millesime" => null,
            "vtsgn" => null,
            "date_circulation" => null,
            );
            
        echo "EXPORT fini\n";
    }
    
    protected function getTopMercuriale ($ligne) 
    {
    	$top_mercuriale = null;
    	if ($ligne[VracProduitsView::VALUE_CODE_APPELLATION] == 1) {
    		if ($ligne[VracProduitsView::VALUE_VTSGN]) {
    			$top_mercuriale = "N";
    		}
    		if ($ligne[VracProduitsView::VALUE_CEPAGE] == "KL") {
    			$top_mercuriale = "N";
    		}
    	}
    	return $top_mercuriale;
    }
    
    protected function getCodeAppellation($appellation)
    {
    	$code = 1;
    	switch ($appellation) {
                case 'CREMANT':
                    $code = 2;
                    break;
                case 'GRDCRU':
                    $code = 3;
                    break;
             	case "COMMUNALE":
                    $code = 7;
             	case "LIEUDIT":
                    $code = 8;
                default:
                    $code = 1;
        }
        return $code;
    }
    
    protected function getCepage($cepage)
    {
    	if ($cepage == "BL" || $cepage == "RS") {
    		$cepage = "CR";
    	}
    	return $cepage;
    }
}