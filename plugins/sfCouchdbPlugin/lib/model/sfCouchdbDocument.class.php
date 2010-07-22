<?php

class sfCouchdbDocument extends sfCouchdbJson {
    protected $_is_new = true;

    public function  __construct() {
        parent::__construct(null, null);
    }

    public function isNew() {
      if (!$this->hasField('_rev'))
	return true;
      return is_null($this->get('_rev'));
    }

    public function save() {
        $ret = sfCouchdbManager::getClient()->saveDocument($this);
	$this->_rev = $ret->rev;
	return $ret;
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
}
