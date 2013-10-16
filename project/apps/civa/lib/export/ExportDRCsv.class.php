<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class ExportDRCsv
 * @author mathurin
 */
class ExportDRCsv extends ExportCsv {
    
    protected $_headers = array(
        "cvi_recoltant" => "CVI récoltant",
        "nom_recoltant" => "nom récoltant",
        "cvi_acheteur" => "CVI acheteur",
        "nom_acheteur" => "nom acheteur",
        "appellation" => "appellation",
        "lieu" => "lieu",
        "cepage" => "cépage",
        "vtsgn" => "vtsgn",
        "denomination" => "dénomination",
        "superficie_livree" => "superficie livrée",
        "volume_livre" => "volume livré",
        "dont_dplc" => "dont volume à détruire",
        "superficie_totale" => "superficie totale",
        "volume_total" => "volume total",
        "volume_a_detruire_total" => "volume à détruire total",
        "creation_date" => "date de création",
        "validation_date" => "date de validation",
        "validation_user" => "validateur",
    );
    protected $_validation_ligne = array(
        "cvi_acheteur" => array("type" => "double"),
        "nom_acheteur" => array("type" => "string"),
        "cvi_recoltant" => array("type" => "double"),
        "nom_recoltant" => array("type" => "string"),
        "appellation" => array("type" => "string"),
        "lieu" => array("type" => "string"),
        "cepage" => array("type" => "string"),
        "vtsgn" => array("type" => "string"),
        "denomination" => array("type" => "string"),
        "superficie_livree" => array("type" => "float", "format" => "%01.02f"),
        "volume_livre" => array("type" => "float", "format" => "%01.02f"),
        "dont_dplc" => array("type" => "float", "format" => "%01.02f"),
        "superficie_totale" => array("type" => "float", "format" => "%01.02f"),
        "creation_date" => array("type" => "string"),
        "volume_total" => array("type" => "float", "format" => "%01.02f"),
        "volume_a_detruire_total" => array("type" => "float", "format" => "%01.02f"),
    );
    
    protected $_acheteur = null;
    protected $_debug = false;
    protected $_md5 = null;
    protected $_campagne = null;
    protected $dr = null;

    /**
     *
     * @param string $campagne 
     */
    public function __construct($campagne, $cvi, $debug = false) {
        parent::__construct($this->_headers);
        $this->_debug = $debug;
        $this->_campagne = $campagne;
        $this->dr = acCouchdbManager::getClient("DR")->retrieveByCampagneAndCvi($cvi,$campagne);
        if (!$this->dr) {
            throw new sfException("DR not find");
        }
        
        $this->_md5 = $this->calculMd5($this->dr);
    }
    
    public function add($data, $validation = array()) {
        $line = parent::add($data, $validation);
        if ($this->_debug) {
            echo $line;
        }
        return $line;
    }

    public function getMd5() {
        return $this->_md5;
    }
    
    public function export() {
        $dr = $this->dr;

        if ($this->_debug) {
           echo "\n\n ------------ \n" . $dr->get('_id') . "\n ----------- \n";
        }

        foreach ($dr->recolte->getAppellations() as $appellation) {
            foreach ($appellation->getLieux() as $lieu) {
                foreach($lieu->getCouleurs() as $couleur) {
                    foreach ($couleur->getCepages() as $cepage) {
                        foreach ($cepage->getDetail() as $detail) {
                            $this->addDetailTotal($detail);
                            foreach ($detail->filter('negoces|cooperatives') as $acheteurs) {
                                foreach ($acheteurs as $acheteur) {
                                        $this->addDetailAcheteur($acheteur);
                                }
                            }
                        }
                    }
                }
                $this->addLieuTotal($lieu);
                foreach ($lieu->acheteurs as $acheteurs) {
                    foreach ($acheteurs as $cvi_a => $acheteur) {
                            $this->addLieuAcheteur($acheteur);
                    }
                }
            }
        }
        $this->addJeunesVignes($dr);
                
        if ($this->_debug) {
            echo "------------ \n" . count($this->_ids_dr) . " DRs \n ------------\n";
        }
    }
    
    protected function addDetailAcheteur($acheteur) {
        $detail = $acheteur->getParent()->getParent();
        
        $this->add(array(
            "cvi_recoltant" => $detail->getCouchdbDocument()->cvi,
            "nom_recoltant" => $detail->getCouchdbDocument()->declarant->nom,
            "cvi_acheteur" => $acheteur->cvi,
            "nom_acheteur" => null,
            "appellation" => $detail->getCepage()->getLieu()->getAppellation()->getConfig()->getLibelle(),
            "lieu" => $detail->getConfig()->hasLieuEditable() ? $detail->lieu : $detail->getCepage()->getLieu()->getConfig()->getLibelle(),
            "cepage" => $detail->getCepage()->getConfig()->getLibelle(),
            "vtsgn" => $detail->vtsgn,
            "denomination" => $detail->getConfig()->hasDenomination() ? $detail->denomination : null,
            "superficie_livree" => (($detail->volume == $acheteur->quantite_vendue) ? $detail->superficie : null),
            "volume_livre" => $acheteur->quantite_vendue,
            "dont_dplc" => null,
            "superficie_totale" => $detail->superficie,
            "volume_total" => $detail->volume,
            "volume_a_detruire_total" => null,
            "creation_date" => $this->dr->getPremiereModificationDr(),
            "validation_date" => $detail->getCouchdbDocument()->validee,
            "validation_user" => $this->getValidationUser($detail->getCouchdbDocument()),
                ), $this->_validation_ligne);
    }

