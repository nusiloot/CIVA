<?php

abstract class BaseDRRecolteCouleur extends sfCouchdbDocumentTree {
    public function configureTree() {
       $this->_root_class_name = 'DR';
       $this->_tree_class_name = 'DRRecolteCouleur';
    }
}