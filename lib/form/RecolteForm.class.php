<?php

class RecolteForm extends sfCouchdbFormDocumentJson {

    const FORM_NAME_NEGOCES = 'negoces';
    const FORM_NAME_COOPERATIVES = 'cooperatives';
    const FORM_SUFFIX_NEW = '_new';
    const FORM_NAME = 'recolte[%s]';

    public function configure() {


        $this->setWidgets(array(
            'denomination' => new sfWidgetFormInputText(),
            'vtsgn' => new sfWidgetFormInputText(),
            'superficie' => new sfWidgetFormInputText(),
            'cave_particuliere' => new sfWidgetFormInputText(),
        ));

        $this->setValidators(array(
            'denomination' => new sfValidatorString(),
            'vtsgn' => new sfValidatorString(),
            'superficie' => new sfValidatorNumber(),
            'cave_particuliere' => new sfValidatorNumber(),
        ));

        $this->configureAcheteurs(self::FORM_NAME_NEGOCES, $this->getObject()->getAcheteursValuesWithCvi('negoces'), $this->getAcheteursNegoces());
        $this->configureAcheteurs(self::FORM_NAME_COOPERATIVES, $this->getObject()->getAcheteursValuesWithCvi('cooperatives'), $this->getAcheteursCooperatives());
        //$this->validatorSchema->setPostValidator();

        $this->widgetSchema->setNameFormat(self::FORM_NAME);
        $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    }

    public function bind(array $taintedValues = null, array $taintedFiles = null) {
        $this->bindCheckDelete(self::FORM_NAME_NEGOCES, $taintedValues);
        $this->configureAcheteursFromBind(self::FORM_NAME_NEGOCES . self::FORM_SUFFIX_NEW, $taintedValues);
        $this->bindCheckDelete(self::FORM_NAME_COOPERATIVES, $taintedValues);
        $this->configureAcheteursFromBind(self::FORM_NAME_COOPERATIVES . self::FORM_SUFFIX_NEW, $taintedValues);

        parent::bind($taintedValues, $taintedFiles);
    }

    protected function configureAcheteurs($name, $values, $acheteurs) {
        $form = new BaseForm();
        foreach ($acheteurs as $cvi) {
            $quantite_vendue = null;
            if (isset($values[$cvi])) {
                $quantite_vendue = $values[$cvi];
            }
            $form->embedForm($cvi, new RecolteAcheteurForm(array('quantite_vendue' => $quantite_vendue)));
        }
        $this->embedForm($name, $form);
    }

    protected function bindCheckDelete($name, $taintedValues) {
        foreach ($this->embeddedForms[$name] as $cvi => $form) {
            if (!isset($taintedValues[$name][$cvi])) {
                unset($this->widgetSchema[$name][$cvi]);
            }
        }
    }

    protected function configureAcheteursFromBind($name, $taintedValues) {
        if (isset($taintedValues[$name])) {
            $form = new BaseForm();
            foreach ($taintedValues as $cvi => $value) {
                $form->embedForm($cvi, new RecolteAcheteurForm(array('quantite_vendue' => $value)));
            }
            $this->embedForm($name, $form);
        }
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $this->getObject()->negoces->clear();
        $this->getObject()->cooperatives->clear();
        
        $this->updateAcheteurs(self::FORM_NAME_NEGOCES, $values, $this->getObject()->getNegoces());
        $this->updateAcheteurs(self::FORM_NAME_NEGOCES . self::FORM_SUFFIX_NEW, $values, $this->getObject()->negoces);
        $this->updateAcheteurs(self::FORM_NAME_COOPERATIVES, $values, $this->getObject()->cooperatives);
        $this->updateAcheteurs(self::FORM_NAME_COOPERATIVES . self::FORM_SUFFIX_NEW, $values, $this->getObject()->cooperatives);
    }

    protected function updateAcheteurs($value_name, $values, $object) {
        if (isset($values[$value_name])) {
            foreach ($values[$value_name ]as $cvi => $value) {
                if ($value['quantite_vendue'] > 0) {
                    $acheteur = $object->add();
                    $acheteur->cvi = $cvi;
                    $acheteur->quantite_vendue = $value['quantite_vendue'];
                }
            }
        }
    }

    protected function getAcheteursNegoces() {
        $acheteurs = $this->getOption('acheteurs_negoce', null);
        if (is_null($acheteurs)) {
            throw new sfException('Option "acheteurs_negoce" is required');
        }
        return $acheteurs;
    }

    protected function getAcheteursCooperatives() {
        $acheteurs = $this->getOption('acheteurs_cooperative', null);
        if (is_null($acheteurs)) {
            throw new sfException('Option "acheteurs_cooperative" is required');
        }
        return $acheteurs;
    }

}