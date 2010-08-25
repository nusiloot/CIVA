<?php

/**
 * recoltant actions.
 *
 * @package    civa
 * @subpackage recoltant
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class recoltantActions extends EtapesActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeLogin(sfWebRequest $request)
  {
    if ($this->getUser()->isAuthenticated()){
       $this->redirect('@mon_espace_civa');
    }elseif($request->getParameter('ticket')){

        error_reporting(E_ALL);
        require_once(sfConfig::get('sf_lib_dir').'/vendor/phpCAS/CAS.class.php');
        //phpCAS::setDebug();

        phpCAS::client(CAS_VERSION_2_0,sfConfig::get('app_cas_domain'), sfConfig::get('app_cas_port'), sfConfig::get('app_cas_path'), false);

        phpCAS::setNoCasServerValidation();

        $this->getContext()->getLogger()->debug('{sfCASRequiredFilter} about to force auth');
        phpCAS::forceAuthentication();
        $this->getContext()->getLogger()->debug('{sfCASRequiredFilter} auth is good');

        $this->getContext()->getUser()->signInWithCas(phpCAS::getUser());
        
    }else{
        $url = 'https://'.sfConfig::get('app_cas_domain').':'.sfConfig::get('app_cas_port').'/'.sfConfig::get('app_cas_path').'/login?service='.$request->getUri();
        $this->redirect($url);
    }



  }

  public function executeLogout(sfWebRequest $request)
  {
        require_once(sfConfig::get('sf_lib_dir').'/vendor/phpCAS/CAS.class.php');
        $this->getUser()->signOut();
        $url = 'http://'.$request->getHost();
        phpCAS::logout();
        $this->redirect($url);
  }
  /**
   *
   * @param sfWebRequest $request 
   */
  public function executeExploitationAdministratif(sfWebRequest $request)
  {
      $this->setCurrentEtape('exploitation_administratif');
      $this->forwardUnless($this->recoltant = $this->getUser()->getRecoltant(), 'declaration', 'monEspaceciva');

      $this->form_gest = new RecoltantExploitantForm($this->getUser()->getRecoltant()->getExploitant());
      $this->form_gest_err = 0;
      $this->form_expl = new RecoltantExploitationForm($this->getUser()->getRecoltant());
      $this->form_expl_err = 0;
        
      if ($request->isMethod(sfWebRequest::POST)) {
	if ($request->getParameter('gestionnaire')) {
	  $this->form_gest->bind($request->getParameter($this->form_gest->getName()));
	  if ($this->form_gest->isValid()) {
	    $this->form_gest->save();
	  }else
	    $this->form_gest_err = 1;            
	}
	if ($request->getParameter('exploitation')) {
	  $this->form_expl->bind($request->getParameter($this->form_expl->getName()));
	  if ($this->form_expl->isValid()) {
	    $this->form_expl->save();
	  }else
	    $this->form_expl_err = 1;            
	}
	$this->redirectByBoutonsEtapes();
      }
  }
}
