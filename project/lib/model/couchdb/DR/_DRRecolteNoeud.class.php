<?php

abstract class _DRRecolteNoeud extends acCouchdbDocumentTree {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfigurationCampagne()->get($this->getHash());
    }

    abstract public function getChildrenNode();

    public function getChildrenNodeDeep($level = 1) {
      if($this->getConfig()->hasManyNoeuds()) {
          
          throw new sfException("getChildrenNodeDeep() peut uniquement être appelé d'un noeud qui contient un seul enfant...");
      }

      $node = $this->getChildrenNode()->getFirst();
      
      if($level > 1) {
        
        return $node->getChildrenNodeDeep($level - 1);
      }

      return $node->getChildrenNode();
    }

    public function getProduits() {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduits());
        }

        return $produits;
    }

    public function getProduitsDetails() {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduitsDetails());
        }
        return $produits;
    }

    public function getRendementRecoltant() {
        if ($this->getTotalSuperficie() > 0) {
            return round($this->getTotalVolume() / ($this->getTotalSuperficie() / 100), 0);
        } else {
            return 0;
        }
    }
    
    public function getTotalSuperficie($force_calcul = false) {

        return $this->getDataByFieldAndMethod("total_superficie", array($this,"getSumNoeudFields"), $force_calcul);
    }

    public function getTotalVolume($force_calcul = false) {

        return $this->getDataByFieldAndMethod("total_volume", array($this,"getSumNoeudFields"), $force_calcul);
    }

    public function getTotalCaveParticuliere() {

        return $this->getDataByFieldAndMethod('total_cave_particuliere', array($this, 'getSumNoeudWithMethod'), true, array('getTotalCaveParticuliere') );
    }

    public function getTotalRebeches() {

        return $this->getDataByFieldAndMethod('total_rebeches', array($this, 'getSumNoeudWithMethod'), true, array('getTotalRebeches', false) );
    }
    
    public function getLies($force_calcul = false) {
        if(!$this->canHaveUsagesLiesSaisi()) {

            return $this->getDataByFieldAndMethod('lies', array($this, 'getSumNoeudWithMethod'), $force_calcul, array('getLies') );
        }

        return $this->_get('lies') ? $this->_get('lies') : 0;
    }

    public function getDplc($force_calcul = false) {
        if(!$this->getConfig()->hasRendementNoeud()) {

            return $this->getDataByFieldAndMethod("dplc", array($this,"getDplcTotal") , $force_calcul);
        }
        
        return $this->getDataByFieldAndMethod('dplc', array($this, 'findDplc'), $force_calcul);
    }

    public function getDplcTotal() {

        return $this->getDataByFieldAndMethod('dplc_total', array($this, 'getSumNoeudFields'),true, array('dplc'));
    }

    public function getUsagesIndustrielsCalcule() {

        return $this->getUsagesIndustriels();
    }

    public function findDplc() {
        $dplc_total = $this->getDplcTotal();
        $dplc = $dplc_total;
        if ($this->getConfig()->hasRendementNoeud()) {
            $dplc_rendement = $this->getDplcRendement();
            if ($dplc_total < $dplc_rendement) {
                $dplc = $dplc_rendement;
            }
        }
        return $dplc;
    }

    public function getDplcRendement() {
        $key = "dplc_rendement";
        if (!isset($this->_storage[$key])) {
            $volume_dplc = 0;
            if ($this->getConfig()->hasRendementNoeud()) {
                $volume = $this->getTotalVolume();
                $volume_max = $this->getVolumeMaxRendement();
                if ($volume > $volume_max) {
                    $volume_dplc = $volume - $volume_max;
                } else {
                    $volume_dplc = 0;
                }
            }
            $this->_storage[$key] = round($volume_dplc, 2);
        }
        return $this->_storage[$key];
    }

    public function getVolumeMaxRendement() {
            
        return round(($this->getTotalSuperficie() / 100) * $this->getConfig()->getRendementNoeud(), 2);
    }

    public function getVolumeRevendique($force_calcul = false) {
        if(!$this->getConfig()->hasRendementNoeud()) {

            return $this->getDataByFieldAndMethod('volume_revendique', array($this, 'getVolumeRevendiqueTotal'), $force_calcul);
        }
        
        return $this->getDataByFieldAndMethod('volume_revendique', array($this, 'findVolumeRevendique'), $force_calcul);
    }

    public function getVolumeRevendiqueTotal($force_calcul = false) {

        return $this->getDataByFieldAndMethod('volume_revendique', array($this, 'getSumNoeudFields'), $force_calcul);
    }

    public function findVolumeRevendique() {

        return round(min($this->getVolumeRevendiqueWithDplc(), $this->getVolumeRevendiqueWithUI()), 2);
    }

    public function getVolumeRevendiqueWithDplc() {
        
        return $this->getTotalVolume() - $this->getDplc();
    }

    public function getVolumeRevendiqueWithUI() {
        
        return $this->getTotalVolume() - $this->getUsagesIndustriels();
    }

    public function getUsagesIndustriels($force_calcul = false) {
        if(!$this->getConfig()->hasRendementNoeud()) {
            
            return $this->getDataByFieldAndMethod('usages_industriels', array($this, 'getUsagesIndustrielsTotal'), $force_calcul);
        }

        return $this->getDplc() > $this->getLies() ? $this->getDplc() : $this->getLies();
    }

    public function getUsagesIndustrielsTotal() {

        return $this->getDataByFieldAndMethod('usages_industriels_total', array($this, 'getSumNoeudFields'), true, array('usages_industriels'));
    }

    public function canHaveUsagesLiesSaisi() {

        return false;
    }

    protected function getSumNoeudFields($field, $exclude = true) {
        $sum = 0;
        foreach ($this->getChildrenNode() as $key => $noeud) {
            if($exclude && $noeud->getConfig()->excludeTotal()) {

                continue;
            }
            
            $sum += $noeud->get($field);
        }
        return $sum;
    }

    public function cleanLies() {
        $this->lies = null;

        foreach($this->getChildrenNode() as $item) {
            $item->cleanLies();
        }
    }

    public function isLiesSaisisCepage() {

        return $this->getDocument()->exist('lies_saisis_cepage') && $this->getDocument()->get('lies_saisis_cepage');
    }

    public function getLibelle() {

        return $this->store('libelle', array($this, 'findLibelle'));
    }

    public function removeVolumes() {
        $this->total_volume = null;
        $this->volume_revendique = null;
        $this->dplc = null;
        $this->usages_industriels = null;
        $this->lies = null;

        if($this->exist('usages_industriels_saisi')) {
            $this->remove('usages_industriels_saisi');
        }
        
        if($this->exist('usages_industriels_calcule')) {
            $this->remove('usages_industriels_calcule');
        }

        foreach ($this->getChildrenNode() as $children) {
            $children->removeVolumes();
        }
    }

    public function isNonSaisie() {
        foreach ($this->getChildrenNode() as $children) {
            if (!$children->isNonSaisie())
                return false;
            
        }
        return true;
    }

    /******* Acheteurs *******/

    public function hasCompleteRecapitulatifVente() {
        if (!$this->getConfig()->existRendement() || !$this->hasAcheteurs()) {
            return true;
        }

        foreach ($this->acheteurs as $type => $type_acheteurs) {
            foreach ($type_acheteurs as $cvi => $acheteur) {
                if ($acheteur->superficie) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getTotalSuperficieRecapitulatifVente() {
        if(!$this->exist('acheteurs')) {

            throw new sfException("Ce ne noeud ne permet pas de stocker des acheteurs");
        }

        $total_superficie = 0;
        foreach ($this->acheteurs as $type => $type_acheteurs) {
            foreach ($type_acheteurs as $cvi => $acheteur) {
                if ($acheteur->superficie) {
                    $total_superficie += $acheteur->superficie;
                }
            }
        }

        return $total_superficie;
    }

    public function getTotalDontDplcRecapitulatifVente() {
        if(!$this->exist('acheteurs')) {

            throw new sfException("Ce ne noeud ne permet pas de stocker des acheteurs");
        }

        $total_dontdplc = 0;
        foreach ($this->acheteurs as $type => $type_acheteurs) {
            foreach ($type_acheteurs as $cvi => $acheteur) {
                if ($acheteur->dontdplc) {
                    $total_dontdplc += $acheteur->dontdplc;
                }
            }
        }

        return $total_dontdplc;
    }

    public function isValidRecapitulatifVente() {
        if (!$this->getConfig()->existRendement()) {
            return true;
        }
        return (round($this->getTotalSuperficie(), 2) >= round($this->getTotalSuperficieRecapitulatifVente(), 2) &&
                round($this->getDplc(), 2) >= round($this->getTotalDontDplcRecapitulatifVente(), 2));
    }

    public function hasAcheteurs() {
        if(!$this->exist('acheteurs')) {

            throw new sfException("Ce ne noeud ne permet pas de stocker des acheteurs");
        }

        $nb_acheteurs = 0;
        foreach ($this->acheteurs as $type => $type_acheteurs) {
            $nb_acheteurs += $type_acheteurs->count();
        }

        return $nb_acheteurs > 0;
    }

    public function updateAcheteurs() {
        if(!$this->exist('acheteurs')) {

            throw new sfException("Ce ne noeud ne permet pas de stocker des acheteurs");
        }

        if ($this->getCouchdbDocument()->canUpdate()) {
            $total_superficie_before = $this->getTotalSuperficie();
            $total_volume_before = $this->getTotalVolume();
            unset($this->_storage['total_superficie']);
            unset($this->_storage['total_volume']);
        }
        
        $this->add('acheteurs');
        $types = array('negoces', 'cooperatives', 'mouts');
        $unique_acheteur = null;
        foreach ($types as $type) {
            $acheteurs = $this->getVolumeAcheteurs($type);
            foreach ($acheteurs as $cvi => $volume) {
                $acheteur = $this->acheteurs->add($type)->add($cvi);
                $acheteur->type_acheteur = $type;
                $unique_acheteur = $acheteur;
                if ($this->getCouchdbDocument()->canUpdate() && (round($this->getTotalSuperficie(), 2) != round($total_superficie_before, 2) ||
                                                                 round($this->getTotalVolume(), 2) != round($total_volume_before, 2))) {
                    $acheteur->superficie = null;
                    $acheteur->dontdplc = null;
                }
            }
            $acheteurs_to_remove = array();
            foreach ($this->acheteurs->get($type) as $cvi => $item) {
                if (!array_key_exists($cvi, $acheteurs)) {
                    $acheteurs_to_remove[] = $type."/".$cvi;
                    //$this->acheteurs->get($type)->remove($cvi);
                }
            }

            foreach($acheteurs_to_remove as $hash) {
                $this->acheteurs->remove($hash);
            }
        }
        $this->acheteurs->update();

        if ($this->getCouchdbDocument()->canUpdate() && $this->hasSellToUniqueAcheteur()) {
            $unique_acheteur->superficie = $this->getTotalSuperficie();
            $unique_acheteur->dontdplc = $this->getDplc();
        }
    }

    public function getVolumeAcheteurs($type = 'negoces|cooperatives|mouts') {
        $key = "volume_acheteurs_" . $type;
        if (!isset($this->_storage[$key])) {
            $this->_storage[$key] = array();
            foreach($this->getChildrenNode() as $children) {
                if ($children->getConfig()->excludeTotal()) {
                    continue;
                }
                $acheteurs = $children->getVolumeAcheteurs($type);
                foreach ($acheteurs as $cvi => $quantite_vendue) {
                        if (!isset($this->_storage[$key][$cvi])) {
                            $this->_storage[$key][$cvi] = 0;
                        }
                        if ($quantite_vendue) {
                            $this->_storage[$key][$cvi] += $quantite_vendue;
                        }
                }   
            }
        }
        return $this->_storage[$key];
    }

    public function getVolumeAcheteur($cvi, $type) {
        $volume = 0;
        $acheteurs = $this->getVolumeAcheteurs($type);
        if (array_key_exists($cvi, $acheteurs)) {
            $volume = $acheteurs[$cvi];
        }
        return $volume;
    }

    public function getTotalVolumeAcheteurs($type = 'negoces|cooperatives|mouts') {
        $key = "total_volume_acheteurs_" . $type;
        if (!isset($this->_storage[$key])) {
            $sum = 0;
            $acheteurs = $this->getVolumeAcheteurs($type);
            foreach ($acheteurs as $volume) {
                $sum += $volume;
            }
            $this->_storage[$key] = $sum;
        }
        
        return $this->_storage[$key];
    }

    public function hasSellToUniqueAcheteur() {
        if ($this->getTotalCaveParticuliere() > 0) {
            return false;
        }
        $vol_total_cvi = array();
        $acheteurs = array();
        $types = array('negoces', 'cooperatives', 'mouts');
        foreach ($types as $type) {
            foreach ($this->getVolumeAcheteurs($type) as $cvi => $volume) {
                if (!isset($vol_total_cvi[$type . '_' . $cvi])) {
                    $vol_total_cvi[$type . '_' . $cvi] = 0;
                }
                $vol_total_cvi[$type . '_' . $cvi] += $volume;
            }
        }
        if (count($vol_total_cvi) != 1) {
            return false;
        }
        return true;
    }

    /******* Fin Acheteurs *******/

    protected function findLibelle() {

        return $this->getConfig()->getLibelle();
    }

    protected function getSumFields($field) {
        $sum = 0;
        foreach ($this->getChildrenNode() as $k => $noeud) {
            $sum += $noeud->get($field);
        }
        return $sum;
    }

    protected function issetField($field) {
        return ($this->_get($field) || $this->_get($field) === 0);
    }

    protected function getDataByFieldAndMethod($field, $method, $force_calcul = false, $parameters = array()) {
        if (!$force_calcul && $this->issetField($field)) {
            return $this->_get($field);
        }

        if(!empty($parameters))
            return $this->store($field, $method, $parameters);

        return $this->store($field, $method, array($field));
    }

    protected function getSumNoeudWithMethod($method, $exclude = true) {
        $sum = 0;
        foreach ($this->getChildrenNode() as $noeud) {
            if($exclude && $noeud->getConfig()->excludeTotal()) {

                continue;
            }

            $sum += $noeud->$method();
        }
        return $sum;
    }

}