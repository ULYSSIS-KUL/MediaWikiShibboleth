<?php

namespace MediaWikiShibboleth;

class SpecialShibbolethLogin extends SpecialUserLogin {
	function __construct() {
		parent::__construct("ShibbolethLogin");
	}

	function errorBox($message) {
		$errorMessage = wfMessage($message)->parse();
		return '<table cellspacing="0" cellpadding="0" border="0" style="background: transparent; margin-top:0.5em;border:1px #b32424 solid;padding:0.5em;background-color: #fee7e6"><tr><td><b>' . $errorMessage . '</b></td></tr></table></p>';
	}

	function image($shib) {
		$clickMessage = wfMessage('mediawikishibboleth-login')->parse();
		return '<p>' . $clickMessage . '</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a>';
	}

	function password_login($formHtml) {
		$passwordLogin = wfMessage('mediawikishibboleth-password-login')->parse();
		return '<a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'' . $passwordLogin . ' &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'' . $passwordLogin . ' &#9654;\'}"><p id="spra">' . $passwordLogin . ' &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';
	}

	function getPageHtml($formHtml) {
		$shib = new bKULshib();
		if ($shib->check_login()) {
			global $wgMWSStudentsOnly;
			if ($wgMWSStudentsOnly && (!$shib->is_student() || $shib->is_employee())) {
				return $this->errorBox('mediawikishibboleth-students-only') . $this->password_login($formHtml);
			}

			global $wgMWSAllowedKULids;
			$found = !$wgMWSAllowedKULids;
			foreach (explode(',', $wgMWSAllowedKULids) as $KULid) {
				if (trim($KULid) === $shib->kulid()) {
					$found = TRUE;
					break;
				}
			}

			if (!$found) {
				return $this->errorBox('mediawikishibboleth-forbidden-student') . $this->password_login($formHtml);
			}

			global $wgMWSAllowedDegrees;
			$found = !$wgMWSAllowedDegrees;
			$oplID = $shib->oplID();
			foreach (explode(',', $wgMWSAllowedDegrees) as $degree) {
				if (in_array(trim($degree), $oplID)) {
					$found = TRUE;
					break;
				}
			}

			if (!$found) {
				return $this->errorBox('mediawikishibboleth-forbidden-degree') . $this->password_login($formHtml);
			}

			$this->successfulAction();
		}

		return $this->image($shib) . $this->password_login($formHtml);
	}
}
