<?php

abstract class BaseDRRecolteLieuAcheteur extends acCouchdbDocumentTree {
    public function configureTree() {
       $this->_root_class_name = 'DR';
       $this->_tree_class_name = 'DRRecolteLieuAcheteur';
    }
}
