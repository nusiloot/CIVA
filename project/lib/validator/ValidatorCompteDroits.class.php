<?php

class ValidatorCompteDroits extends sfValidatorSchema
{

  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('doc');
    $this->addMessage('invalid', "La somme superficie des acheteurs ne peut être supérieure au total");
  }

  protected function doClean($values)
  {
    $errorSchema = new sfValidatorErrorSchema($this);

    $compte = $this->getCompteSociete();
    
    $droits = $compte->getDroitsTiers();

    $droits_finaux = array();

    foreach($values as $id_compte => $c) {
      if(!isset($c["droits"])) {
        continue; 
      }
      foreach($c["droits"] as $droit) {
        $droits_finaux[$droit] = null;
      }
    }

    if(count($droits_finaux) != count($droits)) {
      $errorSchema->addError(new sfValidatorError($this, 'invalid'));
    }

    if (count($errorSchema))
    {
      throw new sfValidatorErrorSchema($this, $errorSchema);
    }

    return $values;
  }


  protected function getCompteSociete() {
        return $this->getOption('doc');
  }
}