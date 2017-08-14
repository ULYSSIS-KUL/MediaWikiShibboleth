<?php

$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['createtalk'] = false;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['writeapi'] = false;
$wgRevokePermissions['Shibboleth']['editmyprivateinfo'] = true;

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
