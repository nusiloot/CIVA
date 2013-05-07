<?php

class RecapitulatifForm extends acCouchdbObjectForm {

    protected $is_saisisable = false;

    public function __construct(acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->getDocable()->remove();
    }

    public function configure() {
        $lieu = $this->getObject();
        if($lieu->canHaveUsagesIndustrielsSaisi()){
            $this->setWidgets(array(
                'usages_industriels_saisi' => new sfWidgetFormInputFloat(array()),
            ));

            $this->setValidators(array(
                'usages_industriels_saisi' => new sfValidatorNumber(array('required' => false, 'max' => $this->getObject()->getVolumeRevendiqueWithoutUIS())),
            ));

            $this->getWidget('usages_industriels_saisi')->setLabel('Usages industriels');

            $this->getValidator('usages_industriels_saisi')->setMessage('max', "Les usages industriels ne peuvent pas être supérieurs au volume total récolté");

            $this->is_saisisable = true;

        }

        
        $form_acheteurs = new BaseForm();
        foreach ($lieu->acheteurs as $type => $acheteurs_type) {
            $form_type = new BaseForm();
            foreach ($acheteurs_type as $cvi => $acheteur) {
                $form_type->embedForm($cvi, new RecapitulatifAcheteurForm());
                
                $this->is_saisisable = true;
            }
            $form_acheteurs->embedForm($type, $form_type);
        }
        $this->embedForm('acheteurs', $form_acheteurs);

        
        $this->getValidatorSchema()->setPostValidator(new ValidatorRecapitulatif(null, array('object' => $this->getObject())));
        $this->widgetSchema->setNameFormat('recapitulatif[%s]');

        $this->disableLocalCSRFProtection();
        $this->disabledRevisionVerification();
    }

    public function isSaisisable() {

        return $this->is_saisisable;
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $lieu = $this->getObject();
        $usages_indus = $values['usages_industriels_saisi'];

        if( isset($values['usages_industriels_saisi']))
            $lieu ->set("usages_industriels_saisi", (float)$usages_indus);

        $this->getObject()->getCouchdbDocument()->update();
    }
}

?>