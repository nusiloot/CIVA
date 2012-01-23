<?php

class ExportDRXml {

    const DEST_DOUANE = 'Douane';
    const DEST_CIVA = 'Civa';

    protected $content = null;
    protected $partial_function = null;
    protected $destinataire = null;
    
    public static function sortXML($a, $b) {
        $a = preg_replace('/L/', '', $a);
        $b = preg_replace('/L/', '', $b);

        $a = preg_replace('/_[0-9]+/', '', $a);
        $b = preg_replace('/_[0-9]+/', '', $b);
        
        return $a > $b;
    }

    private static $type2douane = array('negoces' => 'L6', 'mouts' => 'L7', 'cooperatives' => 'L8');

    private function setAcheteursForXml(&$xml, $obj, $type) {
        $acheteurs = array();
        foreach($obj->getVolumeAcheteurs($type) as $cvi => $volume) {
            $item = array('numCvi' => $cvi, 'volume' => $volume);
            $xml[self::$type2douane[$type].'_'.$cvi] = $item;
        }
    }

    public function  __construct($dr, $partial_function, $destinataire = self::DEST_DOUANE) {
        $this->partial_function = $partial_function;
        $this->destinataire = $destinataire;
        $this->create($dr);
    }
    
    public function getContent() {
        return $this->content;
    }

    protected function getPartial($templateName, $vars = null) {
      return call_user_func_array($this->partial_function, array($templateName, $vars));
    }

