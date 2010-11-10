<?php

/**
 * printable actions.
 *
 * @package    civa
 * @subpackage printable
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class exportActions extends sfActions
{

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
  
  public function executeXml(sfWebRequest $request) 
  {
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
      foreach ($appellation->getConfig()->getLieux() as $lieu_config)  {
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
	    $col['exploitant']['L10'] = $detail->cave_particuliere + $detail->getTotalVolumeAcheteurs(); //Volume revendique non negoces
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
              $col['exploitant']['L16'] = $detail->volume_dplc;
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
	$total['exploitant']['L10'] += $total['exploitant']['L10'] * $dr->getRatioLies();
	uksort($total['exploitant'], 'exportActions::sortXML');
	//Ajout des acheteurs
	/*foreach ($acheteurs as $cvi => $v) {
	  //$total['exploitant'][] = $v;
	}*/
	if ($colass) {
	  $total['colonneAss'] = $colass;
	}
	if ($lieu->getAppellation()->getAppellation() != 'KLEVENER')
	  $xml[] = $total;
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
  public function executePdf(sfWebRequest $request)
  {
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

    /*$validee = 'Non Validée';
    if ($dr->exist('validee')) {
      $validee = 'Déclaration validée le '.$dr->getDateValideeFr();
      if ($dr->exist('modifiee') && $dr->modifiee != $dr->validee) {
	$validee .= ' et modifiée le '.$dr->getDateModifieeFr();
      }
    }

    $header = $tiers->intitule.' '.$tiers->nom."\nCommune de déclaration : ".$dr->declaration_commune."\n".$validee;

    if ($this->getRequestParameter('output', 'pdf') == 'html') {
      $this->document = new PageableHTML('Déclaration de récolte '.$this->annee, $header, $this->annee.'_DR_'.$tiers->cvi.'_'.$dr->_rev.'.pdf');
    }else {
      $this->document = new PageablePDF('Déclaration de récolte '.$this->annee, $header, $this->annee.'_DR_'.$tiers->cvi.'_'.$dr->_rev.'.pdf');
    }

    if($request->getParameter('force'))
      $this->document->removeCache();

    $this->nb_pages = 0;
    if (!$this->document->isCached())
      foreach ($dr->recolte->getConfigAppellations() as $appellation_config) {
        if ($dr->recolte->exist($appellation_config->getKey())) {
            $appellation = $dr->recolte->get($appellation_config->getKey());
            foreach ($appellation->getConfig()->filter('^lieu') as $lieu) {
              if (!$appellation->exist($lieu->getKey()))
                continue;
              $lieu = $appellation->{$lieu->getKey()};
              $this->createAppellationLieu($lieu, $tiers);
            }
        }
      }

    $this->document->generatePDF();

    if ($request->getParameter('ajax')) {
      return $this->ajaxPdf();
    }
    $this->document->addHeaders($this->getResponse());

    return $this->renderText($this->document->output());*/
  }

