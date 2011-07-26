<?php

/**
 * declaration actions.
 *
 * @package    civa
 * @subpackage declaration
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class declarationComponents extends sfComponents {
    
    
    
    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspace(sfWebRequest $request) {
      $this->dr_editable = $this->getUser()->isDrEditable();
    }
    
    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspaceEnCours(sfWebRequest $request) {
        $this->declaration = $this->getUser()->getDeclaration();
        $this->campagnes = $this->getUser()->getTiers()->getDeclarationsArchivesSince(($this->getUser()->getCampagne()-1));
        krsort($this->campagnes);
    }
    
    /**
     *
     * @param sfWebRequest $request 
     */
    public function executeMonEspaceColonne(sfWebRequest $request) {
        $this->campagnes = $this->getUser()->getTiers()->getDeclarationsArchivesSince(($this->getUser()->getCampagne()-1));
        krsort($this->campagnes);
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeRecapDeclaration(sfWebRequest $request) {
        
        $tiers = $this->getUser()->getTiers();
        $annee = $this->getRequestParameter('annee', null);
        $key = 'DR-'.$tiers->cvi.'-'.$annee;
        $dr = sfCouchdbManager::getClient()->retrieveDocumentById($key);
        $this->appellations = array();
        $this->superficie = array();
        $this->volume = array();
        $this->revendique = array();
        $this->dplc = array();
        $this->libelle = array();
        $this->volume_negoces = array();
        $this->volume_cooperatives = array();
        $cvi = array();
        foreach ($dr->recolte->getConfig()->filter('^appellation_') as $appellation_key => $appellation_config) {
          if ($dr->recolte->exist($appellation_key)) {
              $appellation = $dr->recolte->get($appellation_key);
              if ($appellation->getConfig()->excludeTotal())
                continue;
              $this->appellations[] = $appellation->getAppellation();
              $this->libelle[$appellation->getAppellation()] = $appellation->getConfig()->getLibelle();
              $this->superficie[$appellation->getAppellation()] = $appellation->getTotalSuperficie();
              $this->volume[$appellation->getAppellation()] = $appellation->getTotalVolume();
              $this->revendique[$appellation->getAppellation()] = $appellation->getVolumeRevendique();
              $this->dplc[$appellation->getAppellation()] = $appellation->getDplc();
          }
        }

        $this->total_superficie = array_sum(array_values($this->superficie));
        $this->total_volume = array_sum(array_values($this->volume));
        $this->total_dplc = array_sum(array_values($this->dplc));
        $this->total_revendique = array_sum(array_values($this->revendique));

        $this->lies = $dr->lies;
        $this->jeunes_vignes = $dr->jeunes_vignes;
	
	$this->vintable = array();
	if ($dr->recolte->exist('appellation_VINTABLE')) {
	  $this->vintable['superficie'] = $dr->recolte->appellation_VINTABLE->getTotalSuperficie();
	  $this->vintable['volume'] = $dr->recolte->appellation_VINTABLE->getTotalVolume();
	}

        $this->annee = $annee;

    }


}