    protected function create($dr) {
        $xml = array();
        foreach ($dr->recolte->getConfigAppellations() as $appellation_config) {
            if (!$dr->recolte->exist($appellation_config->getKey())) {
                continue;
            }
            $appellation = $dr->recolte->get($appellation_config->getKey());
            foreach ($appellation->getConfig()->getLieux() as $lieu_config) {
                if (!$appellation->exist($lieu_config->getKey())) {
                    continue;
                }
                $lieu = $appellation->get($lieu_config->getKey());
                foreach($lieu_config->getCouleurs() as $couleur_config) {
                    if (!$lieu->exist($couleur_config->getKey())) {
                        continue;
                    }
                    $couleur = $lieu->get($couleur_config->getKey());

                    $object = $lieu;
                    if ($lieu_config->hasManyCouleur()) {
                        $object = $couleur;
                    }

                    //Comme il y a plusieurs acheteurs par lignes, il faut passer par une structure intermédiaire
                    $acheteurs = array();
                    $total = array();

                    $total['L1'] = $object->getCodeDouane();
                    $total['L3'] = 'B';
                    $total['L4'] = $object->getTotalSuperficie();
                    $total['exploitant'] = array();
                    $total['exploitant']['L5'] = $object->getTotalVolume();
                    $this->setAcheteursForXml($total['exploitant'], $object, 'negoces');
                    $this->setAcheteursForXml($total['exploitant'], $object, 'mouts');
                    $this->setAcheteursForXml($total['exploitant'], $object, 'cooperatives');
                    $total['exploitant']['L9'] = $object->getTotalCaveParticuliere();
                    $total['exploitant']['L10'] = $object->getTotalCaveParticuliere() + $object->getTotalVolumeAcheteurs('cooperatives'); //Volume revendique non negoces
                    $total['exploitant']['L11'] = 0; //HS
                    $total['exploitant']['L12'] = 0; //HS
                    $total['exploitant']['L13'] = 0; //HS
                    $total['exploitant']['L14'] = 0; //Vin de table + Rebeches
                    $l15 = $object->volume_revendique - $object->getTotalVolumeAcheteurs('negoces') - $object->getTotalVolumeAcheteurs('mouts');
                    if ($l15 < 0) {
                        $l15 = 0;
                    }
                    $total['exploitant']['L15'] = $l15; //Volume revendique
                    $total['exploitant']['L16'] = $object->dplc; //DPLC
                    $total['exploitant']['L17'] = 0; //HS
                    $total['exploitant']['L18'] = 0; //HS
                    $total['exploitant']['L19'] = 0; //HS

                    $colass = null;

                    if ($this->destinataire == self::DEST_DOUANE && 
                        count($couleur_config->getCepages()) == 1 && 
                        count($couleur->getCepages()) == 1 /*&&
                        !$couleur_config->getCepages()->getFirst()->hasVtsgn()*/) {
                        $cepage = $couleur->getCepages()->getFirst();
                        //$total['mentionVal'] = '';
                        foreach ($cepage->detail as $detail) {
                            if(count($cepage->detail) == 1) {
                                $detail = $cepage->detail[0];
                                if ($appellation_config->hasLieuEditable()) {
                                    //$total['mentionVal'] = $detail->lieu;    
                                } else {
                                    //$total['mentionVal'] = $detail->denomination;
                                }
                                if (!($object->getTotalVolume() > 0)) {
                                    if ($detail->exist('motif_non_recolte') &&  $detail->motif_non_recolte) {
                                        $total['motifSurfZero'] = strtoupper($detail->motif_non_recolte);
                                    } elseif(!isset($total['motifSurfZero'])) {
                                        $total['motifSurfZero'] = 'PC';
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($couleur_config->getCepages() as $cepage_config) {
                            if (!$couleur->exist($cepage_config->getKey())) {
                                continue;
                            }
                            $cepage = $couleur->get($cepage_config->getKey());

                            if($this->destinataire == self::DEST_DOUANE) {
                                if (in_array($appellation->getKey(), array('appellation_ALSACEBLANC', 'appellation_LIEUDIT')) && $cepage->getKey() == 'cepage_ED') {
                                    continue;
                                }
                            }

                            $cols = array();
                            foreach ($cepage->detail as $detail) {

                                $col = array();

                                $col['L1'] = $detail->getCodeDouane();

                                // SI PAS D'AUTRE AOC
                                if ($appellation->getKey() == 'appellation_VINTABLE' && $dr->recolte->getAppellations()->count() > 1) {
                                    $col['L1'] = $detail->getCepage()->getCodeDouane('AOC');
                                }

                                $col['L3'] = 'B';
                                if ($appellation_config->hasLieuEditable()) {
                                    $col['mentionVal'] = $detail->lieu;
                                } else {
                                    $col['mentionVal'] = $detail->denomination;
                                }
                                
                                $col['L4'] = $detail->superficie;
                                
                                $col['exploitant'] = array();
                                $col['exploitant']['L5'] = $detail->volume ; //Volume total sans lies

                                $this->setAcheteursForXml($col['exploitant'], $detail, 'negoces');
                                $this->setAcheteursForXml($col['exploitant'], $detail, 'mouts');
                                $this->setAcheteursForXml($col['exploitant'], $detail, 'cooperatives');

                                $col['exploitant']['L9'] = $detail->cave_particuliere; //Volume revendique sur place
                                $col['exploitant']['L10'] = $detail->cave_particuliere + $detail->getTotalVolumeAcheteurs('cooperatives'); //Volume revendique non negoces
                                $col['exploitant']['L11'] = 0; //HS
                                $col['exploitant']['L12'] = 0; //HS
                                $col['exploitant']['L13'] = 0; //HS
                                $col['exploitant']['L14'] = 0; //Vin de table + Rebeches
                                $col['exploitant']['L15'] = 0; //Volume revendique
                                $col['exploitant']['L16'] = 0; //DPLC
                                $col['exploitant']['L17'] = 0; //HS
                                $col['exploitant']['L18'] = 0; //HS
                                $col['exploitant']['L19'] = 0; //HS

                                if ($cepage->getKey() == 'cepage_RB' && $appellation->getKey() == 'appellation_CREMANT') {
                                    $col['exploitant']['L14'] = $detail->volume;
                                } elseif($appellation->getKey() == 'appellation_VINTABLE') {
                                    $l14 = $detail->volume - $detail->getTotalVolumeAcheteurs('negoces') - $detail->getTotalVolumeAcheteurs('mouts');
                                    if ($l14 < 0) {
                                        $l14 = 0;
                                    }
                                    $col['exploitant']['L14'] = $l14;
                                /*} elseif($appellation->getKey() == 'appellation_GRDCRU') {
                                    $l15 = $detail->volume - $detail->getTotalVolumeAcheteurs('negoces') - $detail->getTotalVolumeAcheteurs('mouts');
                                    if ($l15 < 0) {
                                        $l15 = 0;
                                    }
                                    $col['exploitant']['L15'] = $l15;
                                    //$col['exploitant']['L16'] = $detail->volume_dplc; */
                                } else {
                                    $l15 = $detail->volume - $detail->getTotalVolumeAcheteurs('negoces') - $detail->getTotalVolumeAcheteurs('mouts');
                                    if ($l15 < 0) {
                                        $l15 = 0;
                                    }
                                    $col['exploitant']['L15'] = $l15;
                                }

                                if ($this->destinataire == self::DEST_DOUANE) {
                                    if ($appellation->getKey() == 'appellation_GRDCRU') {
                                        if ($detail->cave_particuliere) {
                                            $col['exploitant']['L5'] += $detail->cave_particuliere * $dr->getRatioLies();  //Volume total avec lies
                                            $col['exploitant']['L9'] += $detail->cave_particuliere * $dr->getRatioLies();
                                            $col['exploitant']['L10'] += $detail->cave_particuliere * $dr->getRatioLies();
                                        }
                                    }
                                }

                                uksort($col['exploitant'], 'exportDRXml::sortXML');

                                if ($detail->exist('motif_non_recolte') && $detail->motif_non_recolte) {
                                    $col['motifSurfZero'] = strtoupper($detail->motif_non_recolte);
                                } elseif(!$detail->volume || $detail->volume == 0) {
                                    if ($appellation->getKey() == 'appellation_ALSACEBLANC' &&
                                        $dr->recolte->exist('appellation_ALSACEBLANC') &&
                                        $dr->recolte->get('appellation_ALSACEBLANC')->lieu->couleur->exist('cepage_ED') &&
                                        $dr->recolte->get('appellation_ALSACEBLANC')->lieu->couleur->get('cepage_ED')->getTotalVolume() > 0) {
                                        $col['motifSurfZero'] = 'AE';
                                    } else {
                                        $col['motifSurfZero'] = 'PC';
                                    }
                                    if ($appellation->getKey() == 'appellation_LIEUDIT' &&
                                        $dr->recolte->exist('appellation_LIEUDIT') &&
                                        $dr->recolte->get('appellation_LIEUDIT')->lieu->couleur->exist('cepage_ED') &&
                                        $dr->recolte->get('appellation_LIEUDIT')->lieu->couleur->get('cepage_ED')->getTotalVolume() > 0) {
                                        $col['motifSurfZero'] = 'AE';
                                    } else {
                                        $col['motifSurfZero'] = 'PC';
                                    }
                                }

                                if (!($object->getTotalVolume() > 0)) {
                                    if ($detail->exist('motif_non_recolte') && $detail->motif_non_recolte) {
                                        $total['motifSurfZero'] = strtoupper($detail->motif_non_recolte);
                                    } elseif(!isset($total['motifSurfZero'])) {
                                        $total['motifSurfZero'] = 'PC';
                                    }
                                }

                                if ($cepage->getKey() == 'cepage_RB' && $appellation->getKey() == 'appellation_CREMANT') {
                                    unset($col['L3'], $col['L4'], $col['mentionVal']);
                                    $colass = $col;
                                } else {
                                    $cols[$detail->vtsgn][] = $col;
                                }
                            }


                            if ($this->destinataire == self::DEST_DOUANE) {
                                $col_final = null;
                                foreach($cols as $vtsgn => $groupe_cols) {
                                    if (count($groupe_cols) > 0) {
                                        $col_final = $groupe_cols[0];
                                        unset($groupe_cols[0]);
                                    }
                                    foreach($groupe_cols as $col) {
                                        $col_final['L4'] += $col['L4'];
                                        unset($col_final['mentionVal']);
                                        if ($cepage->getTotalVolume() != 0) {
                                            unset($col_final['motifSurfZero']);
                                        }
                                        foreach($col_final['exploitant'] as $expl_key => $value) {
                                            if(is_array($value)) {
                                                if(array_key_exists($expl_key, $col['exploitant'])) {
                                                    $col_final['exploitant'][$expl_key]['volume'] += $col['exploitant'][$expl_key]['volume'];
                                                }
                                            } else {
                                                $col_final['exploitant'][$expl_key] += $col['exploitant'][$expl_key];
                                            }
                                        }
                                        foreach($col['exploitant'] as $expl_key => $value) {
                                            if(is_array($value)) {
                                                if(!array_key_exists($expl_key, $col_final['exploitant'])) {
                                                    $col_final['exploitant'][$expl_key] = $value;
                                                }
                                            }
                                        }
                                    }
                                    uksort($col_final['exploitant'], 'exportDRXml::sortXML');

                                    if(!$vtsgn && $appellation->getKey() == 'appellation_GRDCRU') {
                                        $l15 = $col_final['exploitant']['L5'] - $cepage->dplc;
                                        if ($l15 < 0) {
                                            $l15 = 0;
                                        }
                                        $col_final['exploitant']['L15'] = $l15;
                                        $col_final['exploitant']['L16'] = $cepage->dplc;
                                    }

                                    $xml[] = $col_final;
                                }
                            } elseif($this->destinataire == self::DEST_CIVA) {
                                foreach($cols as $groupe_cols) {
                                    foreach($groupe_cols as $col) {
                                        $xml[] = $col;    
                                    }
                                }
                            }
                        }
                    }

                    if ($this->destinataire == self::DEST_DOUANE) {
                        if ($object->getTotalCaveParticuliere()) {
                            $total['exploitant']['L5'] += $object->getTotalCaveParticuliere() * $dr->getRatioLies();  //Volume total avec lies
                            $total['exploitant']['L9'] += $object->getTotalCaveParticuliere() * $dr->getRatioLies();
                            $total['exploitant']['L10'] += $object->getTotalCaveParticuliere() * $dr->getRatioLies();
                        } 
                    }
                    uksort($total['exploitant'], 'exportDRXml::sortXML');

                    if ($colass) {
                        $total['colonneAss'] = $colass;
                    }
                    if (!in_array($appellation->getKey(), array('appellation_GRDCRU', 'appellation_VINTABLE'))) {
                        $xml[] = $total;
                    }
                }
            }
        }

        $this->content = $this->getPartial('export/xml', array('dr' => $dr, 'xml' => $xml));
    }
}
