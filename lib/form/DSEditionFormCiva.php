<?php

class DSEditionFormCiva extends acCouchdbForm {

    protected $ds = null;
    protected $noeud = null;

    public function __construct(acCouchdbJson $ds, DSLieu $noeud, $defaults = array(), $options = array(), $CSRFSecret = null) {

       $this->ds = $ds;
       $this->noeud = $noeud;

       foreach ($this->getProduitsDetails() as $hash => $detail) {     
            $form_key = $detail->getHashForKey();
            
            if(!$detail->getCepage()->no_vtsgn){
                $defaults[DSCivaClient::VOLUME_VT.$form_key] = sprintf("%01.02f", round($detail->volume_vt, 2));
                $defaults[DSCivaClient::VOLUME_SGN.$form_key] = sprintf("%01.02f", round($detail->volume_sgn));
            }  
            $defaults[DSCivaClient::VOLUME_NORMAL.$form_key] = sprintf("%01.02f", round($detail->volume_normal));     
        }
        parent::__construct($ds, $defaults, $options, $CSRFSecret);
    }

    public function configure() {
        foreach ($this->getProduitsDetails() as $hash => $detail) {
          $key = $detail->getHashForKey();         
          if(!$detail->getCepage()->no_vtsgn){
            $this->setWidget(DSCivaClient::VOLUME_VT . $key, new sfWidgetFormInputFloat(array(), array('size' => '6')));
            $this->setValidator(DSCivaClient::VOLUME_VT . $key, new sfValidatorNumber(array('required' => false)));
            $this->widgetSchema->setLabel(DSCivaClient::VOLUME_VT . $key, DSCivaClient::VOLUME_VT);

            $this->setWidget(DSCivaClient::VOLUME_SGN . $key, new sfWidgetFormInput(array(), array('size' => '6')));
            $this->setValidator(DSCivaClient::VOLUME_SGN . $key, new sfValidatorNumber(array('required' => false)));
            $this->widgetSchema->setLabel(DSCivaClient::VOLUME_SGN . $key, DSCivaClient::VOLUME_SGN);
          }
          $this->setWidget(DSCivaClient::VOLUME_NORMAL . $key, new sfWidgetFormInput(array(), array('size' => '6')));
	  $this->setValidator(DSCivaClient::VOLUME_NORMAL . $key, new sfValidatorNumber(array('required' => false)));
	  $this->widgetSchema->setLabel(DSCivaClient::VOLUME_NORMAL . $key, DSCivaClient::VOLUME_NORMAL);
        }
        $this->widgetSchema->setNameFormat('ds[%s]');
    }

    public function doUpdateObject() {  
        $values = $this->values;
        foreach ($values as $prodKey => $volumeRev) {
	      if (substr($prodKey, 0, strlen(DSCivaClient::VOLUME_NORMAL)) === DSCivaClient::VOLUME_NORMAL){ 
		$this->updateVol(DSCivaClient::VOLUME_NORMAL,$this->keyTohash(substr($prodKey,strlen(DSCivaClient::VOLUME_NORMAL))), $volumeRev);
              }
	      if (substr($prodKey, 0, strlen(DSCivaClient::VOLUME_VT)) === DSCivaClient::VOLUME_VT){ 
		$this->updateVol(DSCivaClient::VOLUME_VT,$this->keyTohash(substr($prodKey,strlen(DSCivaClient::VOLUME_VT))), $volumeRev);
              }
	      if (substr($prodKey, 0, strlen('sgn')) === DSCivaClient::VOLUME_SGN){
		$this->updateVol(DSCivaClient::VOLUME_SGN,$this->keyTohash(substr($prodKey,strlen(DSCivaClient::VOLUME_SGN))), $volumeRev);
              }
            }
    }
    
    public function getProduitsDetails() {


        return $this->noeud->getProduitsDetailsSorted();
    }
    
    private function keyTohash($key) {

        return str_replace('-','/',$key);
    }
    

    public function updateVol($kind, $prodKey, $volume) {
        if ($this->getDocument()->get($prodKey)) {
            $this->getDocument()->get($prodKey)->updateVolume($kind,$volume);
        }
    }
     
}
