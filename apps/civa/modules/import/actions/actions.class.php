<?php

/**
 * import actions.
 *
 * @package    civa
 * @subpackage import
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class importActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeCsv(sfWebRequest $request)
  {
    $this->csvform = new ImportCSVForm();
    if (!$request->isMethod('post'))
      return;
    $this->csvform->bind($request->getParameter('csv'),$request->getFiles('csv'));
    if (!$this->csvform->isValid())
      return ;
 
    $file = $this->csvform->getValue('file');

    $this->csv = array();

    if (($handle = fopen($file->getTempName(), "r")) !== FALSE) {
      while (($data = fgetcsv($handle)) !== FALSE) {
	$this->csv[] = $data;
      }
      fclose($handle);
    }else{
      throw new Exception('cannot open the file');
    }	
  }
}
