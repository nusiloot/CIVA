<?php
class ldap {

    protected $ldapserveur;
    protected $ldapdn;
    protected $ldapdc;
    protected $ldappass;

    public function __construct(){
             $this->ldapserveur = sfConfig::get('app_ldap_serveur');
             $this->ldapdn = sfConfig::get('app_ldap_dn');
             $this->ldapdc = sfConfig::get('app_ldap_dc');
             $this->ldappass = sfConfig::get('app_ldap_pass');
    }

    public function ldapConnect() {

        //Connexion au serveur LDAP
        $ldapconn = ldap_connect($this->ldapserveur);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($ldapconn) {
            //Connexion au serveur LDAP
            $ldapbind = ldap_bind($ldapconn, $this->ldapdn, $this->ldappass);

            // Identification
            if ($ldapbind)
                return $ldapconn;
            else
                return false;

        }else
            return false;
    }

    private function getLdapInfo($tiers) {
      $info = array();
      $info['uid']           = $tiers->cvi;
      $info['sn']            = $tiers->nom;
      $info['cn']            = $tiers->nom;
      $info['objectClass'][0]   = 'top';
      $info['objectClass'][1]   = 'person';
      $info['objectClass'][2]   = 'posixAccount';
      $info['objectClass'][3]   = 'inetOrgPerson';
      $info['userPassword']  = $tiers->mot_de_passe;
      $info['loginShell']    = '/bin/bash';
      $info['uidNumber']     = '1000';
      $info['gidNumber']     = '1000';
      $info['homeDirectory'] = '/home/'.$tiers->cvi;
      $info['gecos']         = $tiers->cvi.','.$tiers->no_accises.','.$tiers->intitule.' '.$tiers->nom.','.$tiers->exploitant->nom;
      $info['mail']          = $tiers->email;
      $info['postalAddress'] = $tiers->getAdresse();
      $info['postalCode']    = $tiers->getCodePostal();
      $info['l']             = $tiers->getCommune();
      return $info;
    }

    public function ldapAdd($tiers) {
        if($tiers){
            $ldapConnect = $this->ldapConnect();
            if($ldapConnect) {
                // prepare les données
                $identifier            = 'uid='.$tiers->cvi.',ou=Declarant,ou=People,'.$this->ldapdc;
		$info = $this->getLdapInfo($tiers);
                // Ajoute les données au dossier
                $r=ldap_add($ldapConnect, $identifier, $info);
                ldap_unbind($ldapConnect);
                return $r;
            }
        }
        return false;
    }
    
    public function ldapModify($tiers, $values = null) {

        if($tiers && $values){
            $ldapConnect = $this->ldapConnect();
            if($ldapConnect) {

                // prepare les données
                $identifier            = 'uid='.$tiers->cvi.',ou=Declarant,ou=People,'.$this->ldapdc;
		$info = $this->getLdapInfo($tiers);
                // Ajoute les données au dossier
                $r=ldap_modify($ldapConnect, $identifier, $info);
                ldap_unbind($ldapConnect);
                return $r;
            }

        }
        return false;
    }

    public function ldapDelete($tiers) {
        $ldapConnect = $this->ldapConnect();
        
        if($ldapConnect && $tiers) {
            $identifier  = 'uid='.$tiers->cvi.',ou=Declarant,ou=People,'.$this->ldapdc;
            $delete      = ldap_delete($ldapConnect, $identifier);
            ldap_unbind($ldapConnect);
            return $delete;
        }else{
            return false;
        }
    }

    public function ldapVerifieExistence($tiers){
        $ldapConnect = $this->ldapConnect();
        if($ldapConnect && $tiers) {
            $filter = 'uid='.$tiers->cvi;
            $search = ldap_search($ldapConnect, 'ou=Declarant,ou=People,'.$this->ldapdc, $filter);
            if($search){
                $count = ldap_count_entries($ldapConnect, $search);
                if($count>0)
                    return true;
            }
            return false;
            
        }
    }

}

?>
