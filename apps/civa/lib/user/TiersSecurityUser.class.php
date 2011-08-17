<?php

abstract class TiersSecurityUser extends CompteSecurityUser {
    protected $_tiers = null;
    const SESSION_TIERS = 'tiers';
    const NAMESPACE_TIERS = 'TiersSecurityUser';
    const CREDENTIAL_TIERS = 'tiers';
    const CREDENTIAL_RECOLTANT = 'recoltant';
    const CREDENTIAL_DECLARATION = 'declaration';
    const CREDENTIAL_METTEUR_EN_MARCHE = 'metteur_en_marche';
    const CREDENTIAL_GAMMA = 'gamma';
    const CREDENTIAL_ACHETEUR = 'acheteur';
    
    protected $_credentials_tiers = array(self::CREDENTIAL_TIERS, 
                                          self::CREDENTIAL_RECOLTANT, 
                                          self::CREDENTIAL_DECLARATION, 
                                          self::CREDENTIAL_METTEUR_EN_MARCHE, 
                                          self::CREDENTIAL_GAMMA,
                                          self::CREDENTIAL_ACHETEUR);
    
    /**
     *
     * @param sfEventDispatcher $dispatcher
     * @param sfStorage $storage
     * @param type $options 
     */
    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array()) {
        parent::initialize($dispatcher, $storage, $options);
        
        if (!$this->isAuthenticated())
        {
            $this->signOutTiers();
        }
    }
    
    /**
     *
     * @param _Tiers $tiers 
     */
    public function signInTiers($tiers) {
        $this->requireCompte();
        $this->signOutTiers();
        $this->addCredential(self::CREDENTIAL_TIERS);
	if (!is_array($tiers))
	  $tiers = array($tiers);
	foreach ($tiers as $t) {
	  if ($t->type == "Recoltant") {
            $this->addCredential(self::CREDENTIAL_RECOLTANT);
            $this->addCredential(self::CREDENTIAL_DECLARATION);
	  } elseif($t->type == "MetteurEnMarche") {
            $this->addCredential(self::CREDENTIAL_METTEUR_EN_MARCHE);
            if ($t->no_accises) {
	      $this->addCredential(self::CREDENTIAL_GAMMA);
            }
	  } elseif($t->type == "Acheteur") {
            $this->addCredential(self::CREDENTIAL_ACHETEUR);
	  }
	  $ids[] = $t->_id;
	}
	$this->setAttribute(self::SESSION_TIERS, join(',', $ids), self::NAMESPACE_TIERS);
    }
    
    /**
    * 
    */
    protected function clearCredentialsTiers() {
        foreach($this->_credentials_tiers as $credential) {
            $this->removeCredential($credential);
        }
    }
    
    /**
     * 
     */
    public function signOutTiers() {
        $this->_tiers = null;
        $this->getAttributeHolder()->removeNamespace(self::NAMESPACE_TIERS);
        $this->clearCredentialsTiers();
    }

    /**
     * @return _Tiers
     */
    public function getTiers($type = null) {
        $this->requireTiers();
        
        if (is_null($this->_tiers)) {
	  $this->_tiers = array();
	  foreach (split(',', $this->getAttribute(self::SESSION_TIERS, null, self::NAMESPACE_TIERS)) as $id) {
	    $t = sfCouchdbManager::getClient('_Compte')->retrieveDocumentById($id);
	    if (isset($this->_tiers[$t->type]))
		throw new sfException('An user cannot have more than two tiers of the same type');
            $this->_tiers[$t->type] = $t;
	  }
	  if(!$this->_tiers) {
	    $this->signOutCompte();
	    throw new sfException("The tiers does not exist");
	  }
        }
	if (!$type) {
	  if (isset($this->_tiers['Recoltant']))
	    $type = 'Recoltant';
	  else if (isset($this->tiers['Acheteur']))
	    $type = 'Acheteur';
	  else 
	    $type = 'MetteurEnMarche';
	}

	if (!isset($this->_tiers[$type]))
	  throw new sfException('no tiers for type "'.$type.'"');
	return $this->_tiers[$type];
    }
    
    public function getDeclarant() {
      return $this->getTiers('Recoltant');
    }

    /**
     * 
     */
    protected function requireTiers() {
        $this->requireCompte();
        if (!$this->hasCredential(self::CREDENTIAL_TIERS)) {
	  throw new sfException("you must be logged in with a tiers");
        }
    }
    
    /**
     *
     * @param string $namespace 
     */
    public function signOutCompte($namespace) {
        $this->signOutTiers();
        parent::signOutCompte($namespace);
    }
}
