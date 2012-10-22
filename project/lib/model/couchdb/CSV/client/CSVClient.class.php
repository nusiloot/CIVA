<?php

class CSVClient extends sfCouchdbClient {

  public static function getInstance() {
    
    return sfCouchdbManager::getClient('CSV'); 
  }

  private function getCSVsFromRecoltantArray($campagne, $cvi) {
    $csv = $this->startkey(array($campagne, $cvi))->endkey(array($campagne, $cvi, array()))->executeView('CSV', 'recoltant');
    $ids = array();
    foreach ($csv as $k => $c) 
      $ids[] = $k;
    return $ids;
  }

  public function getCSVsAcheteurs($campagne = null) {
    if (!$campagne) 
      $campagne = CurrentClient::getCurrent()->campagne;
    $csv = $this->startkey(array($campagne))->endkey(array(($campagne+1).''))->executeView('CSV', 'acheteur');
    $ids = array();
    foreach ($csv as $k => $c) 
      $ids[] = $k;
    return $ids;
  }

  public function findAll($hydrate = sfCouchdbClient::HYDRATE_DOCUMENT) {
      return $this->executeView('CSV', 'acheteur', $hydrate);
  }

  public function countCSVsAcheteurs($campagne = null) {
    return count($this->getCSVsAcheteurs($campagne));
  }

  public function countCSVsFromRecoltant($campagne, $cvi) {
    return count($this->getCSVsFromRecoltantArray($campagne, $cvi));
  }

  public function getCSVsFromRecoltant($campagne, $cvi) {
    $docs = array();
    foreach($this->getCSVsFromRecoltantArray($campagne, $cvi) as $id) {
      $docs[] = parent::retrieveDocumentById($id);
    }
    return $docs;
  }
  public function retrieveByCviAndCampagneOrCreateIt($cvi, $campagne = null, $hydrate = sfCouchdbClient::HYDRATE_DOCUMENT) {
    $csv = $this->retrieveByCviAndCampagne($cvi, $campagne, $hydrate);
    if (!$csv) {
      if (!$campagne) 
        $campagne = CurrentClient::getCurrent()->campagne;
      $csv = new CSV();
      $csv->set('_id', 'CSV-'.$cvi.'-'.$campagne);
      $csv->campagne = $campagne;
      $csv->cvi = $cvi;
      $csv->type = 'CSV';
    }
    return $csv;
  }
  public function retrieveByCviAndCampagne($cvi, $campagne = null, $hydrate = sfCouchdbClient::HYDRATE_DOCUMENT) {
    if (!$campagne) 
      $campagne = CurrentClient::getCurrent()->campagne;
    return parent::retrieveDocumentById('CSV-'.$cvi.'-'.$campagne, $hydrate);
  }
  
}