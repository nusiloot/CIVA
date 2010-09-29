<?php

class DRRecolteAppellationLieuAcheteur extends BaseDRRecolteAppellationLieuAcheteur 
{
  private $acheteur = null;

  public function getVolume() {
    return $this->getParent()->getParent()->getVolumeAcheteur($this->getKey(), $this->type_acheteur);
  }
  public function getNom() {
    if ($v = $this->_get('nom'))
      return $v;
    $v = $this->getAcheteurFromCVI()->getNom();
    $this->nom = $v;
    return $v;
  }
  public function getCommune() {
    if ($v = $this->_get('commune'))
      return $v;
    $v = $this->getAcheteurFromCVI()->getCommune();
    $this->commune = $v;
    return $v;
  }
  public function getCVI() {
    return $this->getKey();
  }
  private function getAcheteurFromCVI() {
    if (!$this->acheteur)
      $this->acheteur = sfCouchdbManager::getClient()->retrieveDocumentById('ACHAT-'.$this->getKey());
    if (!$this->acheteur)
      throw new Exception("Unknown CVI ".$this->getKey());
    return $this->acheteur;
  }

  public function update($params = array()) {
    parent::update($params);
    $this->getNom();
    $this->getCommune();
  }
}