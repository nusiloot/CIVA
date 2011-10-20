<?php

class Configuration extends BaseConfiguration {
    public function getArrayAppellationsMout() {
        $appellations = $this->getRecolte();
        $appellations_array_mouts = array();
        foreach ($appellations->filter('^appellation') as $appellation_key => $appellation) {
            if ($appellation->getMout() == 1) {
                $appellations_array_mouts[$appellation_key] = $appellation;
            }
        }
        return $appellations_array_mouts;
    }

    public function getArrayAppellations() {
        $appellations = $this->getRecolte();
        $appellations_array = array();
        foreach ($appellations->filter('^appellation') as $appellation_key => $appellation) {
            $appellations_array[$appellation_key] = $appellation;
        }
        return $appellations_array;
    }

    public function isCompteAdminExist($login, $mot_de_passe) {
        foreach ($this->compte_admin as $item) {
            if (strlen($item->mot_de_passe) > 6) {
                if ($login == $item->login) {
                    $is_mot_de_passe_valid = false;
                    $cryptage = str_replace(array('{', '}'), array('', ''), substr($item->mot_de_passe, 0, 6));
                    $mot_de_passe_compte = substr($item->mot_de_passe, 6, strlen($item->mot_de_passe) - 6);
                    if ($cryptage == 'SSHA') {
                        $is_mot_de_passe_valid = ($mot_de_passe_compte == sha1($mot_de_passe));
                    } elseif ($cryptage == 'TEXT') {
                        $is_mot_de_passe_valid = ($mot_de_passe_compte == $mot_de_passe);
                    }
                    if ($is_mot_de_passe_valid) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private static function normalizeLibelle($libelle) {
      $libelle = preg_replace('/&nbsp;/', '', strtolower($libelle));
      $libelle = str_replace(array('é', 'è', 'ê'), 'e', $libelle);
      $libelle = preg_replace('/[^a-z ]/', '', preg_replace('/  */', ' ', preg_replace('/&([a-z])[^;]+;/i', '\1', $libelle)));
      $libelle = preg_replace('/^\s+/', '', preg_replace('/\s+$/', '', $libelle));
      return $libelle;
    }

    public function identifyProduct($appellation, $lieu, $cepage) {
      $appid = null;
      $lieuid = 'lieu';
      $cepageid = null;
      $libelle = self::normalizeLibelle($appellation);
      foreach ( $this->getRecolte()->filter('^appellation') as $appellation_key => $appellation_obj) {
	if ($libelle == self::normalizeLibelle($appellation_obj->getLibelle())) {
	  $appid=$appellation_key;
	  break;
	}
      }
      if (!$appid)
	return array("error" => $appellation);

      if ($lieu) {
	$libelle = self::normalizeLibelle($lieu);
	foreach($appellation_obj->filter('^lieu') as $lieu_key => $lieu_obj) {
	  if ($lieu_key == 'lieu')
	    break;
	  if ($libelle == self::normalizeLibelle($lieu_obj->getLibelle())) {
	    $lieuid=$lieu_key;
	    break;
	  }
	}
      }
      if ($lieuid == 'lieu') {
	if (!$appellation_obj->exist('lieu'))
	  return array("error" => $lieu);
      }

      $libelle = self::normalizeLibelle($cepage);
      $prodhash = '';
      $evalhash = '';
      $eval = null;
      foreach($appellation_obj->get($lieuid)->getCepages() as $cepage_key => $cepage_obj) {
	$cepage_libelle = self::normalizeLibelle($cepage_obj->getLibelle());
	if ($libelle == $cepage_libelle) {
	  $cepageid = $cepage_key;
	  $prodhash = $cepage_obj->getHash();
	  break;
	}
	//Gestion des cépages tronqués (Gewurzt)
	if (preg_match('/^'.$cepage_libelle.'/', $libelle)) {
	  if ($eval === null) {
	    $eval = $cepage_key;
	    $evalhash = $cepage_obj->getHash();
	  } else
	    $eval = 0;
	}
      }
      if (!$cepageid) {
	if ($eval) {
	  $cepageid = $eval;
	  $prodhash = $evalhash;
	} else
	  return array("error" => $cepage);
      }
      return array("ids" => $appid.'/'.$lieuid.'/'.$cepageid, "hash" => $prodhash);
    }

}
