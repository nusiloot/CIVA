<?php

class TiersSecurity implements SecurityInterface {

    const DR = 'DR';
    const DR_ACHETEUR = 'DR_ACHETEUR';
    const DS_PROPRIETE = 'DS_PROPRIETE';
    const DS_NEGOCE = 'DS_NEGOCE';
    const GAMMA = 'GAMMA';
    const VRAC = 'VRAC';

    protected $compte;

    public static function getInstance($compte) {

        return new TiersSecurity($compte);
    }

    public function __construct($compte) {
        $this->compte = $compte;
        if(!$this->compte) {

            throw new sfException("Le compte est nul");
        }
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(in_array(self::DR, $droits)) {

            return DRSecurity::getInstance(DRClient::getInstance()->getEtablissement($this->compte->getSociete()))->isAuthorized(DRSecurity::DECLARANT);
        }

        if(in_array(self::DR_ACHETEUR, $droits)) {

            return DRAcheteurSecurity::getInstance($this->compte)->isAuthorized(DRAcheteurSecurity::DECLARANT);
        }

        if(in_array(self::DS_PROPRIETE, $droits)) {

            return DSSecurity::getInstance(DSCivaClient::getInstance()->getEtablissement($this->compte->getSociete(), DSCivaClient::TYPE_DS_PROPRIETE), null, DSCivaClient::TYPE_DS_PROPRIETE)->isAuthorized(DSSecurity::DECLARANT);
        }

        if(in_array(self::DS_NEGOCE, $droits)) {

            return DSSecurity::getInstance(DSCivaClient::getInstance()->getEtablissement($this->compte->getSociete(), DSCivaClient::TYPE_DS_NEGOCE), null, DSCivaClient::TYPE_DS_NEGOCE)->isAuthorized(DSSecurity::DECLARANT);
        }

        if(in_array(self::VRAC, $droits)) {

            $isDeclarant = VracSecurity::getInstance($this->compte)->isAuthorized(VracSecurity::DECLARANT);

            if(!$isDeclarant) {

                return false;
            }

            if(VracSecurity::getInstance($this->compte, null)->isAuthorized(VracSecurity::CREATION)) {

                return true;
            }

            $tiersVrac = $this->myUser->getDeclarantsVrac();

            if($tiersVrac instanceof sfOutputEscaperArrayDecorator) {
                $tiersVrac = $tiersVrac->getRawValue();
            }

            if(!count(VracTousView::getInstance()->findSortedByDeclarants($tiersVrac))) {

                return false;
            }

            return true;
        }

        return;

        if(in_array(self::GAMMA, $droits)) {

            return GammaSecurity::getInstance($this->myUser)->isAuthorized(GammaSecurity::DECLARANT);
        }

        return false;
    }

    public function getBlocs() {
        $blocs = array();
        foreach($this->getDroitUrls() as $droit => $url) {
            if ($this->isAuthorized($droit)) {
                $blocs[$droit] = $url;
            }
        }

        return $blocs;
    }

    public function getDroitUrls() {

        return array(
            TiersSecurity::DR => 'mon_espace_civa_dr',
            TiersSecurity::DR_ACHETEUR => 'mon_espace_civa_dr_acheteur',
            TiersSecurity::VRAC => 'mon_espace_civa_vrac',
            TiersSecurity::GAMMA => 'mon_espace_civa_gamma',
            TiersSecurity::DS_PROPRIETE => array('mon_espace_civa_ds', array('type' => DSCivaClient::TYPE_DS_PROPRIETE)),
            TiersSecurity::DS_NEGOCE => array('mon_espace_civa_ds', array('type' => DSCivaClient::TYPE_DS_NEGOCE)),
        );
    }

}
