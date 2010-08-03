<?php

/**
 * acheteurs actions.
 *
 * @package    civa
 * @subpackage acheteurs
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class acheteurActions extends EtapesActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeExploitationAcheteurs(sfWebRequest $request) {
        $this->setCurrentEtape('exploitation_acheteurs');

        $this->appellations = ExploitationAcheteursForm::getListeAppellations();

        $this->acheteurs_negociant = include(sfConfig::get('sf_data_dir') . '/acheteurs-negociant.php');
        $this->acheteurs_cave = include(sfConfig::get('sf_data_dir') . '/acheteurs-cave.php');

        $this->acheteurs_negociant_json = array();
        $this->acheteurs_cave_json = array();
        foreach ($this->acheteurs_negociant as $item) {
            $this->acheteurs_negociant_json[] = $item['nom'] . '|@' . $item['cvi'] . '|@' . $item['commune'];
        }
        foreach ($this->acheteurs_cave as $item) {
            $this->acheteurs_cave_json[] = $item['nom'] . '|@' . $item['cvi'] . '|@' . $item['commune'];
        }

        $this->form = new ExploitationAcheteursForm($this->getUser()->getDeclaration()->getAcheteurs());

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                $this->redirectByBoutonsEtapes();
            }
        }
    }

    public function executeExploitationAcheteursTableRowItemAjax(sfWebRequest $request) {
        if ($request->isXmlHttpRequest() && $request->isMethod(sfWebRequest::POST)) {
            $name = $request->getParameter('qualite_name');
            $donnees = $request->getParameter('donnees');
            $nom = $donnees[0];
            $cvi = $donnees[1];
            $commune = $donnees[2];
            $mout = ($request->getParameter('acheteur_mouts', null) == '1');

            $appellations_form = ExploitationAcheteursForm::getListeAppellations();
            if ($mout) {
                $appellations_form = ExploitationAcheteursForm::getListeAppellationsMout();
            }
            $values = array();
            $i = 3;
            foreach ($appellations_form as $key => $item) {
                $values[$key] = (isset($donnees[$i]) && $donnees[$i] == '1');
                $i++;
            }

            $form = ExploitationAcheteursForm::getNewItemAjax($name, $cvi, $values, $appellations_form);

            return $this->renderPartial('exploitationAcheteursTableRowItem', array('nom' => $nom,
                'cvi' => $cvi,
                'commune' => $commune,
                'appellations' => ExploitationAcheteursForm::getListeAppellations(),
                'form_item' => $form[$name.ExploitationAcheteursForm::FORM_SUFFIX_NEW][$cvi],
                'mout' => $mout));
        } else {
            $this->forward404();
        }
    }

}
