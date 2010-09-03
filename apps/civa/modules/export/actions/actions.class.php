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

  private static $type2douane = array('negoces' => 'L6', 'mouts' => 'L7', 'cooperatives' => 'L8');
  private function setAcheteurType($acheteurs, $type, $detail) {
    if ($detail->exist($type)) {
      foreach ($detail->{$type} as $n) {
	if (!isset($acheteurs[$n->cvi][self::$type2douane[$type]])) {
	  $acheteurs[$n->cvi][self::$type2douane[$type]]['cvi'] = $n->cvi;
	  $acheteurs[$n->cvi][self::$type2douane[$type]]['volume'] = 0;
	}
	$acheteurs[$n->cvi][self::$type2douane[$type]]['volume'] += $n->quantite_vendue;
      }
    }
    return $acheteurs;
  }

  public function executeXml(sfWebRequest $request) 
  {
    $tiers = $this->getUser()->getTiers();
    $annee = $this->getRequestParameter('annee', null);
    $key = 'DR-'.$tiers->cvi.'-'.$annee;
    $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
    $xml = array();
    foreach ($dr->recolte->filter('^appellation_') as $appellation) {
      foreach ($appellation->filter('^lieu') as $lieu)  {
	//Comme il y a plusieurs acheteurs par lignes, il faut passer par une structure intermédiaire
	$acheteurs = array();
	$total = array();
	//	$total['hash'] = $lieu->gethash();
	$total['L1'] = $lieu->getCodeDouane();
	$total['L3'] = 'B';
	$total['mentionVal'] = '';
	$total['L4'] = 0;
	$total['exploitant'] = array();
	$total['exploitant']['L5'] = 0;
	$total['exploitant']['L9'] = 0;
	$total['exploitant']['L10'] = 0; //Volume revendique non negoces
	$total['exploitant']['L11'] = 0; //HS
	$total['exploitant']['L12'] = 0; //HS
	$total['exploitant']['L13'] = 0; //HS
	$total['exploitant']['L14'] = 0; //Vin de table + Rebeches
	$total['exploitant']['L15'] = 0; //Volume revendique
	$total['exploitant']['L16'] = 0; //DPLC
	$total['exploitant']['L17'] = 0; //HS
	$total['exploitant']['L18'] = 0; //HS
	$total['exploitant']['L19'] = 0; //HS
	$colass = null;
	foreach ($lieu->filter('^cepage_') as $cepage) {
	  foreach ($cepage->detail as $detail) {
	    //	    echo "dhash: ".$detail->getHash()."<br/>\n";
	    $col = array();
	    //	    $col['hash'] = $detail->getHash();
	    $col['L1'] = $detail->getCodeDouane();
	    $col['L3'] = 'B';
	    $col['mentionVal'] = $detail->denomination;
	    $col['L4'] = $detail->superficie;
	    $total['L4'] = $detail->superficie;
	    if (isset($detail->motif_non_recolte) && $detail->motif_non_recolte)
	      $col['motifSurfZero'] = $detail->motif_non_recolte;
	    $col['exploitant'] = array();
	    $col['exploitant']['L5'] = $detail->volume + $detail->volume * $dr->getRatioLies(); //Volume total avec lies
	    $total['exploitant']['L5'] += $col['exploitant']['L5'];
	    $col['exploitant']['L9'] = $detail->cave_particuliere; //Volume revendique sur place
	    $total['exploitant']['L9'] += $detail->cave_particuliere; //Volume revendique sur place
	    $col['exploitant']['L10'] = 0; //Volume revendique non negoces
	    $col['exploitant']['L11'] = 0; //HS
	    $col['exploitant']['L12'] = 0; //HS
	    $col['exploitant']['L13'] = 0; //HS
	    $col['exploitant']['L14'] = 0; //Vin de table + Rebeches
	    $col['exploitant']['L15'] = 0; //Volume revendique
	    $col['exploitant']['L16'] = 0; //DPLC
	    $col['exploitant']['L17'] = 0; //HS
	    $col['exploitant']['L18'] = 0; //HS
	    $col['exploitant']['L19'] = 0; //HS

	    if ($detail->exist('cooperatives'))
	      foreach ($detail->cooperatives as $coop)  {
		$col['exploitant'][] = array('L8' => array('cvi' => $coop->cvi, 'volume' => $coop->quantite_vendue));
		$col['exploitant']['L10'] += $coop->quantite_vendue;
	      }
	    $col['exploitant']['L10'] += $detail->cave_particuliere;
	    $col['exploitant']['L10'] += $col['exploitant']['L10'] * $dr->getRatioLies();
	    $total['exploitant']['L10'] += $col['exploitant']['L10'];

	    if ($detail->exist('negoces'))
	      foreach ($detail->negoces as $n)  {
		$col['exploitant'][] = array('L6' => array('cvi' => $n->cvi, 'volume' => $n->quantite_vendue));
	      }

	    if ($detail->exist('mouts'))
	      foreach ($detail->mouts as $n)  {
		$col['exploitant'][] = array('L7' => array('cvi' => $n->cvi, 'volume' => $n->quantite_vendue));
	      }

	    $acheteurs = $this->setAcheteurType($acheteurs, 'negoces', $detail);
	    $acheteurs = $this->setAcheteurType($acheteurs, 'mouts', $detail);
	    $acheteurs = $this->setAcheteurType($acheteurs, 'cooperatives', $detail);

	    if (($detail->cepage == 'RB' && $detail->appellation == 'CREMANT') || $detail->appellation == 'VINTABLE') {
	      $col['exploitant']['L14'] = $detail->volume;
	      $total['exploitation']['L14'] =+ $detail->volume;
	    }

	    $col['exploitant']['L15'] = $detail->volume_revendique;
	    $total['exploitant']['L15'] = $detail->volume_revendique;
	    $col['exploitant']['L16'] = $detail->volume_dplc;
	    $total['exploitant']['L16'] = $detail->volume_dplc;
	    if ($detail->cepage == 'RB' && $detail->appellation == 'CREMANT')
	      $colass = $col;
	    else
	      $xml[] = $col;
	  }
	}
	//Ajout des acheteurs
	foreach ($acheteurs as $cvi => $v) {
	  $total['exploitant'][] = $v;
	}
	if ($colass)
	  $total['colonneAss'] = $colass;
	else if ($lieu->getAppellation() != 'KLEVENER')
	  $xml[] = $total;
      }
    }
    $this->xml = $xml;
    $this->dr = $dr;
    $this->setLayout(false);
    $this->response->setContentType('text/xml');
  }
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executePdf(sfWebRequest $request)
  {
    $tiers = $this->getUser()->getTiers();
    $annee = $this->getRequestParameter('annee', null);
    $key = 'DR-'.$tiers->cvi.'-'.$annee;
    $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
    try {
      $dr->recolte->filter('^appellation')->getFirst()->filter('^lieu')->getFirst()->filter('^cepage')->getFirst()->acheteurs;
      $dr->recolte->filter('^appellation')->getFirst()->filter('^lieu')->getFirst()->acheteurs;
    }catch(Exception $e) {
      $dr->update();
      $dr->save();
    }
    $this->forward404Unless($dr);

    $this->setLayout(false);
    //    $this->getResponse()->setContent('application/x-pdf');

    if ($this->getRequestParameter('output', 'pdf') == 'html') {
      $this->document = new PageableHTML('Déclaration de récolte '.$annee, $tiers->nom, $annee.'_DR_'.$tiers->cvi.'.pdf');
    }else {
      $this->document = new PageablePDF('Déclaration de récolte '.$annee, $tiers->nom, $annee.'_DR_'.$tiers->cvi.'.pdf');
    }

    foreach ($dr->getRecolte()->filter('^appellation_') as $appellation) {
      foreach ($appellation->filter('^lieu') as $lieu) {
	$this->createAppellationLieu($lieu, $tiers);
      }
    }

    return $this->document->output();
    
  }

  private function createAppellationLieu($lieu, $tiers) {
    $colonnes = array();
    $acheteurs = $lieu->acheteurs;
    $cpt = 0;
    foreach ($lieu->filter('^cepage_') as $cepage) {
      $i = 0;
      foreach ($cepage->detail as $detail) {
	$c = array();
	$c['type'] = 'detail';
	$c['cepage'] = $cepage->getLibelle();
	$c['denomination'] = $detail->denomination;
	$c['vtsgn'] = $detail->vtsgn;
	$c['superficie'] = $detail->superficie;
	$c['volume'] = $detail->volume;
	$c['cave_particuliere'] = $detail->cave_particuliere;
	$c['revendique'] = $detail->volume_revendique;
	$c['dplc'] = $detail->volume_dplc;
	foreach($detail->negoces as $vente) {
	  $c[$vente->cvi] = $vente->quantite_vendue;
	}
	foreach($detail->cooperatives as $vente) {
	  $c[$vente->cvi] = $vente->quantite_vendue;
	}
	$last = array_push($colonnes, $c) - 1;
	$i++;
	$cpt ++;
	/*
	if ($cpt > 8)
	  break 2;
	*/
      }
      if ($i > 1) {
	$c = array();
	$c['type'] = 'total';
	$c['cepage'] = $cepage->getLibelle();
	$c['denomination'] = 'Total';
	$c['vtsgn'] = '';
	$c['superficie'] = $cepage->total_superficie;
	$c['volume'] = $cepage->total_volume;
	$c['cave_particuliere'] = $cepage->getTotalCaveParticuliere();
	$c['revendique'] = $cepage->volume_revendique;
	$c['dplc'] = $cepage->dplc;
	$negoces = $cepage->getTotalAcheteursByCvi('negoces');
	foreach($negoces as $cvi => $total) {
	  $c[$cvi] = $total;
	}
	$coop =  $cepage->getTotalAcheteursByCvi('cooperatives');
	foreach($detail->cooperatives as $vente) {
	  $c[$vente->cvi] = $coop[$vente->cvi];
	}
	array_push($colonnes, $c);
	$cpt ++;
      }else{
	$colonnes[$last]['type'] = 'total';
      }
    }
    $c = array();
    $c['type'] = 'total';
    $c['cepage'] = 'Appellation';
    $c['denomination'] = 'Total';
    $c['vtsgn'] = '';
    $c['superficie'] = $lieu->total_superficie;
    $c['volume'] = $lieu->total_volume;
    $c['cave_particuliere'] = $lieu->getTotalCaveParticuliere();
    $c['revendique'] = $lieu->total_volume_revendique;
    $c['dplc'] = $lieu->total_dplc;
    $negoces = $lieu->getTotalAcheteursByCvi('negoces');
    foreach($negoces as $cvi => $vente) {
      $c[$cvi] = $vente;
    }
    $coop =  $lieu->getTotalAcheteursByCvi('cooperatives');
    foreach($detail->cooperatives as $vente) {
      $c[$vente->cvi] = $coop[$vente->cvi];
    }
    array_push($colonnes, $c);
    
    $pages = array();
    
    //On peut pas mettre plus de 6 colonnes par page, si plus de 6 colonnes cepage
    //alors on coupe au total précédent
    $nb_colonnes_by_page = 6;
    $lasti = 0;
    for ($i = 0 ; $i < count($colonnes); ) {
      $page = array_slice($colonnes, $i, $nb_colonnes_by_page);
      $i += count($page) - 1;
      /*
      if (count($page) == $nb_colonnes_by_page) {
	while($page[$i - $lasti]['type'] != 'total') {
	  unset($page[$i - $lasti]);
	  $i--;
	}
      }
      */
      array_push($pages, $page);
      $lasti = ++$i;
    }

    //L'identification des acheteurs ne peut apparaitre qu'une fois par cépage
    $identification_enabled = 1;
    foreach($pages as $p) {
      $this->document->addPage($this->getPartial('pageDR', array('tiers'=>$tiers, 'libelle_appellation' => $lieu->getLibelleWithAppellation(), 'colonnes_cepage' => $p, 'acheteurs' => $acheteurs, 'enable_identification' => $identification_enabled)));
      $identification_enabled = 0;
    }
  }
}
