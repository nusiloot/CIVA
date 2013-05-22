<?php
class dsActions extends sfActions {
    
    public function executeIndex(sfWebRequest $request) {
       if ($request->isMethod(sfWebRequest::POST)) {
           $this->tiers = acCouchdbManager::getClient('Recoltant')->retrieveByCvi($request['cvi']);           
           $dss = DSCivaClient::getInstance()->findOrCreateDssByTiers($this->tiers,date('Y-m-d'));
           foreach ($dss as $ds) {
               $ds->save();
           }
           $this->redirect('ds_lieux_stockage', $this->tiers);
        }
    } 

    public function executeLieuxStockage(sfWebRequest $request)
    {
        $this->tiers = $this->getRoute()->getTiers();
        $this->form = new DSLieuxDeStockageForm($this->tiers);   
        $this->dss = DSCivaClient::getInstance()->findDssByCvi($this->tiers, date('Y-m-d')); 
        $this->ds = DSCivaClient::getInstance()->getDSPrincipale($this->tiers, date('Y-m-d'));
        
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if($this->form->isValid()) {
               $this->dss = $this->form->doUpdateDss($this->dss);
                foreach ($this->dss as $current_ds) {
                    $current_ds->save();
                }
                if($request->isXmlHttpRequest())
                {            
                    return $this->renderText(json_encode(array("success" => true)));                  
                }   
                $this->redirect('ds_edition_operateur', array('id' => $this->ds->_id));
            }
        }
    }
    
    public function executeMonEspace(sfWebRequest $request) {    
         
        $this->tiers = $this->getRoute()->getTiers();        
        $this->dsHistorique = DSClient::getInstance()->getHistoryByOperateur($this->etablissement);
        $this->generationOperateurForm = new DSGenerationOperateurForm();
        
        if ($request->isMethod(sfWebRequest::POST)) {
	          $this->generationOperateurForm->bind($request->getParameter($this->generationOperateurForm->getName()));
	          if ($this->generationOperateurForm->isValid()) {
                $values = $this->generationOperateurForm->getValues();
                $date = $values["date_declaration"];
          	    try {
          	        $ds = DSClient::getInstance()->findOrCreateDsByEtbId($this->etablissement->identifiant, $date);     
          	        $ds->save();
          	    }catch(sfException $e) {
          	        $this->getUser()->setFlash('global_error', $e->getMessage());
          	        $this->redirect('ds');
          	    }                
                return $this->redirect('ds_generation_operateur', $ds);
              }
        }
    }

    public function executeStock(sfWebRequest $request) {    
        $this->ds = $this->getRoute()->getDS();
        $this->tiers = $this->getRoute()->getTiers();
        if(!$this->getRoute()->getNoeud()) {

            return $this->redirect('ds_edition_operateur', $this->ds->getFirstAppellation());
        }

        if($this->getRoute()->getNoeud() instanceof DSAppellation) {
            if(count($this->getRoute()->getNoeud()->getLieux()) < 1 && $this->getRoute()->getNoeud()->getConfig()->hasManyLieu()) {
                
                return $this->redirect('ds_ajout_lieu', $this->getRoute()->getNoeud());
            }

            return $this->redirect('ds_edition_operateur', $this->getRoute()->getNoeud()->getLieux()->getFirst());
        }

        $this->lieu = $this->getRoute()->getNoeud();

        if(count($this->lieu->getProduitsDetails()) < 1) {

            return $this->redirect('ds_ajout_produit', $this->lieu);
        }

        $this->form = new DSEditionFormCiva($this->ds, $this->lieu);

        $this->appellations = $this->ds->declaration->getAppellationsSorted();
        $this->appellation = $this->lieu->getAppellation();
        $this->current_lieu = null;
        $this->isFirstAppellation = ($this->ds->getFirstAppellation()->getHash() == $this->appellation->getHash());
        
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->doUpdateObject();
                $this->ds->save();
                if($request->isXmlHttpRequest())
                {            
                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->ds->get('_id'),"revision" => $this->ds->get('_rev')))));                  
                }
                            
                $next = $this->ds->getNextLieu($this->lieu);
                if($next){
                    $this->redirect('ds_edition_operateur', $next);
                }
                else
                {
                    $this->redirect('ds_recapitulatif_lieu_stockage', array('id' => $this->ds->_id));   
                }
            }
        }
    }

    public function executeAjoutLieu(sfWebRequest $request) {
        $this->ds = $this->getRoute()->getDS();
        $this->tiers = $this->getRoute()->getTiers();
        $this->appellation = $this->getRoute()->getNoeud();

        $this->config_appellation = $this->appellation->getConfig();
        $this->form = new DSEditionAddLieuFormCiva($this->ds, $this->config_appellation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        
        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        $lieu = $this->ds->addLieu($this->form->getValue('hashref'));
        $this->ds->save();

        return $this->redirect('ds_edition_operateur', $lieu);
    }

    public function executeAjoutProduit(sfWebRequest $request) {
        $this->ds = $this->getRoute()->getDS();
        $this->tiers = $this->getRoute()->getTiers();
        $this->lieu = $this->getRoute()->getNoeud();
        $this->config_lieu = $this->lieu->getConfig();
        $this->appellation = $this->lieu->getAppellation();
        $this->form = new DSEditionAddProduitFormCiva($this->ds, $this->config_lieu);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
        
        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }

        $this->ds->addDetail($this->form->getValue('hashref'), $this->form->getValue('lieudit'));
        $this->ds->save();

        return $this->redirect('ds_edition_operateur', $this->lieu);
    }
    
    public function executeRecapitulatifLieuStockage(sfWebRequest $request) {
        $this->ds = $this->getRoute()->getDS();
        $this->tiers = $this->getRoute()->getTiers();
        $suivant = isset($request['suivant']) && $request['suivant'];
        if($suivant){
            $nextDs = DSCivaClient::getInstance()->getNextDS($this->ds);
            if($nextDs){
                $this->redirect('ds_edition_operateur', array('id' => $nextDs->_id,'appellation_lieu' => $nextDs->getFirstAppellation()));
            }
            else{
                $this->redirect('ds_autre', $this->tiers); 
            }
        }
    }
    
    public function executeAutre(sfWebRequest $request)
    {
        $this->tiers = $this->getRoute()->getTiers();
        $this->ds = DSCivaClient::getInstance()->getDSPrincipale($this->tiers, date('Y-m-d'));
        $this->form = new DSEditionCivaAutreForm($this->ds);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                if($request->isXmlHttpRequest())
                {            
                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->ds->get('_id'),"revision" => $this->ds->get('_rev')))));                  
                } 
                $this->redirect('ds_validation', $this->tiers); 
            }
        }
    }
    
    public function executeValidation(sfWebRequest $request)
    {
        $this->tiers = $this->getRoute()->getTiers();
        $this->ds_client = DSCivaClient::getInstance();
        $this->ds_principale = $this->ds_client->getDSPrincipale($this->tiers, date('Y-m-d'));
        
    }
    
}
