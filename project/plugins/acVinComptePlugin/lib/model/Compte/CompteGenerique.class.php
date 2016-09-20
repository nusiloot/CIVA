<?php

abstract class CompteGenerique extends acCouchdbDocument {

    protected $telephone_mobile = null;
    protected $telephone_perso = null;
    protected $site_internet = null;
    protected $insee = null;

    public function getAdresse() {

        return $this->siege->adresse;
    }

    public function setAdresse($s) {

        return ($this->siege->adresse = $s);
    }

    public function getCommune() {

        return $this->siege->commune;
    }

    public function setCommune($s) {

        return ($this->siege->commune = $s);
    }

    public function getCodePostal() {

        return $this->siege->code_postal;
    }

    public function setCodePostal($s) {

        return ($this->siege->code_postal = $s);
    }

    public function getPays() {

        return $this->siege->pays;
    }

    public function setPays($s) {

        return ($this->siege->pays = $s);
    }

    public function getAdresseComplementaire() {

        return $this->siege->adresse_complementaire;
    }

    public function getInsee() {
        if (is_null($this->insee)) {
            $this->insee = $this->getMasterCompte()->insee;
        }

        return $this->insee;
    }

    public function setInsee($s) {

        return ($this->insee = $s);
    }

    public function setAdresseComplementaire($s) {

        return ($this->siege->adresse_complementaire = $s);
    }

    public function setEmail($email) {

        return $this->_set('email', $email);
    }

    public function setTelephonePerso($s) {

        return ($this->telephone_perso = $s);
    }

    public function setTelephoneMobile($s) {

        return ($this->telephone_mobile = $s);
    }

    public function setTelephoneBureau($tel) {

        return $this->setTelephone($tel);
    }

    public function setSiteInternet($s) {

        return ($this->site_internet = $s);
    }

    public function setFax($fax) {

        return $this->_set('fax', $fax);
    }

    public function getEmail() {

        return $this->_get('email');
    }

    public function getTelephone() {

        return $this->_get('telephone');
    }

    public function setTelephone($phone) {

        return $this->_set('telephone', $phone);
    }

    public function getTelephoneBureau() {

        return $this->getTelephone();
    }

    public function getTelephonePerso() {
        if (is_null($this->telephone_perso)) {
            $this->telephone_perso = $this->getMasterCompte()->telephone_perso;
        }
        return $this->telephone_perso;
    }

    public function getTelephoneMobile() {
    if (is_null($this->telephone_mobile)) {
            if(!$this->getMasterCompte()) {
                throw new sfException($this->_id);
            }
            $this->telephone_mobile = $this->getMasterCompte()->telephone_mobile;
        }
        return $this->telephone_mobile;
    }

    public function getSiteInternet() {
        if (is_null($this->site_internet)) {
            $this->site_internet = $this->getMasterCompte()->site_internet;
        }
        return $this->site_internet;
    }

    public function getFax() {

        return $this->_get('fax');
    }

    public static function isSameAdresseComptes(InterfaceCompteGenerique $compte1, InterfaceCompteGenerique $compte2) {

        if
        (
            ($compte1->getAdresse() == $compte2->getAdresse() || !$compte1->getAdresse()) &&
            ($compte1->getCommune() == $compte2->getCommune() || !$compte1->getCommune()) &&
            ($compte1->getCodePostal() == $compte2->getCodePostal() || !$compte1->getCodePostal()) &&
            ($compte1->getInsee() == $compte2->getInsee() || !$compte1->getInsee()) &&
            ($compte1->getAdresseComplementaire() == $compte2->getAdresseComplementaire() || !$compte1->getAdresseComplementaire()) &&
            ($compte1->getPays() == $compte2->getPays() || !$compte1->getPays())
        )
        {

            return true;
        }

        return false;
    }

    public static function isSameContactComptes(InterfaceCompteGenerique $compte1, InterfaceCompteGenerique $compte2) {

        if
        (
            ($compte1->getTelephoneBureau() == $compte2->getTelephoneBureau() || !$compte1->getTelephoneBureau()) &&
            ($compte1->getTelephoneMobile() == $compte2->getTelephoneMobile() || !$compte1->getTelephoneMobile()) &&
            ($compte1->getTelephonePerso() == $compte2->getTelephonePerso() || !$compte1->getTelephonePerso()) &&
            ($compte1->getEmail() == $compte2->getEmail() || !$compte1->getEmail()) &&
            ($compte1->getFax() == $compte2->getFax() || !$compte1->getFax()) &&
            ($compte1->getSiteInternet() == $compte2->getSiteInternet() || !$compte1->getSiteInternet())
        )
        {

            return true;
        }

        return false;
    }


    public function isSameAdresseThan(InterfaceCompteGenerique $compte) {

        return self::isSameAdresseComptes($this, $compte);
    }

    public function isSameContactThan(InterfaceCompteGenerique $compte) {

        return self::isSameContactComptes($this, $compte);
    }

    public function pushContactAndAdresseTo(InterfaceCompteGenerique $compte) {
        $this->pushAdresseTo($compte);
        $this->pushContactTo($compte);
    }

    public function pushAdresseTo(InterfaceCompteGenerique $compte) {
        $compte->adresse = $this->getAdresse();
        $compte->adresse_complementaire = $this->getAdresseComplementaire();
        $compte->commune = $this->getCommune();
        $compte->code_postal = $this->getCodePostal();
        $compte->insee = $this->getInsee();
        $compte->pays = $this->getPays();
    }

    public function pushContactTo(InterfaceCompteGenerique $compte) {
        $compte->telephone_bureau= $this->getTelephoneBureau();
        $compte->email = $this->getEmail();
        $compte->fax = $this->getFax();
        $compte->telephone_perso = $this->getTelephonePerso();
        $compte->telephone_mobile = $this->getTelephoneMobile();
        $compte->site_internet = $this->getSiteInternet();
    }

    public function pullContactAndAdresseFrom(InterfaceCompteGenerique $compte) {
        $this->pullAdresseFrom($compte);
        $this->pullContactFrom($compte);
    }

    public static function pullAdresse(InterfaceCompteGenerique $compteTo, InterfaceCompteGenerique $compteFrom) {
        $compteTo->setAdresse($compteFrom->adresse);
        $compteTo->setCommune($compteFrom->commune);
        $compteTo->setCodePostal($compteFrom->code_postal);
        $compteTo->setPays($compteFrom->pays);
    }

    public function pullAdresseFrom(InterfaceCompteGenerique $compteFrom) {
        self::pullAdresse($this, $compteFrom);
    }

    public static function pullContact(InterfaceCompteGenerique $compteTo, InterfaceCompteGenerique $compteFrom) {
        $compteTo->setTelephoneBureau($compteFrom->telephone_bureau);
        $compteTo->setEmail($compteFrom->email);
        $compteTo->setFax($compteFrom->fax);
        $compteTo->setTelephonePerso($compteFrom->telephone_perso);
        $compteTo->setTelephoneMobile($compteFrom->telephone_mobile);
    }

    public function pullContactFrom(InterfaceCompteGenerique $compteFrom) {
        self::pullContact($this, $compteFrom);
    }

}