    protected function addDetailTotal($detail) {
        $lieu = "";
        $denomination = "";

        $this->add(array(
            "cvi_recoltant" => $detail->getCouchdbDocument()->cvi,
            "nom_recoltant" => $detail->getCouchdbDocument()->declarant->nom,
            "cvi_acheteur" => null,
            "nom_acheteur" => null,
            "appellation" => $detail->getCepage()->getLieu()->getAppellation()->getConfig()->getLibelle(),
            "lieu" => $detail->getConfig()->hasLieuEditable() ? $detail->lieu : $detail->getCepage()->getLieu()->getConfig()->getLibelle(),
            "cepage" => $detail->getCepage()->getConfig()->getLibelle(),
            "vtsgn" => $detail->vtsgn,
            "denomination" => $detail->getConfig()->hasDenomination() ? $detail->denomination : null,
            "superficie_livree" => null,
            "volume_livre" => null,
            "dont_dplc" => null,
            "superficie_totale" => $detail->superficie,
            "volume_total" => $detail->volume,
            "volume_a_detruire_total" => null,
            "creation_date" =>  $this->dr->getPremiereModificationDr(),
            "validation_date" => $detail->getCouchdbDocument()->validee,
            "validation_user" => $this->getValidationUser($detail->getCouchdbDocument()),
                ), $this->_validation_ligne);
    }

    protected function addLieuAcheteur(DRRecolteLieuAcheteur $acheteur) {
        $lieu = $acheteur->getLieu();
        $this->add(array(
            "cvi_recoltant" => $acheteur->getCouchdbDocument()->cvi,
            "nom_recoltant" => $acheteur->getCouchdbDocument()->declarant->nom,
            "cvi_acheteur" => $acheteur->cvi,
            "nom_acheteur" => $acheteur->nom,
            "appellation" => $lieu->getAppellation()->getConfig()->getLibelle(),
            "lieu" => $lieu->getConfig()->getLibelle(),
            "cepage" => "TOTAL",
            "vtsgn" => null,
            "denomination" => null,
            "superficie_livree" => $acheteur->superficie,
            "volume_livre" => $acheteur->getVolume(),
            "dont_dplc" => $acheteur->dontdplc,
            "superficie_totale" => $lieu->getTotalSuperficie(),
            "volume_total" => $lieu->getTotalVolume(),
            "volume_a_detruire_total" => $lieu->getUsagesIndustriels(),
            "creation_date" => $this->dr->getPremiereModificationDr(),
            "validation_date" => $acheteur->getCouchdbDocument()->validee,
            "validation_user" => $this->getValidationUser($acheteur->getCouchdbDocument()),
                ), $this->_validation_ligne);
    }
    
    protected function addLieuTotal(DRRecolteLieu $lieu) {
        $this->add(array(
            "cvi_recoltant" => $lieu->getCouchdbDocument()->cvi,
            "nom_recoltant" => $lieu->getCouchdbDocument()->declarant->nom,
            "cvi_acheteur" => null,
            "nom_acheteur" => null,
            "appellation" => $lieu->getAppellation()->getConfig()->getLibelle(),
            "lieu" => $lieu->getConfig()->getLibelle(),
            "cepage" => "TOTAL",
            "vtsgn" => null,
            "denomination" => null,
            "superficie_livree" => null,
            "volume_livre" => null,
            "dont_dplc" => null,
            "superficie_totale" => $lieu->getTotalSuperficie(),
            "volume_total" => $lieu->getTotalVolume(),
            "volume_a_detruire_total" => $lieu->getUsagesIndustriels(),
            "creation_date" => $this->dr->getPremiereModificationDr(),
            "validation_date" => $lieu->getCouchdbDocument()->validee,
            "validation_user" => $this->getValidationUser($lieu->getCouchdbDocument()),
                ), $this->_validation_ligne);
    }
    
    protected function addJeunesVignes(DR $dr) {
        $this->add(array(
            "cvi_recoltant" => $dr->cvi,
            "nom_recoltant" => $dr->declarant->nom,
            "cvi_acheteur" => null,
            "nom_acheteur" => null,
            "appellation" => "Jeunes Vignes",
            "lieu" => null,
            "cepage" => null,
            "vtsgn" => null,
            "denomination" => null,
            "superficie_livree" => null,
            "volume_livre" => null,
            "dont_dplc" => null,
            "superficie_totale" => $dr->jeunes_vignes,
            "volume_total" => null,
            "volume_a_detruire_total" => null,
            "creation_date" => $this->dr->getPremiereModificationDr(),
            "validation_date" => $dr->validee,
            "validation_user" => $this->getValidationUser($dr),
                ), $this->_validation_ligne);
    }
    

    protected function calculMd5($dr) {
       return $dr->_rev;
    }
    
        private function getValidationUser($dr) {
        $user = null;
        if ($dr->exist('utilisateurs')) {
            foreach($dr->utilisateurs->validation as $compte => $date_fr) {
                if (preg_match('/^COMPTE-[0-9]+$/', $compte)) {
                    $user = "Récoltant";
                    break;
                } elseif(preg_match('/^COMPTE-.*civa.*$/', $compte)) {
                    $user = "CIVA";
                    break;
                } elseif(!preg_match('/^COMPTE-/', $compte)) {
                    $user = $compte;
                    break;
                }
            }
        }

        if ((!$user || $user == "CIVA") && strtotime($dr->validee) >= strtotime($this->_campagne.'-12-10') && strtotime($dr->modifiee) >= strtotime($this->_campagne.'-12-10')) {
            $user = 'Automatique';
        } 

        if(!$user) {
            $user = 'Inconnu';
        }

        return $user;
    }
    
}