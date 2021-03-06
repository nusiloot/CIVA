<?php

class EtablissementExportCsvTask extends sfBaseTask
{

  protected $_insee = null;

  protected function configure()
  {
     $this->addArguments(array(
     ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'civa'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
    ));

    $this->namespace        = 'etablissement';
    $this->name             = 'export-csv';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [importTiers3|INFO] task does things.
Call it with:

  [php symfony importTiers3|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $results = EtablissementAllView::getInstance()->find();

        foreach($results->rows as $row) {
            $etablissement = EtablissementClient::getInstance()->find($row->id);
            echo implode(";", EtablissementCsvFile::export($etablissement))."\n";
        }

    }
}
