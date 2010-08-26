<?php

/**
 * printable actions.
 *
 * @package    civa
 * @subpackage printable
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */

class printableActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeDR(sfWebRequest $request)
  {
    $recoltant = $this->getUser()->getRecoltant();
    $annee = $this->getRequestParameter('annee', null);
    $key = 'DR-'.$recoltant->cvi.'-'.$annee;
    $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
    try {
      $dr->recolte->filter('^appellation')->getFirst()->filter('^lieu')->getFirst()->filter('^cepage')->getFirst()->acheteurs;
    }catch(Exception $e) {
      $dr->update();
      $dr->save();
    }
    $this->forward404Unless($dr);

    $this->setLayout(false);
    //    $this->getResponse()->setContent('application/x-pdf');

    if ($this->getRequestParameter('output', 'pdf') == 'html') {
      $this->document = new PageableHTML('Déclaration de récolte '.$annee, $recoltant->nom, $annee.'_DR_'.$recoltant->cvi.'.pdf');
    }else {
      $this->document = new PageablePDF('Déclaration de récolte '.$annee, $recoltant->nom, $annee.'_DR_'.$recoltant->cvi.'.pdf');
    }

    foreach ($dr->getRecolte()->filter('^appellation_') as $appellation) {
      foreach ($appellation->filter('^lieu') as $lieu) {
	$this->createAppellationLieu($lieu, $recoltant);
      }
    }

    return $this->document->output();
    
  }

  private function createAppellationLieu($lieu, $recoltant) {
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
	$c['vtsn'] = $detail->vtsgn;
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
	$c['vtsn'] = '';
	$c['superficie'] = $cepage->total_superficie;
	$c['volume'] = $cepage->total_volume;
	$c['cave_particuliere'] = '';
	$c['revendique'] = $cepage->volume_revendique;
	$c['dplc'] = $cepage->dplc;
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
    $c['vtsn'] = '';
    $c['superficie'] = $lieu->total_superficie;
    $c['volume'] = $lieu->total_volume;
    $c['cave_particuliere'] = '';
    $c['revendique'] = $lieu->volume_revendique;
    $c['dplc'] = $lieu->dplc;
    array_push($colonnes, $c);

    $pages = array();

    $nb_colonnes_by_page = 6;

    $cpt = 0;
    $lasti = 0;
    for ($i = 0 ; $i < count($colonnes); ) {
      $page = array_slice($colonnes, $i, $nb_colonnes_by_page);
      $i += count($page) - 1;
      if (count($page) == $nb_colonnes_by_page) {
	while($page[$i - $lasti]['type'] != 'total') {
	  unset($page[$i - $lasti]);
	  $i--;
	}
      }
      array_push($pages, $page);
      $lasti = ++$i;
    }

    $identification_enabled = 1;
    foreach($pages as $p) {
      $this->document->addPage($this->getPartial('pageDR', array('recoltant'=>$recoltant, 'libelle_appellation' => $lieu->getLibelleWithAppellation(), 'colonnes_cepage' => $p, 'acheteurs' => $acheteurs, 'enable_identification' => $identification_enabled)));
      $identification_enabled = 0;
    }
  }
}
