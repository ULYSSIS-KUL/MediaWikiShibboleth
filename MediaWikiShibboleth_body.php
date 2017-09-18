<?php
$wgRevokePermissions['Shibboleth']['editmyprivateinfo'] = true;
$wgGroupPermissions['*']['createaccount'] = false;


class MediaWikiShibboleth {
	public static function onPersonalUrls(array &$personal_urls, Title $title, SkinTemplate $skin) {
		unset($personal_urls['createaccount']);
	}

	public static function onSpecialPage_initList(&$specialPages) {
		$specialPages['Userlogin'] = "SpecialShibbolethLogin";
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
