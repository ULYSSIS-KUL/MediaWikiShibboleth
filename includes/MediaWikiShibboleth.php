<?php

namespace MediaWikiShibboleth;

use Title;
use SkinTemplate;

class MediaWikiShibboleth {
	public static function onSkinTemplateNavigationUniversal($skinTemplate, &$links) {
		unset($links['user-menu']['createaccount']);
	}

	public static function onSpecialPage_initList(&$specialPages) {
		$specialPages['Userlogin'] = "MediaWikiShibboleth\\SpecialShibbolethLogin";
		return true;
	}

	public static function onUserLogout(&$user) {
		global $wgOut;

		$shib = new bKULshib();
		if ($shib->check_login()) {
			$wgOut->redirect($shib->logout_link());
		}
		return true;
	}
}

