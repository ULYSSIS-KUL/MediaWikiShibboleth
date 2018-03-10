<?php

class SpecialShibbolethLogin extends SpecialUserLogin {
	function __construct() {
		parent::__construct("ShibbolethLogin");
	}

	function getPageHtml($formHtml) {
		$shib = new bKULshib();

		if ($shib->check_login()) {
			global $wgMWSStudentsOnly;
			if ($wgMWSStudentsOnly && (!$shib->is_student() || $shib->is_employee())) {
				return '<table cellspacing="0" cellpadding="0" border="0" style="background: transparent; margin-top:0.5em;border:1px #b32424 solid;padding:0.5em;background-color: #fee7e6"><tr><td><b>Only students are allowed to access this site!</b></td></tr></table></p><p>Click on image to log in:</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a><a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9654;\'}"><p id="spra">Password Login &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';
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
				return '<table cellspacing="0" cellpadding="0" border="0" style="background: transparent; margin-top:0.5em;border:1px #b32424 solid;padding:0.5em;background-color: #fee7e6"><tr><td><b>You are not allowed to access this site!</b></td></tr></table></p><p>Click on image to log in:</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a><a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9654;\'}"><p id="spra">Password Login &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';
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
				return '<table cellspacing="0" cellpadding="0" border="0" style="background: transparent; margin-top:0.5em;border:1px #b32424 solid;padding:0.5em;background-color: #fee7e6"><tr><td><b>You are not allowed to access this site!</b></td></tr></table></p><p>Click on image to log in:</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a><a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9654;\'}"><p id="spra">Password Login &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';
			}
		}

		return '<p>Click on image to log in:</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a><a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9654;\'}"><p id="spra">Password Login &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';
	}
}
