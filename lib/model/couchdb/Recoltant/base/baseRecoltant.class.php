<?php

abstract class BaseRecoltant extends sfCouchdbDocument {
    public function setupDefinition() {
        $this->_definition_model = self::getDocumentDefinitionModel();
        $this->_definition_hash = '/';
    }

    public static function getDocumentDefinitionModel() {
        return 'Recoltant';
    }
}
