<?php

class acVinTiersPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
      $this->dispatcher->connect('routing.load_configuration', array('acVinTiersRouting', 'listenToRoutingLoadConfigurationEvent'));
  }
}
