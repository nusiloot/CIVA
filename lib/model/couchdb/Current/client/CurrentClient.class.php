<?php

class CurrentClient extends sfCouchdbClient {
  private static $current = array();

  public static function getCurrent() {
    if (self::$current == null) {
        self::$current = CacheFunction::cache('model', array(sfCouchdbManager::getClient(), 'retrieveDocumentById'), array('CURRENT'));
    }
    return self::$current;
  }
  public function retrieveCurrent() {
    return parent::retrieveDocumentById('CURRENT');
  }
}
