<?php

class sfCouchdbDocument extends sfCouchdbJson {
    protected $_is_new = true;
    protected $_loaded_data = null;
    
    public function loadFromCouchdb(stdClass $data) {
        if(!is_null($this->_loaded_data)) {
            throw new sfCouchdbException("data already load");
        }
        $this->_loaded_data = serialize($data);
        $this->load($data);
    }
    
    public function __toString() {
      return $this->get('_id').'/'.$this->get('_rev');
    }

    public function  __construct() {
      parent::__construct(null, null, $this, "/");
      try{
	if (isset($this->_definition_model)) {
	  $this->type = $this->_definition_model;
	}
      }catch(Exception $e) {
	throw new sfCouchdbException('Model should include Type field in the document root');
      }
    }

    public function isNew() {
      if (!$this->hasField('_rev'))
	return true;
      return is_null($this->get('_rev'));
    }

    public function save() {
      if($this->isModified()) {
          $ret = sfCouchdbManager::getClient()->saveDocument($this);
          $this->_rev = $ret->rev;
          $this->_loaded_data = serialize($this->getData());
          return $ret;
      }
      return false;
    }

    public function getData() {
        $data = parent::getData();
        if ($this->isNew()) {
            unset($data->_rev);
        }
        return $data;
    }

    public static function getDocumentDefinitionModel() {
        throw new sfCouchdbException('Definition model not implemented');
    }    

    public function delete() {
      return sfCouchdbManager::getClient()->deleteDocument($this);
    }
    
    public function update($params = array()) {
      return parent::update($params);
    }
    
    public function isModified() {
        return $this->isNew() || (unserialize($this->_loaded_data) != $this->getData());
    }

    public function  __clone() {
        $this->_rev = null;
        $this->_id = null;
    }
}
