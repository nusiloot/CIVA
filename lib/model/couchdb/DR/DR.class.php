<?php
class DR extends BaseDR {
    public function removeVolumes() {
      $this->lies = null;
      return $this->recolte->removeVolumes();
    }
    public function getTotalSuperficie() {
      $v = 0;
      foreach($this->recolte->filter('^appellation_') as $appellation) {
	$v += $appellation->getTotalSuperficie();
      }
      return $v;
    }
    public function getTotalVolume() {
      $v = 0;
      foreach($this->recolte->filter('^appellation_') as $appellation) {
	$v += $appellation->getTotalVolume();
      }
      return $v;
    }
    public function getVolumeRevendique() {
      $v = 0;
      foreach($this->recolte->filter('^appellation_') as $appellation) {
	$v += $appellation->getVolumeRevendique();
      }
      return $v;
    }
    public function getDplc() {
      $v = 0;
      foreach($this->recolte->filter('^appellation_') as $appellation) {
	$v += $appellation->getDplc();
      }
      return $v;
    }
    public function getTotalCaveParticuliere() {
      $v = 0;
      foreach($this->recolte->filter('^appellation_') as $appellation) {
	$v += $appellation->getTotalCaveParticuliere();
      }
      return $v;
    }
    public function getRatioLies() {
      if (!($v = $this->getTotalCaveParticuliere())) {
	return 0;
      }
      return $this->lies / $v;
    }

    public function getLies(){
        $v = $this->_get('lies');
        if(!$v)
            return 0;
        else
            return $v;
    }

    public function canUpdate() {
      return !$this->exist('modifiee');
    }

    public function isValideeCiva() {
      if ($this->exist('modifiee')) {
          return $this->modifiee;
      }
      return false;
    }

    public function isValideeTiers() {
      if ($this->exist('validee')) {
          return $this->validee;
      }
      return false;
    }

    public function validate($tiers){
        $this->remove('etape');
        $this->add('modifiee', date('Y-m-d'));
        if (!$this->exist('validee') || !$this->validee) {
            $this->add('validee', date('Y-m-d'));
        }
        $this->declarant->nom =  $tiers->get('nom');
        $this->declarant->email =  $tiers->get('email');
        $this->declarant->telephone =  $tiers->get('telephone');
    }

    public function getDateModifieeFr() {
        return preg_replace('/(\d+)\-(\d+)\-(\d+)/', '\3/\2/\1', $this->get('modifiee'));
    }

    public function getDateValideeFr() {
        return preg_replace('/(\d+)\-(\d+)\-(\d+)/', '\3/\2/\1', $this->get('validee'));
    }

    public function getJeunesVignes(){
        $v = $this->_get('jeunes_vignes');
        if(!$v)
            return 0;
        else
            return $v;
    }

    public function clean() {
        $clean = false;
        foreach($this->recolte->getAppellations() as $appellation)  {
            if (count($appellation->getLieux()) < 1) {
                $this->recolte->remove($appellation->getKey());
                $this->acheteurs->remove($appellation->getKey());
                $clean = true;
            }
            foreach($appellation->getLieux() as $lieu) {
                if (count($lieu->getCepages()) < 1) {
                    $this->recolte->remove($appellation->getKey());
                    $this->acheteurs->remove($appellation->getKey());
                    $clean = true;
                }
                foreach($lieu->getCepages() as $cepage) {
                    if (count($cepage->detail) < 1) {
                        $this->recolte->remove($appellation->getKey());
                        $this->acheteurs->remove($appellation->getKey());
                        $clean = true;
                    }
                }
            }
        }

        return $clean;
    }
    
    public function update($params = array()) {
      parent::update($params);
      $u = $this->add('updated', 1);
    }
}