<?php

class DSSecurity {

    const CONSULTATION = 'CONSULTATION';
    const EDITION = 'EDITION';

    protected $instance;
    protected $ds;
    protected $myUser;

    public static function getInstance($myUser, $ds) {

        return new DSSecurity($myUser, $ds);
    }

    public function __construct(myUser $myUser, DSCiva $ds_principale) {
        $this->myUser = $myUser;
        $this->ds = $ds_principale;
    }

    public function isAuthorized($droits) {
        if(!is_array($droits)) {
            $droits = array($droits);
        }

        if(in_array(self::EDITION , $droits) && $this->ds->isValideeCiva()) {

            return false;
        }

        if($this->myUser->hasCredential(myUser::CREDENTIAL_ADMIN)) {
            
            return true;
        }

        if(in_array(self::EDITION , $droits) && $this->ds->isValideeTiers()) {

            return false;
        }

        if($this->myUser->hasCredential(myUser::CREDENTIAL_OPERATEUR)) {

            return true;
        }

        if(in_array(self::EDITION , $droits) && !CurrentClient::getCurrent()->isDSEditable()) {

            return false;
        }

        if(!$this->myUser->getDeclarant()->isDeclarantStock()) {

            return false;
        }

        if($this->myUser->getDeclarant()->getIdentifiant() != $this->ds->identifiant) {

            return false;
        }

        return true;
    }

}