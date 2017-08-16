<?php

class SpecialShibbolethLogin extends SpecialUserLogin {
	function __construct() {
		parent::__construct("ShibbolethLogin");
	}

	function getPageHtml($formHtml) {
		$shib = new bKULshib();
		$formHtml = '<p>Click on image to log in:</p><a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Centrale KU Leuven Login" align="middle"></a><a href="#" onclick="if(document.getElementById(\'spr\').style.display==\'none\'){document.getElementById(\'spr\').style.display=\'\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9660;\'}else{document.getElementById(\'spr\').style.display=\'none\';document.getElementById(\'spra\').innerHTML=\'Password Login &#9654;\'}"><p id="spra">Password Login &#9654;</p></a><div id="spr" style="display:none">' . $formHtml . '</div>';

		return $formHtml;
	}
}
