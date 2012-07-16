<?php
abstract class _Tiers extends Base_Tiers {
    
    /**
     *
     * @param string $campagne
     * @return array 
     */
    public function getDeclarationsArchivesSince($campagne) {
        return sfCouchdbManager::getClient('DR')->getArchivesSince($this->cvi, $campagne);
    }
    
    /**
     *
     * @param string $campagne
     * @return DR 
     */
    public function getDeclaration($campagne) {
        return sfCouchdbManager::getClient('DR')->retrieveByCampagneAndCvi($this->cvi, $campagne);
    }
    
    /**
     *
     * @return string 
     */
    public function getAdresse() {
        return $this->get('siege')->get('adresse');
    }
    
    /**
     *
     * @return string 
     */
    public function getCodePostal() {
        return $this->get('siege')->get('code_postal');
    }
    
    /**
     *
     * @return string
     */
    public function getCommune() {
        return $this->get('siege')->get('commune');
    }
    
    /**
     *
     * @param string $a
     * @return sfCouchdbJsonField 
     */
    public function setAdresse($a) {
        return $this->get('siege')->set('adresse', $a);
    }
    
    /**
     *
     * @param string $c
     * @return sfCouchdbJsonField 
     */
    public function setCodePostal($c) {
        return $this->get('siege')->set('code_postal', $c);
    }
    
    /**
     *
     * @param string $c
     * @return sfCouchdbJsonField 
     */
    public function setCommune($c) {
        return $this->get('siege')->set('commune', $c);
    }
}