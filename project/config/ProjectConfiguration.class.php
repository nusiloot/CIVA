<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('acCouchdbPlugin');
    $this->enablePlugins('acExceptionNotifierPlugin');
    $this->enablePlugins('acVinDSPlugin');
    $this->enablePlugins('acVinLibPlugin');
    $this->enablePlugins('acVinDocumentPlugin');
  }
}
