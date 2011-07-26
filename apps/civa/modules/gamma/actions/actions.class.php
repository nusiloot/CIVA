<?php

/**
 * gamma actions.
 *
 * @package    civa
 * @subpackage gamma
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class gammaActions extends sfActions
{
    /**
     *
     * @param sfWebRequest $request
     */
    public function executeProcess(sfWebRequest $request) {
        $inscription = $request->getParameter('gamma_inscription');
        $this->tiers = $this->getUser()->getTiers();
	$type = $request->getParameter('gamma') ;
        if (isset($inscription) && $inscription['choix']) {
		$this->tiers->add('gamma', "INSCRIT");
		$this->tiers->save();
		return $this->redirect(sfConfig::get('app_gamma_url_prod'));
	}
	if (isset($inscription) || !isset($type)) {
		return $this->redirect('@mon_espace_civa');
	}
        if ($type['type_acces'] == 'plateforme') {
            return $this->redirect(sfConfig::get('app_gamma_url_prod'));
        }
	return $this->redirect(sfConfig::get('app_gamma_url_qualif'));
    }
   
    /**
     *
     * @param sfWebRequest $request
     */
    public function executeDownloadNotice(sfWebRequest $request) {
        return $this->renderPdf(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR."images/aide_gamma.pdf", "aide_gamma.pdf");
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeDownloadAdhesion(sfWebRequest $request) {
        return $this->renderPdf(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR."images/AdhesionGamma_EDI_CIVA.pdf", "AdhesionGamma_EDI_CIVA.pdf");
    }
}
