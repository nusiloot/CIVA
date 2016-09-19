<?php

class dsComponents extends sfComponents {



    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspace(sfWebRequest $request) {
      $this->ds = $this->getUser()->getDs($this->type_ds);
      $this->ds_editable = $this->getUser()->isDsEditable($this->type_ds);
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspaceEnCours(sfWebRequest $request) {
        $this->ds = $this->getUser()->getDs($this->type_ds);
    }

        /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspaceValidee(sfWebRequest $request) {
        $this->ds = $this->getUser()->getDs($this->type_ds);
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspaceColonne(sfWebRequest $request) {
        $this->tiers = $this->getUser()->getDeclarantDS($this->type_ds);
        $this->dsByperiodes = acCouchdbManager::getClient('DSCiva')->retrieveDsPrincipalesByPeriodeAndCvi($this->tiers->getIdentifiant(), $this->getUser()->getPeriodeDS($this->type_ds)-1);

        krsort($this->dsByperiodes);
    }

    /**
     *
     * @param sfWebRequest $request
     */
    public function executeMonEspaceNoLieuxStockage(sfWebRequest $request) {
    }


}
