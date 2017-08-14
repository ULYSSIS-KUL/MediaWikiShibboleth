<?php

class SpecialShibbolethLogin extends SpecialUserLogin {
	function __construct() {
		parent::__construct("ShibbolethLogin");
	}

	function execute($subPage) {
		$shib = new bKULshib();
		$html = '<a href="' . $shib->login_link() . '"><img src="extensions/MediaWikiShibboleth/shib.gif" alt="Central KU Leuven Login"></a><p><br></p>';
		$output = $this->getOutput();
		$output->addHTML($html);
		parent::execute($subPage);
	}
}
