<?php

class TiersSecurity implements SecurityInterface {

    const DR = 'DR';
    const DR_APPORTEUR = 'DR_APPORTEUR';
    const DS = 'DS';
    const GAMMA = 'GAMMA';
    const VRAC = 'VRAC';

    protected $instance;
    protected $myUser;

    public static function getInstance($myUser) {

        return new TiersSecurity($myUser);
    }

    public function __construct($myUser) {
        $this->myUser = $myUser;
        $this->tiers = $this->myUser->getDeclarant();
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(in_array(self::DR, $droits)) {

            return DRSecurity::getInstance($this->myUser)->isAuthorized(DRSecurity::DECLARANT);
        }

        if(in_array(self::DR_APPORTEUR, $droits)) {

            return DRAcheteurSecurity::getInstance($this->myUser)->isAuthorized(DRAcheteurSecurity::DECLARANT);
        }

        if(in_array(self::DS, $droits)) {

            return DSSecurity::getInstance($this->myUser)->isAuthorized(DSSecurity::DECLARANT);
        }
        
        if(in_array(self::VRAC, $droits)) {

            return VracSecurity::getInstance($this->myUser)->isAuthorized(VracSecurity::DECLARANT);
        }

        if(in_array(self::GAMMA, $droits)) {

            return GammaSecurity::getInstance($this->myUser)->isAuthorized(GammaSecurity::DECLARANT);
        }

        return false;
    }

}