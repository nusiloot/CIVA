<?php

class DRRecolteAppellationCepageDetail extends BaseDRRecolteAppellationCepageDetail {

    public function getConfig() {
        return sfCouchdbManager::getClient('Configuration')->getConfiguration()->get($this->getHash());
    }

    public function getAcheteursValuesWithCvi($field) {
        $values = array();
        if ($this->exist($field)) {
            $acheteurs = $this->get($field);
            foreach ($acheteurs as $acheteur) {
                $values[$acheteur->cvi] = $acheteur->quantite_vendue;
            }
        }
        return $values;
    }
    protected function update() {
        parent::update();
        $v = $this->cave_particuliere;
        $v += $this->getSumAcheteur('negoces');
        $v += $this->getSumAcheteur('cooperatives');
        $v += $this->getSumAcheteur('mouts');

        $this->volume = $v;

        $volume_max = $this->getVolumeMax();

        if ($this->volume > $volume_max) {
            $this->volume_revendique = $volume_max;
            $this->volume_dplc = $this->volume - $volume_max;
        } else {
            $this->volume_revendique = $this->volume;
            $this->volume_dplc = 0;
        }

        if ($this->volume && $this->volume > 0) {
           $this->remove('motif_non_recolte');
        } else {
           $this->add('motif_non_recolte');
        }
    }

    private function getVolumeMax() {
        return $this->superficie * $this->getRendementCepage();
    }

    private function getSumAcheteur($field) {
      $sum = 0;
      if ($this->exist($field)) {
          foreach ($this->get($field) as $acheteur) {
            $sum += $acheteur->quantite_vendue;
          }
      }
      return $sum;
    }
    
    public function getRendementCepage() {
        $cepage_detail = $this->getCouchdbDocument()->get($this->getParentHash());
        $cepage = $this->getCouchdbDocument()->get($cepage_detail->getParentHash());
        return $cepage->getRendement();
    }
    
    public function save() {
      return $this->getCouchdbDocument()->save();
    }

    public function removeVolumes() {
      $this->volume = null;
      $this->cave_particuliere = null;
      $this->remove('cooperative');
      $this->remove('mouts');
      $this->remove('negoces');
    }

    public function getMotifNonRecolteLibelle() {
      if ($this->volume)
	return '';

      if ($this->exist('motif_non_recolte') && ConfigurationClient::getConfiguration()->motif_non_recolte->exist($this->motif_non_recolte)) {
          return ConfigurationClient::getConfiguration()->motif_non_recolte->get($this->motif_non_recolte);
      } else {
         return 'Non saisi';
      }
      
    }
}
