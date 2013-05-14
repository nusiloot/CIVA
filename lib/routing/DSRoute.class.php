<?php
class DSRoute extends sfObjectRoute implements InterfaceTiersRoute {

	protected $ds = null;

	protected function getObjectForParameters($parameters) {

//        if (preg_match('/^[0-9]{4}[0-9]{2}$/',$parameters['periode'])) {            
//            $periode = $parameters['periode'];
//        } else {
//            throw new InvalidArgumentException(sprintf('The "%s" route has an invalid parameter "%s" value "%s".', $this->pattern, 'periode', $parameters['periode']));
//        }
//        
//        if (preg_match('/^[0-9]{10}$/',$parameters['identifiant'])) {            
//            $identifiant = $parameters['identifiant'];
//        } else {
//            throw new InvalidArgumentException(sprintf('The "%s" route has an invalid parameter "%s" value "%s".', $this->pattern, 'identifiant', $parameters['identifiant']));
//        }
//
//        if (preg_match('/^[0-9]{3}$/',$parameters['lieu_stockage'])) {            
//            $lieu_stockage = $parameters['lieu_stockage'];
//        } else {
//            throw new InvalidArgumentException(sprintf('The "%s" route has an invalid parameter "%s" value "%s".', $this->pattern, 'lieu_stockage', $parameters['lieu_stockage']));
//        }
        $matches = array();
        if (preg_match('/^DS-([0-9]{10})-([0-9]{4}[0-9]{2})-([0-9]{3})$/',$parameters['id'],$matches)) {   
            $identifiant = $matches[1];
            $periode = $matches[2];
            $lieu_stockage = $matches[3];
        } else {
            throw new InvalidArgumentException(sprintf('The DS "%s" is not valid.', $this->pattern, $parameters['id']));
        }
        
        $this->ds = DSClient::getInstance()->findByIdentifiantAndPeriode($identifiant, $periode, $lieu_stockage);
        if (!$this->ds) {
            throw new sfError404Exception(sprintf('No DS found with the id "%s" and the periode "%s".',  $identifiant, $periode));
        }
        return $this->ds;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDS() {
        if (!$this->ds) {
            $this->getObject();
        }

        return $this->ds;
    }

    public function getTiers() {

        return $this->getDS()->getEtablissement();
    }
}