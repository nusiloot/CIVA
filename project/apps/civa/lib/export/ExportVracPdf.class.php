<?php

class ExportVracPdf extends ExportDocument {

    const NB_LIGNES_PAR_PAGES = 50;

    protected $type;
    protected $document; 
    protected $nb_pages;
    protected $partial_function;
    protected $file_dir;
    protected $no_cache;
    
    protected $vrac;

    public function __construct($vrac, $partial_function, $type = 'pdf', $file_dir = null, $no_cache = false, $filename = null) {

        $this->type = $type;
        $this->partial_function = $partial_function;
        $this->file_dir = $file_dir;
        $this->no_cache = $no_cache;

        $this->vrac = $vrac;
     //   $this->trieVracForPDF();
        
        $this->init($filename);
    }

    protected function trieVracForPDF() {
//        $dss_sorted = array();
//        foreach ($this->dss as $key => $ds) {
//            if($ds->isDsPrincipale()){
//                $dss_sorted[] = $ds;
//            }
//        }
//        foreach ($this->dss as $key => $ds) {
//            if(!$ds->isDsPrincipale()){
//                $dss_sorted[] = $ds;
//            }
//        }
//        $this->dss = $dss_sorted;
    }
    
    public function generatePDF() {
        if($this->no_cache || !$this->isCached()) {
            $this->create();
        }
        return $this->document->generatePDF($this->no_cache);
    }

    protected function init($filename = null) {       
        $title = 'Contrat numéro '.$this->vrac->_id;
        $header = sprintf("Contrat entre : %s et %s", $this->vrac->acheteur->raison_sociale, $this->vrac->vendeur->raison_sociale);

        if (!$filename) {
            $filename = $this->getFileName(true, true);
        }
        var_dump($filename); exit;
        $config = array('PDF_FONT_SIZE_MAIN' => 9);
        if ($this->type == 'html') {
          $this->document = new PageableHTML($title, $header, $filename, $this->file_dir, ' de ', 'P', $config);
        }else {
          $this->document = new PageablePDF($title, $header, $filename, $this->file_dir, ' de ', 'P', $config);
        }
    }

    public function getFileName($with_name = true, $with_rev = false) {

      return self::buildFileName($this->vrac, $with_name, $with_rev);
    }

    public static function buildFileName($vrac, $with_name = true, $with_rev = false) {
        $filename = sprintf("CONTRAT_%s", $vrac->numero_contrat);        
        
        if($with_name) {            
            $declarant_nom = _TiersClient::getInstance()->find($vrac->mandataire_identifiant,  acCouchdbClient::HYDRATE_JSON)->exploitant->nom;
            $declarant_nom = strtoupper(KeyInflector::slugify($declarant_nom));
            $filename .= '_'.$declarant_nom;
        }

        if($with_rev) {            
            $rev = VracClient::getInstance()->find($vrac->_id, acCouchdbClient::HYDRATE_JSON)->_rev;;
            $rev = md5($rev);
            $filename .= '_'.$rev;
        }

        return $filename.'.pdf';
    }

    protected function create() {
        var_dump("Le vla ton PDF MAN");
    }

    protected function getPartial($templateName, $vars = null) {
      return call_user_func_array($this->partial_function, array($templateName, $vars));
    }

}
