<?php

namespace MediaWikiShibboleth;

use SpecialUserLogin;
use MediaWiki\MediaWikiServices;

class SpecialShibbolethLogin extends SpecialUserLogin {
	function __construct() {
		parent::__construct(MediaWikiServices::getInstance()->getAuthManager());
	}

	function errorBox($message) {
		return '<table cellspacing="0" cellpadding="0" border="0" style="background: transparent; margin-top:0.5em;border:1px #b32424 solid;padding:0.5em;background-color: #fee7e6"><tr><td><b>' . $message . '</b></td></tr></table></p>';
	}

	function image($shib) {
		$login_link = $shib->login_link();
		$clickMessage = '<a href="' . $login_link . '">' . wfMessage('mediawikishibboleth-login')->parse() . '</a>';
		return '<p>' . $clickMessage . '</p><a href="' . $login_link . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a>';
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
				return $this->errorBox(wfMessage('mediawikishibboleth-students-only')->parse()) . $this->password_login($formHtml);
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
				return $this->errorBox(wfMessage('mediawikishibboleth-forbidden-student', $shib->kulid())->parse()) . $this->password_login($formHtml);
			}

			global $wgMWSAllowedDegrees;
			$found = !$wgMWSAllowedDegrees;
			$oplID = $shib->oplID();
			if(!empty($oplID)) {
				foreach (explode(',', $wgMWSAllowedDegrees) as $degree) {
					if (in_array(trim($degree), $oplID)) {
						$found = TRUE;
						break;
					}
				}
			}

			if (!$found) {
				if (empty($oplID)) {
					return $this->errorBox(wfMessage('mediawikishibboleth-forbidden-nodegree')->parse()) . $this->password_login($formHtml);
				}
				else {
					return $this->errorBox(wfMessage('mediawikishibboleth-forbidden-degree', count($oplID), implode(', ', $oplID))->parse()) . $this->password_login($formHtml);
				}
			}

			$this->successfulAction();
		}

		return $this->image($shib) . '<hr>' . $this->password_login($formHtml);
	}
}
