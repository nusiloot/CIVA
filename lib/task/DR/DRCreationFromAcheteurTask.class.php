<?php

class DRCreationFromAcheteur extends sfBaseTask {

    protected function configure() {
      $this->addOptions(array(
			      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'civa'),
			      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
			      new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
			      new sfCommandOption('removedb', null, sfCommandOption::PARAMETER_REQUIRED, '= yes if remove the db debore import [yes|no]', 'no'),
			      new sfCommandOption('cvi', null, sfCommandOption::PARAMETER_OPTIONAL, 'CVI du récoltant dont il faut générer une DR', ''),
			      new sfCommandOption('save-error', null, sfCommandOption::PARAMETER_OPTIONAL, 'Sauve la DR même si les données importée sont en erreur', ''),
			      new sfCommandOption('save-vigilance', null, sfCommandOption::PARAMETER_OPTIONAL, 'Sauve la DR même si les données importée sont en vigileance', ''),
			      new sfCommandOption('year', null, sfCommandOption::PARAMETER_REQUIRED, 'Année de la DR à générer', '')
			      ));

      $this->namespace = 'DR';
      $this->name = 'creationFromAcheteur';
      $this->briefDescription = '';
      $this->detailedDescription = <<<EOF
	Generate DR
EOF;
    }

    protected function createOne($cvi, $year) {
	$tiers = sfCouchdbManager::getClient('Recoltant')->retrieveByCvi($cvi);
	if (!$tiers) {
	  print "ERROR: ".$cvi." n'existe pas\n";
	  return false;
	}
	$dr = sfCouchdbManager::getClient('DR')->retrieveByCampagneAndCvi($cvi, $year);
	if ($dr) {
	  print "LOG: DR pour ".$cvi." existe\n";
	  return false;
	}

	$import_from = array();
	try{
	  $dr = sfCouchdbManager::getClient('DR')->createFromCSVRecoltant($tiers, $import_from);
	  $check = $dr->check();
	  if (count($check['erreur']) || count($check['vigilance'])) {
	    print "ERROR: ".$dr->_id." a des erreurs ou des points de vigilance\n";
	    if (count($check['vigilance']) && !$this->save_vigilance)
	      return false;
	    if (count($check['erreur']) && !$this->save_error)
		return false;
	    
	  }else{
	    $tiers = sfCouchdbManager::getClient('Recoltant')->retrieveByCvi($dr->getCVI());
	    if (!$tiers) {
	      print "ERROR: unknown tiers".$dr->getCVI()."\n";
	      return false;
	    }
	    $dr->validate($tiers);
	  }
	}catch(sfException $e) {
	  print 'ERROR: '.$e->getMessage()."\n";
	  return false;
	}
	try {
	  $dr->save();
	}catch(sfException $e) {	
	  print "ERROR: ".$dr->_id." NOT saved\n";
	  return false;
	}
	print "LOG: ".$dr->_id." saved\n";
	return true;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
	$this->save_error = $options['save-error'];
	$this->save_vigilance = $options['save-vigilance'] || $options['save-error'];
	  
	if ($options['cvi'] && $options['year']) {
	  return $this->createOne($options['cvi'], $options['year']);
	}

	if (!$options['year']) {
	  print "ERROR : options --year needed\n";
	  return ;
	}

	$CVIs = sfCouchdbManager::getClient()
	  ->getView("CSV", "recoltant");

	foreach ($CVIs->rows as $o) {
	  print $o->key[0]."\n";
//	  $this->createOne($o->key[0], $options['year']);
	}
    }

  }