//  private function createAppellationLieu($lieu, $tiers) {
//    $colonnes = array();
//    $afterTotal = array();
//    $acheteurs = $lieu->acheteurs;
//    $cpt = 0;
//    foreach ($lieu->getConfig()->filter('^cepage_') as $cepage) {
//      if (!$lieu->exist($cepage->getKey()))
//	continue;
//      $cepage = $lieu->{$cepage->getKey()};
//      $i = 0;
//      foreach ($cepage->detail as $detail) {
//	$c = array();
//	$c['type'] = 'detail';
//	$c['cepage'] = $cepage->getLibelle();
//	$c['denomination'] = $detail->denomination;
//	$c['vtsgn'] = $detail->vtsgn;
//	$c['superficie'] = $detail->superficie;
//	$c['volume'] = $detail->volume;
//	$c['cave_particuliere'] = $detail->cave_particuliere;
//	//	$c['revendique'] = $detail->volume_revendique;
//	//	$c['dplc'] = $detail->volume_dplc;
//	foreach($detail->negoces as $vente) {
//	  $c[$vente->cvi] = $vente->quantite_vendue;
//	}
//	foreach($detail->cooperatives as $vente) {
//	  $c[$vente->cvi] = $vente->quantite_vendue;
//	}
//	if ($detail->exist('mouts'))
//	  foreach($detail->mouts as $vente) {
//	    $c[$vente->cvi] = $vente->quantite_vendue;
//	  }
//	if ($cepage->getConfig()->excludeTotal()) {
//	  array_push($afterTotal, $c);
//	}else{
//	  $last = array_push($colonnes, $c) - 1;
//	}
//	$i++;
//	$cpt ++;
//	/*
//	if ($cpt > 8)
//	  break 2;
//	*/
//      }
//      if ($cepage->getConfig()->hasTotalCepage()) {
//	if ($i > 1) {
//	  $c = array();
//	  $c['type'] = 'total';
//	  $c['cepage'] = $cepage->getLibelle();
//	  $c['denomination'] = 'Total';
//	  $c['vtsgn'] = '';
//	  $c['superficie'] = $cepage->total_superficie;
//	  $c['volume'] = $cepage->total_volume;
//	  $c['cave_particuliere'] = $cepage->getTotalCaveParticuliere();
//	  $c['revendique'] = $cepage->volume_revendique;
//	  $c['dplc'] = $cepage->dplc;
//	  if (!$c['dplc'])
//	    $c['dplc'] = '0,00';
//	  $negoces = $cepage->getVolumeAcheteurs('negoces');
//	  foreach($negoces as $cvi => $total) {
//	    $c[$cvi] = $total;
//	  }
//	  $coop =  $cepage->getVolumeAcheteurs('cooperatives');
//	  foreach($coop as $cvi => $total) {
//	    $c[$cvi] = $total;
//	  }
//	  $mouts =  $cepage->getVolumeAcheteurs('mouts');
//	  foreach($mouts as $cvi => $total) {
//	    $c[$cvi] = $total;
//	  }
//	  array_push($colonnes, $c);
//	  $cpt ++;
//	}else{
//	  $colonnes[$last]['type'] = 'total';
//	  $colonnes[$last]['revendique'] = $cepage->volume_revendique;
//	  $colonnes[$last]['dplc'] = $cepage->dplc;
//	  if (!$colonnes[$last]['dplc'])
//	    $colonnes[$last]['dplc'] = '0,00';
//	}
//      }
//    }
//    $c = array();
//    $c['type'] = 'total';
//    $c['cepage'] = 'Total';
//    $c['denomination'] = ($lieu->getKey() == 'lieu') ? 'Appellation' : 'Lieu-dit';
//    if ($lieu->getAppellation()->getAppellation() == 'VINTABLE')
//      $c['denomination'] = '';
//    $c['vtsgn'] = '';
//    $c['superficie'] = $lieu->total_superficie;
//    $c['volume'] = $lieu->total_volume;
//    $c['cave_particuliere'] = $lieu->getTotalCaveParticuliere();
//    $c['revendique'] = $lieu->volume_revendique;
//    $c['dplc'] = $lieu->dplc;
//    if (!$c['dplc'])
//      $c['dplc'] = '0,00';
//    $negoces = $lieu->getVolumeAcheteurs('negoces');
//    foreach($negoces as $cvi => $vente) {
//      $c[$cvi] = $vente;
//    }
//    $coop =  $lieu->getVolumeAcheteurs('cooperatives');
//    foreach($coop as $cvi => $vente) {
//      $c[$cvi] = $vente;
//    }
//    $mouts =  $lieu->getVolumeAcheteurs('mouts');
//    foreach($mouts as $cvi => $vente) {
//      $c[$cvi] = $vente;
//    }
//    array_push($colonnes, $c);
//
//    //add afterTOtal columns
//    $colonnes = array_merge($colonnes, $afterTotal);
//
//    $pages = array();
//
//    //On peut pas mettre plus de 6 colonnes par page, si plus de 6 colonnes cepage
//    //alors on coupe au total précédent
//    $nb_colonnes_by_page = 6;
//    $lasti = 0;
//    for ($i = 0 ; $i < count($colonnes); ) {
//      $page = array_slice($colonnes, $i, $nb_colonnes_by_page);
//      $i += count($page) - 1;
//      /*
//      if (count($page) == $nb_colonnes_by_page) {
//	while($page[$i - $lasti]['type'] != 'total') {
//	  unset($page[$i - $lasti]);
//	  $i--;
//	}
//      }
//      */
//      array_push($pages, $page);
//      $lasti = ++$i;
//    }
//
//    $extra = array('lies' => $lieu->getCouchdbDocument()->lies, 'jeunes_vignes' => $lieu->getCouchdbDocument()->jeunes_vignes);
//
//    //L'identification des acheteurs ne peut apparaitre qu'une fois par cépage
//    $identification_enabled = 1;
//    foreach($pages as $p) {
//      $this->nb_pages++;
//      $this->document->addPage($this->getPartial('pageDR', array('tiers'=>$tiers, 'libelle_appellation' => $lieu->getLibelleWithAppellation(), 'colonnes_cepage' => $p, 'acheteurs' => $acheteurs, 'enable_identification' => $identification_enabled, 'extra' => $extra, 'nb_pages' => $this->nb_pages)));
//      $identification_enabled = 0;
//    }
//  }

  public function executeCsvTiers() {
      set_time_limit('240');
      ini_set('memory_limit', '512M');
      $tiers = sfCouchdbManager::getClient("Tiers")->getAll(sfCouchdbClient::HYDRATE_JSON);
      $content = '';
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

             foreach($ligne as $key => $item_ligne) {
                 $ligne[$key] = '"'.str_replace('"', '\"', $item_ligne).'"';
             }

             $content .= implode(';', $ligne) . "\n";
         }
      }
      
      $this->response->setContentType('application/csv');
      $this->response->setHttpHeader('Content-disposition', 'filename=tiers.csv', true);
      $this->response->setHttpHeader('Pragma', 'o-cache', true);
      $this->response->setHttpHeader('Expires', '0', true);
      return $this->renderText($content);
  }
}
