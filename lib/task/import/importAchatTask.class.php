<?php

class importAchatTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        // $this->addArguments(array(
        //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
        // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
                // add your own options here
            new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
            new sfCommandOption('removedb', null, sfCommandOption::PARAMETER_REQUIRED, '= yes if remove the db debore import [yes|no]', 'no'),
            new sfCommandOption('year', null, sfCommandOption::PARAMETER_REQUIRED, 'year', '09'),
            new sfCommandOption('file', null, sfCommandOption::PARAMETER_REQUIRED, 'data file', null),

        ));

        $this->namespace = 'import';
        $this->name = 'Achat';
        $this->briefDescription = 'import csv achat file';
        $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        ini_set('memory_limit', '512M');
        set_time_limit('3600');
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
		
	if (!$options['file']) {
	  $options['file'] = sfConfig::get('sf_data_dir') . '/import/' . $options['year'].'/Achat'.$options['year'];
	}

	if($options['removedb'] == 'yes' && $options['import'] == 'couchdb') {
	  if (sfCouchdbManager::getClient()->databaseExists()) {
	    sfCouchdbManager::getClient()->deleteDatabase();
	  }
	  sfCouchdbManager::getClient()->createDatabase();
	}

	$docs = array();

        foreach (file($options['file']) as $a) {
	  $json = new stdClass();
	  $achat = explode(',', preg_replace('/"/', '', $a));
	  if (!isset($achat[2]) || !$achat[2] || !strlen($achat[2]) || ($achat[4] != 'C' && $achat[4] != 'N' && $achat[4] != 'X' ))
	    continue;
	  $json->_id = 'ACHAT-'.$achat[2];
	  $json->cvi = $achat[2];
	  $json->civaba = $achat[1];
	  $json->type = "Acheteur";
          if($achat[4] == 'N' || $achat[4] == 'X')
            $json->qualite = 'Negociant';
          else if($achat[4] == 'C')
            $json->qualite = 'Cooperative';
	  $json->nom = rtrim(preg_replace('/\s{4}\s*/', ', ', $achat[5]));
	  $json->commune = rtrim($achat[6]);
	  $json->achnum = $achat[0];
	  $docs[] = $json;
	}
	if ($options['import'] == 'couchdb') {
	  foreach ($docs as $data) {
            $this->log($data->_id);
            $doc = sfCouchdbManager::getClient()->retrieveDocumentById($data->_id);
            if ($doc) {
                $doc->delete();
                unset($doc);
            }
	    $doc = sfCouchdbManager::getClient()->createDocumentFromData($data);
	    $doc->save();
            unset($doc);
	  }
	  return;
	}
	echo '{"docs":';
	echo json_encode($docs);
	echo '}';
    }

    private function recode_number($val) {
        return preg_replace('/^\./', '0.', $val) + 0;
    }

}