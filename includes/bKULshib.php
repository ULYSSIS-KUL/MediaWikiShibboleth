<?php

class bKULshib {
	protected $shib = FALSE;
	protected $debug = FALSE;
	protected $ldap_user = FALSE;
	protected $ldap_password = FALSE;
	protected $headers = array();
	protected $shib_names = array(
	'appid' => array('Shib-Application-ID'),
	'givenname' => array('Shib-Person-givenName', 'givenName'),
	'surname' => array('Shib-Person-surname', 'sn'),
	'commonname' => array('Shib-Person-commonName', 'cn'),
	'mail' => array('Shib-Person-mail', 'mail', 'KULPreferredMail'),
	'uid' => array('Shib-Person-uid', 'uid'),
	'migrateuid' => array('KULAssocMigrateID', 'eppn', 'Shib-EP-PrincipalName', 'eduPersonPrincipalName'),
	'uuid' => array('KULMoreUnifiedUID'),
	'opl' => array('Shib-KUL-opl', 'KULopl'),
	'dipl' => array('Shib-KUL-dipl', 'KULdipl'),
	'affiliation' => array('Shib-EP-ScopedAffiliation', 'eduPersonScopedAffiliation', 'affiliation'),
	'ou' => array('Shib-EP-OrgUnitDN', 'eduPersonOrgUnitDN', 'orgunit-dn')
	);
	protected $mail_prioselect = array('kuleuven.be','kuleuven-kulak.be','groept.be','khleuven.be','hubkaho.be','khlim.be','luca-arts.be','thomasmore.be','vives.be');
	protected $campus_affil = array('KU Leuven' => 'kuleuven.be', 'Groep T' => 'groept.be','KHLeuven' => 'khleuven.be', 'HUB-KAHO' => 'hubkaho.be', 'KHLim' => 'khlim.be', 'LUCA - School of Arts' => 'luca-arts.be', 'Thomas More' => 'thomasmore.be', 'VIVES' => 'vives.be');
	function __construct() {
		foreach($_SERVER as $key => $value) {
			$this->headers[$this->clean_header($key)] = $value;
		}
		foreach($this->shib_names['appid'] as $appid) {
			if(isset($this->headers[$appid])) {
				$this->shib = TRUE;
				break;
			}
		}
	}
	
	
	//debugging zorgt ervoor dat er in bepaalde gevallen naast FALSE ook een PHP E_WARNING op een vraag volgt
	function enable_debug() {
		$this->debug = TRUE;
	}
	
	function enable_testmode() {
		$_SERVER['Shib-Person-givenName'] = 'Tester';
		$_SERVER['Shib-Person-surname'] = 'Persoons';
		$_SERVER['Shib-Person-commonName'] = 'Tester Persoons';
		$_SERVER['Shib-Person-mail'] = 'tester.persoons@student.kuleuven.be';
		$_SERVER['Shib-Person-uid'] = 'r9999999';
		$_SERVER['KULMoreUnifiedUID'] = 'q9999999';
		$_SERVER['Shib-KUL-opl'] = '2015 50441668;2015 51016753';
		$_SERVER['Shib-KUL-dipl'] = '50074166';
		$_SERVER['Shib-EP-ScopedAffiliation'] = 'student@kuleuven.be;member@kuleuven.be';
		$_SERVER['Shib-EP-OrgUnitDN'] = 'KULouNumber=50000050,ou=unit,dc=kuleuven,dc=be;KULouNumber=50000405,ou=unit,dc=kuleuven,dc=be;KULouNumber=50000275,ou=unit,dc=kuleuven,dc=be';
		$this->shib = TRUE;
		foreach($_SERVER as $key => $value) {
			$this->headers[$this->clean_header($key)] = $value;
		}
	}
	
