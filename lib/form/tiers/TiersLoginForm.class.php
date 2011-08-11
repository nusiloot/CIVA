<?php

class TiersLoginForm extends BaseForm { 
    protected $_compte = null;
    
    /**
     *
     * @param Compte $compte
     * @param array $options
     * @param string $CSRFSecret 
     */
    public function __construct(_Compte $compte, $options = array(), $CSRFSecret = null) {
        $this->_compte = $compte;
        $this->checkCompte();
        parent::__construct(array(), $options, $CSRFSecret);
    }
    
    /**
     * 
     */
    protected function checkCompte() {
        if (!$this->_compte) {
            throw new sfException("compte does exist");
        }
    }
    
    public function configure() {
        $this->setWidget("tiers", new sfWidgetFormChoice(array("expanded" => true, "choices" => $this->getChoiceTiers())));
        $this->setValidator("tiers", new sfValidatorChoice(array("choices" => array_keys($this->getChoiceTiers()), 
                                                                 "required" => true)));
        
        $this->getValidator("tiers")->setMessage("required", "Champs obligatoire");
        
        $this->widgetSchema->setNameFormat('tiers[%s]');
    }

    /**
     * 
     * @return _Tiers;
     */
    public function process() {
        if ($this->isValid()) {
            return sfCouchdbManager::getClient()->retrieveDocumentById($this->getValue('tiers'));
        } else {
            throw new sfException("must be valid");
        }
    }
    
    public function getChoiceTiers() {
        $choices = array();
        foreach($this->_compte->tiers as $id => $item) {
            $type = null;
            if ($item->type == "Recoltant") {
                $type = "Récoltant";
            } elseif($item->type == "MetteurEnMarche") {
                $type = "Metteur en marché";
            } else {
                $type = $item->type;
            }
            $choices[$id] = $item->nom . ' - ' . $type;
        }
        
        return $choices;
    }
}


