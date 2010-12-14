<?php

/**
 * printable actions.
 *
 * @package    civa
 * @subpackage printable
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class exportActions extends sfActions {

    public static function sortXML($a, $b) {
        $a = preg_replace('/L/', '', $a);
        $b = preg_replace('/L/', '', $b);
        return $a > $b;
    }

    private static $type2douane = array('negoces' => 'L6', 'mouts' => 'L7', 'cooperatives' => 'L8');
    private function setAcheteursForXml(&$xml, $obj, $type) {
        $acheteurs = array();
        foreach($obj->getVolumeAcheteurs($type) as $cvi => $volume) {
            $item = array('numCvi' => $cvi, 'volume' => $volume);
            $acheteurs[] = $item;
        }
        if(count($acheteurs) > 0) {
            $xml[self::$type2douane[$type]] = $acheteurs;
        }
    }

    public function executeXml(sfWebRequest $request) {
        $tiers = $this->getUser()->getTiers();
        $this->annee = $this->getRequestParameter('annee', null);
        $key = 'DR-'.$tiers->cvi.'-'.$this->annee;
        $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
        try {
            if (!$dr->updated)
                throw new Exception();
        }catch(Exception $e) {
            $dr->update();
            $dr->save();
        }
        $xml = array();
        foreach ($dr->recolte->getConfigAppellations() as $appellation_config) {
            if (!$dr->recolte->exist($appellation_config->getKey())) {
                continue;
            }
            $appellation = $dr->recolte->get($appellation_config->getKey());
            foreach ($appellation->getConfig()->getLieux() as $lieu_config) {
                if (!$appellation->exist($lieu_config->getKey())) {
                    continue;
                }
                $lieu = $appellation->get($lieu_config->getKey());
                //Comme il y a plusieurs acheteurs par lignes, il faut passer par une structure intermédiaire
                $acheteurs = array();
                $total = array();
                //	$total['hash'] = $lieu->gethash();
                $total['L1'] = $lieu->getCodeDouane();
                $total['L3'] = 'B';
                //$total['mentionVal'] = '';
                $total['L4'] = $lieu->getTotalSuperficie();
                $total['exploitant'] = array();
                $total['exploitant']['L5'] = $lieu->getTotalVolume();
                $this->setAcheteursForXml($total['exploitant'], $lieu, 'negoces');
                $this->setAcheteursForXml($total['exploitant'], $lieu, 'mouts');
                $this->setAcheteursForXml($total['exploitant'], $lieu, 'cooperatives');
                $total['exploitant']['L9'] = $lieu->getTotalCaveParticuliere();
                $total['exploitant']['L10'] = $lieu->getTotalCaveParticuliere() + $lieu->getTotalVolumeAcheteurs('cooperatives'); //Volume revendique non negoces
                $total['exploitant']['L11'] = 0; //HS
                $total['exploitant']['L12'] = 0; //HS
                $total['exploitant']['L13'] = 0; //HS
                $total['exploitant']['L14'] = 0; //Vin de table + Rebeches
                $total['exploitant']['L15'] = $lieu->volume_revendique; //Volume revendique
                $total['exploitant']['L16'] = $lieu->dplc; //DPLC
                $total['exploitant']['L17'] = 0; //HS
                $total['exploitant']['L18'] = 0; //HS
                $total['exploitant']['L19'] = 0; //HS
                $colass = null;
                foreach ($lieu->getConfig()->getCepages() as $cepage_config) {
                    if (!$lieu->exist($cepage_config->getKey())) {
                        continue;
                    }
                    $cepage = $lieu->get($cepage_config->getKey());
                    foreach ($cepage->detail as $detail) {
                        //	    echo "dhash: ".$detail->getHash()."<br/>\n";
                        $col = array();
                        //	    $col['hash'] = $detail->getHash();
                        $col['L1'] = $detail->getCodeDouane();

                        // SI PAS D'AUTRE AOC
                        if ($appellation->getKey() == 'appellation_VINTABLE' && $dr->recolte->getAppellations()->count() == 1) {
                            $col['L1'] .= 'O';
                        }

                        $col['L3'] = 'B';
                        $col['mentionVal'] = $detail->denomination;
                        $col['L4'] = $detail->superficie;
                        //$total['L4'] += $detail->superficie;
                        if (isset($detail->motif_non_recolte) && $detail->motif_non_recolte)
                            $col['motifSurfZero'] = $detail->motif_non_recolte;
                        $col['exploitant'] = array();
                        $col['exploitant']['L5'] = $detail->volume ; //Volume total sans lies

                        $this->setAcheteursForXml($col['exploitant'], $detail, 'negoces');
                        $this->setAcheteursForXml($col['exploitant'], $detail, 'mouts');
                        $this->setAcheteursForXml($col['exploitant'], $detail, 'cooperatives');

                        //$total['exploitant']['L5'] += $col['exploitant']['L5'];
                        $col['exploitant']['L9'] = $detail->cave_particuliere; //Volume revendique sur place
                        //$total['exploitant']['L9'] += $detail->cave_particuliere; //Volume revendique sur place
                        $col['exploitant']['L10'] = $detail->cave_particuliere + $detail->getTotalVolumeAcheteurs('cooperatives'); //Volume revendique non negoces
                        $col['exploitant']['L11'] = 0; //HS
                        $col['exploitant']['L12'] = 0; //HS
                        $col['exploitant']['L13'] = 0; //HS
                        $col['exploitant']['L14'] = 0; //Vin de table + Rebeches
                        $col['exploitant']['L15'] = 0; //Volume revendique
                        $col['exploitant']['L16'] = 0; //DPLC
                        $col['exploitant']['L17'] = 0; //HS
                        $col['exploitant']['L18'] = 0; //HS
                        $col['exploitant']['L19'] = 0; //HS


                        /* if ($detail->exist('cooperatives'))
	      foreach ($detail->cooperatives as $coop)  {
		if (!isset($col['exploitant']['L8']))
		  $col['exploitant']['L8'] = array();
		$col['exploitant']['L8'][count($col['exploitant']['L8'])] = array('numCvi' => $coop->cvi, 'volume' => $coop->quantite_vendue);
		//$col['exploitant']['L10'] += $coop->quantite_vendue;
	      }
	    //$col['exploitant']['L10'] += $detail->cave_particuliere;
	    //$total['exploitant']['L10'] += $col['exploitant']['L10'];

	    if ($detail->exist('negoces'))
	      foreach ($detail->negoces as $n)  {
		if (!isset($col['exploitant']['L6']))
		  $col['exploitant']['L6'] = array();
		$col['exploitant']['L6'][count($col['exploitant']['L6'])] = array('numCvi' => $n->cvi, 'volume' => $n->quantite_vendue);
	      }

	    if ($detail->exist('mouts'))
	      foreach ($detail->mouts as $n)  {
		if (!isset($col['exploitant']['L7']))
		  $col['exploitant']['L7'] = array();
		$col['exploitant']['L7'][count($col['exploitant']['L7'])] = array('numCvi' => $n->cvi, 'volume' => $n->quantite_vendue);
	      }
            *
            * */


                        /* $acheteurs = $this->setAcheteurType($acheteurs, 'negoces', $detail);
	    $acheteurs = $this->setAcheteurType($acheteurs, 'mouts', $detail);
	    $acheteurs = $this->setAcheteurType($acheteurs, 'cooperatives', $detail);*/

                        if (($cepage->getKey() == 'cepage_RB' && $appellation->getKey() == 'appellation_CREMANT') || $appellation->getKey() == 'appellation_VINTABLE') {
                            $col['exploitant']['L14'] = $detail->volume;
                            //$total['exploitant']['L14'] =+ $detail->volume;
                        } else {
                            $col['exploitant']['L15'] = $detail->volume_revendique;
                            //$total['exploitant']['L15'] = $detail->volume_revendique;
                            //$col['exploitant']['L16'] = $detail->volume_dplc;
                        }


                        uksort($col['exploitant'], 'exportActions::sortXML');
                        //$total['exploitant']['L16'] = $detail->volume_dplc;
                        //echo $detail->cepage .' hey<br />';
                        if ($cepage->getKey() == 'cepage_RB' && $appellation->getKey() == 'appellation_CREMANT') {
                            unset($col['L3'], $col['L4'], $col['mentionVal']);
                            $colass = $col;
                        } else {
                            $xml[] = $col;
                        }
                    }
                }

                $total['exploitant']['L5'] += $total['exploitant']['L5'] * $dr->getRatioLies();  //Volume total avec lies
                $total['exploitant']['L10'] += $total['exploitant']['L9'] * $dr->getRatioLies();
                $total['exploitant']['L10'] += $total['exploitant']['L10'] * $dr->getRatioLies();
                uksort($total['exploitant'], 'exportActions::sortXML');
                //Ajout des acheteurs
                /*foreach ($acheteurs as $cvi => $v) {
	  //$total['exploitant'][] = $v;
	}*/
                if ($colass) {
                    $total['colonneAss'] = $colass;
                }
                if ($lieu->getAppellation()->getAppellation() != 'KLEVENER' && $lieu->getAppellation()->getAppellation() != 'VINTABLE') {
                    $xml[] = $total;
                }
            }
        }
        $this->xml = $xml;
        $this->dr = $dr;
        $this->setLayout(false);
        $this->response->setContentType('text/xml');
        /*$this->response->setHttpHeader('Content-disposition', 'attachment; filename='.$this->dr->cvi.'.xml', true);
    $this->response->setHttpHeader('Pragma', 'o-cache', true);
    $this->response->setHttpHeader('Expires', '0', true);*/
    }

    private function ajaxPdf() {
        sfConfig::set('sf_web_debug', false);
        return $this->renderText($this->generateUrl('print', array('annee'=>$this->annee)));
    }
    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executePdf(sfWebRequest $request) {
        $tiers = $this->getUser()->getTiers();
        $this->annee = $this->getRequestParameter('annee', null);

        $key = 'DR-'.$tiers->cvi.'-'.$this->annee;
        $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
        $this->setLayout(false);

        try {
            if (!$dr->updated)
                throw new Exception();
        }catch(Exception $e) {
            $dr->update();
            $dr->save();
        }
        $this->forward404Unless($dr);

        $this->document = new DocumentDR($dr, $tiers, array($this, 'getPartial'), $this->getRequestParameter('output', 'pdf'));

        if($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generatePDF();

        if ($request->getParameter('ajax')) {
            return $this->ajaxPdf();
        }

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    public function executeCsvTiers(sfWebRequest $request) {
        set_time_limit('240');
        ini_set('memory_limit', '512M');
        $tiers = sfCouchdbManager::getClient("Tiers")->getAll(sfCouchdbClient::HYDRATE_JSON);
        $values = array();
        foreach ($tiers as $item) {
            if ($item->recoltant == 1 && $item->cvi != "7523700100") {
                $ligne = array();
                $ligne[] = $item->cvi;
                if (strpos('{TEXT}', $item->mot_de_passe) === false) {
                    $ligne[] = str_replace('{TEXT}', '', $item->mot_de_passe);
                } else {
                    $ligne[] = "code activé";
                }
                $ligne[] = $item->nom;
                $ligne[] = $item->siege->adresse;
                $ligne[] = $item->siege->code_postal;
                $ligne[] = $item->siege->commune;
                $ligne[] = $item->no_accises;

                $values[] = $ligne;
            }
        }

        $this->setResponseCsv('tiers.csv');
        return $this->renderText(Tools::getCsvFromArray($values));
    }

    public function executeCsvTiersDREncours(sfWebRequest $request) {
        set_time_limit('240');
        ini_set('memory_limit', '512M');
        $tiers = sfCouchdbManager::getClient("Tiers")->getAll(sfCouchdbClient::HYDRATE_JSON);
        $values = array();
        foreach ($tiers as $item) {
            if ($item->cvi != "7523700100") {
                $dr = sfCouchdbManager::getClient("DR")->retrieveByCampagneAndCvi($item->cvi, $this->getUser()->getCampagne(), sfCouchdbClient::HYDRATE_JSON);
                if ($dr && (!isset($dr->validee) || !$dr->validee)) {
                    $ligne = array();
                    $ligne[] = $item->cvi;
                    $ligne[] = $item->nom;
                    $ligne[] = $item->declaration_commune;
                    $ligne[] = $item->telephone;
                    $ligne[] = $item->email;
                    $inscrit = 'non_inscrit';
                    if (substr($item->mot_de_passe, 0, 6) !== "{TEXT}") {
                        $inscrit = 'inscrit';
                    }
                    $ligne[] = $inscrit;
                    $ligne[] = $dr->etape;
                    $values[] = $ligne;
                }
            }
        }

        $this->setResponseCsv('tiers.csv');
        return $this->renderText(Tools::getCsvFromArray($values));
    }

    public function executeCsvTiersNonValideeCiva(sfWebRequest $request) {
        set_time_limit('240');
        ini_set('memory_limit', '512M');
        $tiers = sfCouchdbManager::getClient("Tiers")->getAll(sfCouchdbClient::HYDRATE_JSON);
        $values = array();
        foreach ($tiers as $item) {
            if ($item->cvi != "7523700100") {
                $dr = sfCouchdbManager::getClient("DR")->retrieveByCampagneAndCvi($item->cvi, $this->getUser()->getCampagne(), sfCouchdbClient::HYDRATE_JSON);
                if ($dr && isset($dr->validee) && $dr->validee && (!isset($dr->modifiee) || !$dr->modifiee)) {
                    $ligne = array();
                    $ligne[] = $item->cvi;
                    $ligne[] = $item->nom;
                    $ligne[] = $item->declaration_commune;
                    $ligne[] = $item->telephone;
                    $ligne[] = $item->email;
                    $inscrit = 'non_inscrit';
                    if (substr($item->mot_de_passe, 0, 6) !== "{TEXT}") {
                        $inscrit = 'inscrit';
                    }
                    $ligne[] = $inscrit;
                    $ligne[] = $dr->etape;
                    $values[] = $ligne;
                }
            }
        }
        $this->setResponseCsv('tiers.csv');
        return $this->renderText(Tools::getCsvFromArray($values));
    }

    public function executeCsvTiersModifications(sfWebRequest $request) {
        set_time_limit(0);
        $tiers_ids = sfCouchdbManager::getClient("Tiers")->getAll(sfCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
        
        $values = array();
        $values[] = array("Exploitation - N° CVI",
                        "Exploitation - N° SIRET",
                        "Exploitation - Nom",
                        "Exploitation - Adresse",
                        "Exploitation - Code Postal",
                        "Exploitation - Commune",
                        "Exploitation - Téléphone",
                        "Exploitation - Fax",
                        "Exploitant - Nom",
                        "Exploitant - Adresse",
                        "Exploitant - Naissance",
                        "Exploitant - Téléphone");
        foreach($tiers_ids as $id) {
            $data_revs = sfCouchdbManager::getClient("Tiers")->revs_info(true)->retrieveDocumentById($id, sfCouchdbClient::HYDRATE_JSON);
            $revs = $data_revs->_revs_info;
            if (count($revs) < 4) {
                $first_revision = $revs[count($revs)-3]->rev;
            } else {
                $first_revision = $revs[count($revs)-2]->rev;
            }
            $first_revision = $revs[count($revs)-1]->rev;
            $tiers_old = sfCouchdbManager::getClient("Tiers")->rev($first_revision)->retrieveDocumentById($id, sfCouchdbClient::HYDRATE_ARRAY);
            $tiers_current = sfCouchdbManager::getClient("Tiers")->retrieveDocumentById($id, sfCouchdbClient::HYDRATE_ARRAY);
            $values_changed = Tools::array_diff_recursive($tiers_current, $tiers_old);
            $value = array();
            $value[] = $this->formatModifiedValue(array('cvi' => true), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('siret' => true), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('nom' => true), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('siege' => array('adresse' => true)), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('siege' => array('code_postal' => true)), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('siege' => array('commune' => true)), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('telephone' => true), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('fax' => true), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('exploitant' => array('nom' => true)), $tiers_current, $values_changed);
            $value[] = $this->formatModifiedValue(array('exploitant' => array('adresse' => true)), $tiers_current, $values_changed);
            $value[] = preg_replace('/(\d+)\-(\d+)\-(\d+)/', '\3/\2/\1', $this->formatModifiedValue(array('exploitant' => array('date_naissance' => true)), $tiers_current, $values_changed));
            $value[] = $this->formatModifiedValue(array('exploitant' => array('telephone' => true)), $tiers_current, $values_changed);

            $keys_used = array('cvi', 'siret', 'nom', 'siege', 'telephone', 'fax', 'exploitant');
            $nb_change = 0;
            foreach($keys_used as $key_use) {
                if (array_key_exists($key_use, $values_changed)) {
                    $nb_change++;
                }
            }

            if ($nb_change > 0) {
                $values[] = $value;
            }
        }
        
        $this->setResponseCsv('tiers-modifications.csv');
        return $this->renderText(Tools::getCsvFromArray($values));
    }

    protected function setResponseCsv($filename) {
        $this->response->setContentType('application/csv');
        $this->response->setHttpHeader('Content-disposition', 'filename='.$filename, true);
        $this->response->setHttpHeader('Pragma', 'o-cache', true);
        $this->response->setHttpHeader('Expires', '0', true);
    }

    protected function formatModifiedValue($keys, $values, $values_changed, $indicator = '*') {
        foreach($keys as $key => $value_key) {
            if (array_key_exists($key, $values_changed)) {
                if (is_array($value_key)) {
                    return $this->formatModifiedValue($value_key, $values[$key], $values_changed[$key]);
                } else {
                    return $indicator.$values[$key];
                }
            } elseif (is_array($value_key)) {
                return $this->formatModifiedValue($value_key, $values[$key], array());
            } else {
                return $values[$key];
            }
        }
        return '';
    }

    public function executeSendPdf(sfWebRequest $request) {

        $tiers = $this->getUser()->getTiers();
        $dr = $this->getUser()->getDeclaration();

        $document = new DocumentDR($dr, $tiers, array($this, 'getPartial'));
        $document->generatePDF();

        $pdfContent = $document->output();

        $mess = 'Bonjour '.$tiers->nom.',

Vous trouverez ci-joint votre déclaration de récolte que vous venez de valider.

Cordialement,

Le CIVA';

        //send email
        try {

             $message = Swift_Message::newInstance()
                          ->setFrom(array('ne_pas_repondre@civa.fr' => "Webmaster Vinsalsace.pro"))
                          ->setTo($tiers->email)
                          ->setSubject('CIVA - Votre déclaration de récolte')
                          ->setBody($mess);
            

            $file_name = $dr->_id.'.pdf';

            $attachment = new Swift_Attachment($pdfContent, $file_name, 'application/pdf');
            $message->attach($attachment);

            $this->getMailer()->send($message);

            $this->emailSend = true;

        }catch(Exception $e) {

            $this->emailSend = false;
        }

    }
}