	function check_login() {
		if($this->shib) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	function login_link($returnlink = NULL, $force_ssl = TRUE) {
		//Voorbeeld loginlink: https://bert.ulyssis.be/Shibboleth.sso/WAYF/kuleuven?target=https%3A%2F%2Fbert.ulyssis.be%2Fshib%2Ftest.php
		
		if( ((isset($this->headers['HTTPS'])) AND ($this->headers['HTTPS'] == "on")) OR ($force_ssl) ) {
			$return = "https://" . $_SERVER['HTTP_HOST'] . "/Shibboleth.sso/WAYF/kuleuven?target=";
		}
		else {
			$return = "http://" . $_SERVER['HTTP_HOST'] . "/Shibboleth.sso/WAYF/kuleuven?target=";
		}
		
		if((strpos($returnlink, "http") === 0)) {
			return $return . urlencode($returnlink);
		}
		else {
			if( ((isset($this->headers['HTTPS'])) AND ($this->headers['HTTPS'] == "on")) OR ($force_ssl) ) {
				$return .= urlencode("https://") . urlencode($_SERVER['HTTP_HOST']);
			}
			else {
				$return .= urlencode("http://") . urlencode($_SERVER['HTTP_HOST']);
			}
			
			if($returnlink == NULL) {
				return $return . urlencode($_SERVER['REQUEST_URI']);
			}
			elseif(strpos($returnlink, "/") === 0) {
				return $return . urlencode($returnlink);
			}
			else {
				return $return . "/" . urlencode($returnlink);
			}
		}
	}
	
	function logout_link($displaylink = NULL, $force_ssl = TRUE) {
		//Voorbeeld logoutlink, volwaardige logout bij IdP, geen redirect: https://bert.ulyssis.be/Shibboleth.sso/Logout?return=https%3A%2F%2Fidp.kuleuven.be%2Fidp%2Flogout%3Freturn%3Dhttps%3A%2F%2Fbert.ulyssis.be%2Findex.php
		
		if( ((isset($this->headers['HTTPS'])) AND ($this->headers['HTTPS'] == "on")) OR ($force_ssl) ) {
			$return = "https://" . $_SERVER['HTTP_HOST'] . "/Shibboleth.sso/Logout?return=https%3A%2F%2Fidp.kuleuven.be%2Fidp%2Flogout%3Freturn%3D";
		}
		else {
			$return = "http://" . $_SERVER['HTTP_HOST'] . "/Shibboleth.sso/Logout?return=https%3A%2F%2Fidp.kuleuven.be%2Fidp%2Flogout%3Freturn%3D";
		}
		
		if((strpos($displaylink, "http") === 0)) {
			return $return . urlencode($displaylink);
		}
		else {
			if( ((isset($this->headers['HTTPS'])) AND ($this->headers['HTTPS'] == "on")) OR ($force_ssl) ) {
				$return .= urlencode("https://") . urlencode($_SERVER['HTTP_HOST']);
			}
			else {
				$return .= urlencode("http://") . urlencode($_SERVER['HTTP_HOST']);
			}
			
			if($displaylink == NULL) {
				return $return . urlencode($_SERVER['REQUEST_URI']);
			}
			elseif(strpos($displaylink, "/") === 0) {
				return $return . urlencode($displaylink);
			}
			else {
				return $return . "/" . urlencode($displaylink);
			}
		}
	}
	
	function set_kul_ldap_user($kuluser) {
		$this->ldap_user = $kuluser;
	}
	
	function set_kul_ldap_password($kulpassword) {
		$this->ldap_password = $kulpassword;
	}
	
	function kulid() {
		if($this->shib) {
			foreach($this->shib_names['uid'] as $uid) {
				if(isset($this->headers[$uid])) {
					return $this->headers[$uid];
					break;
				}
			}
			foreach($this->shib_names['migrateuid'] as $migrateuid) {
				if(isset($this->headers[$migrateuid])) {
					$split = explode("@", $this->headers[$migrateuid]);
					return $split[0];
					break;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;uid&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function SAPid() {
		if($this->shib) {
			foreach($this->shib_names['uuid'] as $uuid) {
				if(isset($this->headers[$uuid])) {
					return $this->headers[$uuid];
					break;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;KULMoreUnifiedUID&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function firstname() {
		if($this->shib) {
			foreach($this->shib_names['givenname'] as $givenname) {
				if(isset($this->headers[$givenname])) {
					return $this->headers[$givenname];
					break;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;givenName&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function lastname() {
		if($this->shib) {
			foreach($this->shib_names['surname'] as $surname) {
				if(isset($this->headers[$surname])) {
					return $this->headers[$surname];
					break;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;surName&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function fullname() {
		if($this->shib) {
			foreach($this->shib_names['commonname'] as $commonname) {
				if(isset($this->headers[$commonname])) {
					return $this->headers[$commonname];
					break;
				}
			}
			if( $this->firstname() && $this->lastname() ) {
				return $this->firstname() . " " . $this->lastname();
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;fullName&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function emails() {
		if($this->shib) {
			foreach($this->shib_names['mail'] as $mail) {
				if(isset($this->headers[$mail])) {
					return explode(";", $this->headers[$mail]);
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;mail&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function single_email() {
		if($this->shib) {
			foreach($this->emails() as $singlemail) {
				foreach($this->mail_prioselect as $tail) {
					if(is_int(strpos($singlemail, $tail))) {
						return $singlemail;
					}
				}
			}
			if($this->emails()) {
				$emails = $this->emails();
				return $emails[0];
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;mail&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function ldapfail() {
		if($this->debug) {
			trigger_error("bKULshib: De verbinding met de KULldapserver kon niet worden gemaakt. Controleer de ldap bindgegevens en de toegang tot ldap1.kuleuven.be.", E_USER_WARNING);
		}
		return FALSE;
	}
	
	function opl() {
		if($this->shib) {
			if($this->oplID()) { 
				$this->resultarray = array();
				$this->i = 0;
				foreach($this->oplID() as $this->did) {
					$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
					ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
					$this->ldap_filter = "(oplnr=" . $this->did . ")";
					$this->ldap_search = ldap_search($this->ldap_connection, "ou=opleiding,dc=kuleuven,dc=be", $this->ldap_filter, array("oplnaam"));
					$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_search);
					$this->ldaparray = $this->ldap_result;
					$this->resultarray[$this->i] = $this->ldaparray[0]['oplnaam'][0];
					$this->i++;
				}
				return $this->resultarray;
			}
			else {
				if($this->debug) {
					trigger_error("bKULshib: De shibboleth attribuut &quot;KULopl&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
				}
				return FALSE;
			}
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function trans_opl($oplID) {
		$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
		ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
		$this->ldap_filter = "(oplnr=" . $oplID . ")";
		$this->ldap_search = ldap_search($this->ldap_connection, "ou=opleiding,dc=kuleuven,dc=be", $this->ldap_filter, array("oplnaam"));
		$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_search);
		$this->ldaparray = $this->ldap_result;
		return $this->ldaparray[0]['oplnaam'][0];
	}
	
	function oplID() {
		if($this->shib) {
			foreach($this->shib_names['opl'] as $opl) {
				if(isset($this->headers[$opl])) {
					$this->semicolonsplits = explode(";", $this->headers[$opl]);
					$this->i = 0;
					foreach($this->semicolonsplits as $this->semicolonsplit)  {
						if(strlen($this->semicolonsplit) > 0) {
							$this->spacesplits = explode(" ", $this->semicolonsplit);
							foreach($this->spacesplits as $this->spacesplit) {
								if(strlen($this->spacesplit) == 8) {
									$this->oplID[$this->i] = $this->spacesplit;
									$this->i++;
								}
							}
						}
					}
					return $this->oplID;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;KULopl&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function dipl() {
		if($this->shib) {
			if($this->diplID()) { 
				$this->resultarray = array();
				$this->i = 0;
				foreach($this->diplID() as $this->did) {
					$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
					ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
					$this->ldap_filter = "(dipl=" . $this->did . ")";
					$this->ldap_search = ldap_search($this->ldap_connection, "ou=diploma,dc=kuleuven,dc=be", $this->ldap_filter, array("diplnaam"));
					$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_search);
					$this->ldaparray = $this->ldap_result;
					$this->resultarray[$this->i] = $this->ldaparray[0]['diplnaam'][0];
					$this->i++;
				}
				return $this->resultarray;
			}
			else {
				if($this->debug) {
					trigger_error("bKULshib: De shibboleth attribuut &quot;KULdipl&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
				}
				return FALSE;
			}
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function trans_dipl($diplID) {
		$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
		ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
		$this->ldap_filter = "(dipl=" . $diplID . ")";
		$this->ldap_search = ldap_search($this->ldap_connection, "ou=diploma,dc=kuleuven,dc=be", $this->ldap_filter, array("diplnaam"));
		$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_search);
		$this->ldaparray = $this->ldap_result;
		return $this->ldaparray[0]['diplnaam'][0];
	}
	
	function diplID() {
		if($this->shib) {
			foreach($this->shib_names['dipl'] as $dipl) {
				if(isset($this->headers[$dipl])) {
					$this->semicolonsplits = explode(";", $this->headers[$dipl]);
					$this->i = 0;
					foreach($this->semicolonsplits as $this->semicolonsplit)  {
						if(strlen($this->semicolonsplit) > 0) {
							$this->diplID[$this->i] = $this->semicolonsplit;
							$this->i++;
						}
					}
					return $this->diplID;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;KULdipl&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function is_student() {
		if($this->shib) {
			foreach($this->shib_names['affiliation'] as $affiliation) {
				if(isset($this->headers[$affiliation])) {
					if(is_int(strpos($this->headers[$affiliation], 'student'))) {
						return TRUE;
					}
					return FALSE;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;Shib-EP-ScopedAffiliation&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function is_employee() {
		if($this->shib) {
			foreach($this->shib_names['affiliation'] as $affiliation) {
				if(isset($this->headers[$affiliation])) {
					if(is_int(strpos($this->headers[$affiliation], 'employee'))) {
						return TRUE;
					}
					return FALSE;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;Shib-EP-ScopedAffiliation&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function campus() {
		if($this->shib) {
			$return = FALSE;
			foreach($this->shib_names['affiliation'] as $affiliation) {
				if(isset($this->headers[$affiliation])) {
					foreach($this->campus_affil as $campusname => $tail) {
						if(is_int(strpos($this->headers[$affiliation], $tail))) {
							$return[] = $campusname;
						}
					}
					return $return;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;Shib-EP-ScopedAffiliation&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function group() {
		if($this->shib) {
			if($this->groupDN()) { 
				$this->resultarray = array();
				$this->i = 0;
				foreach($this->groupDN() as $this->gdn) {
					$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
					ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
					$this->ldap_read = ldap_read($this->ldap_connection, $this->gdn, "(objectClass=*)");
					$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_read);
					$this->ldaparray = $this->ldap_result;
					$this->resultarray[$this->i] = $this->ldaparray[0]['ou'][0];
					$this->i++;
				}
				return $this->resultarray;
			}
			else {
				if($this->debug) {
					trigger_error("bKULshib: De shibboleth attribuut &quot;EP-OrgUnitDN&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
				}
				return FALSE;
			}
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	function trans_group($gdn) {
		$this->ldap_connection = ldap_connect("ldaps://ldap.kuleuven.be:636") or $this->ldapfail();
		ldap_bind($this->ldap_connection, $this->ldap_user, $this->ldap_password) or $this->ldapfail();
		$this->ldap_read = ldap_read($this->ldap_connection, $this->gdn, "(objectClass=*)");
		$this->ldap_result = ldap_get_entries($this->ldap_connection, $this->ldap_read);
		$this->ldaparray = $this->ldap_result;
		return $this->ldaparray[0]['ou'][0];
	}
	
	function groupDN() {
		if($this->shib) {
			foreach($this->shib_names['ou'] as $ou) {
				if(isset($this->headers[$ou])) {
					$this->semicolonsplits = explode(";", $this->headers[$ou]);
					$this->i = 0;
					foreach($this->semicolonsplits as $this->semicolonsplit)  {
						if(strlen($this->semicolonsplit) > 0) {
							$this->groupDN[$this->i] = $this->semicolonsplit;
							$this->i++;
						}
					}
					return $this->groupDN;
				}
			}
			if($this->debug) {
				trigger_error("bKULshib: De shibboleth attribuut &quot;EP-OrgUnitDN&quot; werd opgevraagd die niet toegankelijk is, is deze attribuut aangevraagd bij ICTS?", E_USER_WARNING);
			}
			return FALSE;
		}
		else {
			if($this->debug) {
				trigger_error("bKULshib: Een shibboleth attribuut werd opgevraagd terwijl de gebruiker helemaal niet is ingelogd, gebruik check_login voordat je attributen vraagt!", E_USER_WARNING);
			}
			return FALSE;
		}
	}
	
	protected function clean_header($header) {
		$header = str_replace('REDIRECT_', '', $header);
		$header = str_replace('_', '-', $header);
		return $header;
	}
}

?>

