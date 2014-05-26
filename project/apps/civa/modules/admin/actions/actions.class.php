<?php

/**
 * admin actions.
 *
 * @package    civa
 * @subpackage admin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class adminActions extends sfActions {

    /**
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutCompteUsed();
        $this->form = new AdminCompteLoginForm(null, array('comptes_type' => array('CompteTiers', 'CompteProxy')), false);
        $this->form_back_future = new AdminBackToTheFutureForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->getUser()->signInCompteUsed($this->form->process());
                $this->redirect('@tiers');
            }
        }
    }

    /**
     *
     * @param sfWebRequest $request 
     */
    public function executeGamma(sfWebRequest $request) {

        $this->forward404Unless($request->isMethod(sfWebRequest::POST));
        if ($request->getParameter('gamma_type_acces') == 'prod') {
            $this->redirect(sfConfig::get('app_gamma_url_prod'));
        } elseif ($request->getParameter('gamma_type_acces') == 'test') {
            $this->redirect(sfConfig::get('app_gamma_url_qualif'));
        }
    }

    /**
     *
     * @param sfRequest $request A request object
     */
    public function executeBackToFuture(sfWebRequest $request) {
        $this->form = new AdminBackToTheFutureForm();

        if (!$request->isMethod(sfWebRequest::POST)) {
            
            return $this->redirect('@admin');
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {

            return $this->redirect('@admin');
        }
            
        $campagne = $this->form->getValue('campagne');

        $this->getUser()->setAttribute('back_to_the_future', $campagne);

        return $this->redirect('@admin');
    }

    public function executeBackToNow(sfWebRequest $request) {
        $this->getUser()->getAttributeHolder()->remove('back_to_the_future');

        return $this->redirect('@admin');
    }
}
