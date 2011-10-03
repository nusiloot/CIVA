<?php

/**
 * upload actions.
 *
 * @package    civa
 * @subpackage upload
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class uploadActions extends sfActions
{

 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeCsvUpload(sfWebRequest $request)
  {
    $this->csvform = new UploadCSVForm();
    if (!$request->isMethod('post'))
      return;
    $this->csvform->bind($request->getParameter('csv'),$request->getFiles('csv'));
    if (!$this->csvform->isValid())
      return ;
    return $this->redirect('upload/csvView?md5='.$this->csvform->getValue('file')->getMd5());
  }

  public static function cleanTable($table) {
    for($i = 0 ; $i < count($table) ; $i++) { 
      $table[$i] = preg_replace('/^\s+/', '', preg_replace('/\s+$/', '', $table[$i]));
    }
    return $table;
  }

  public function executeCsvView(sfWebRequest $request) 
  {
    $md5 = $request->getParameter('md5');

    $this->csv = new CsvFile(sfConfig::get('sf_data_dir').'/upload/'.$md5);
    $cpt = -1;
    $this->errors = array();
    $this->warnings = array();
    foreach ($this->csv->getCsv() as $line) {
      $line = self::cleanTable($line);
      $cpt++;
      $this->errors[$cpt] = array();
      $this->warnings[$cpt] = array();
      if (!$this->hasCVI($line)) {
	if (!$cpt)
	  continue;
	$this->errors[$cpt][] = 'no cvi';
      }
      ;
      if ($errorprod = $this->cannotIdentifyProduct($line))
	$this->errors[$cpt][] = 'Il nous  est impossible de repérer le produit correspondant à «'.$errorprod.'», merci de vérifier les libellés.';
      else if (!$this->hasVolume($line))
	$this->errors[$cpt][] = 'Le volume ne devrait pas être absent ou null.';
      else if (!$this->mayHaveSuperficie($line))
	$this->errors[$cpt][] = 'La superficie erronnée.';
      if (!$this->isVTSGNOk($line))
	$this->errors[$cpt][] = 'Le champ VT/SGN est non valide.';
      if (!$this->hasGoodUnit($line)) {
	$this->warnings[$cpt][] = 'Les unités ne semblent pas en ares et hectolitres.';
      }
      if (!$this->needSuperficie($line)) {
	$this->warnings[$cpt][] = 'La superficie devrait être renseignée';
      }
      if (!$this->canHaveRebeche($line)) {
	$this->warnings[$cpt][] = 'Vous ne pouvez pas déclarer de rebeche';
      }
    }
    $this->recap = new stdClass();
    $this->recap->errors = array();
    $this->recap->warnings = array();
    
    $nb_errors = 0;
    foreach ($this->errors as $line => $array) {
      foreach ($array as $value) {
	$nb_errors++;
	$this->recap->errors[$value][] = $line +1;
      }
    }

    $nb_warnings = 0;
    foreach ($this->warnings as $line => $array) {
      foreach ($array as $value) {
	$nb_warnings++;
	$this->recap->warnings[$value][] = $line +1;
      }
    }

    if (!$nb_errors) {
      $cvi = $this->getUser()->getTiers()->cvi;
      $csv = sfCouchdbManager::getClient('CSV')->retrieveByCviAndCampagneOrCreateIt($cvi);
      $csv->storeCSV($this->csv);
      $csv->save();
    }
  }
  
  protected function isVTSGNOk($line) {
    if (!$line[CsvFile::CSV_VTSGN])
      return true;
    if ($line[CsvFile::CSV_VTSGN] == 'VT')
      return true;
    if ($line[CsvFile::CSV_VTSGN] == 'SGN')
      return true;
    return false;
  }

  protected function hasCVI($line) {
    if (preg_match('/^6[78]\d{8}$/', $line[CsvFile::CSV_ACHETEUR_CVI]) && preg_match('/^6[78]\d{8}$/', $line[CsvFile::CSV_RECOLTANT_CVI]))
      return true;
    return false;
  }
  protected function cannotIdentifyProduct($line) {
    $this->no_volume = false;
    $this->no_surface = false;
    $this->is_rebeche = false;

    if (!preg_match('/[a-z]/i', $line[CsvFile::CSV_APPELLATION])) {
	return "appellation vide";
    }

    if (strtolower($line[CsvFile::CSV_APPELLATION]) == 'jeunes vignes') {
      $this->no_volume = true;
      return false;
    }

    if (!preg_match('/[a-z]/i', $line[CsvFile::CSV_CEPAGE])) {
        return "cepage vide";
    }

    $prod = ConfigurationClient::getConfiguration()->identifyProduct($line[CsvFile::CSV_APPELLATION], 
								     $line[CsvFile::CSV_LIEU], 
								     $line[CsvFile::CSV_CEPAGE]);
    if (isset($prod['hash'])) {
      if (preg_match('/_(RB|ED)$/', $prod['hash'])) {
	$this->no_surface = true;
      }
      
      if (preg_match('/_RB$/', $prod['hash'])) {
	$this->is_rebeche = true;
      }
    }

    if (!isset($prod['error']))
      return false;
    return $prod['error'];
  }

  protected function canHaveRebeche($line) {
    if (!$this->is_rebeche)
      return true;
    try {
    if (!$this->getUser()->getTiers('Acheteur')->getQualite() != 'cave')
      return false;
    }catch(Exception $e) { return true;}
    return true;
  }


  private function isPositive($val) {
    $val = preg_replace('/,/', '.', $val);
    if (!is_numeric($val))
      return false;
    if ($val <= 0)
      return false;
    return true;
  }

  protected function hasGoodUnit($line) {
    if (
	(preg_match('/^[0-9,\.]+$/', $line[CsvFile::CSV_SUPERFICIE]) || !$line[CsvFile::CSV_SUPERFICIE]) &&
	(!$line[CsvFile::CSV_VOLUME] || preg_match('/^[0-9,\.]+$/', $line[CsvFile::CSV_VOLUME]))
	)
      return true;
    if (	$line[CsvFile::CSV_VOLUME] * 100 / $line[CsvFile::CSV_SUPERFICIE] > 10)
      return true;
    return false;
  }

  protected function hasVolume($line) {
    if ($this->no_volume)
      return true;
    return $this->isPositive($line[CsvFile::CSV_VOLUME]);
  }
  protected function mayHaveSuperficie($line) {
    if (!isset($line[CsvFile::CSV_SUPERFICIE]) || !$line[CsvFile::CSV_SUPERFICIE])
      return true;
    return $this->isPositive($line[CsvFile::CSV_SUPERFICIE]);
  }
  protected function needSuperficie($line) {
    if ($this->no_superficie)
      return true;
    try {
    if (!$this->getUser()->getTiers('Acheteur')->getQualite() != 'negociant' && (!isset($line[CsvFile::CSV_SUPERFICIE]) || !$line[CsvFile::CSV_SUPERFICIE]))
      return false;
    }catch(Exception $e) {return true;}
    return true;
  }
}
