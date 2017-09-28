<?php

use MediaWiki\Session\SessionProvider;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;

class ShibbolethSessionProvider extends SessionProvider {
	public function provideSessionInfo(WebRequest $request) {
		$shib = new bKULshib();
		if ($shib->check_login()) {
			$kulid = ucwords($shib->kulid());
			$user = User::newFromName($kulid);

			if (!$user->getId()) {
				$user = User::createNew($kulid, [
                                	"email" => $shib->single_email(),
                                	"real_name" => $shib->fullname(),
					"email_authenticated" => wfTimestamp(TS_MW) + 100
                        	]);
				$user->addGroup("Shibboleth");
			}

			return new SessionInfo(SessionInfo::MAX_PRIORITY, [
				'provider' => $this,
				'id' => $this->hashToSessionId(join( "\n", [
                                	        $shib->single_email(),
                                        	$shib->fullname(),
                                        	wfWikiID()
                                	])),
				'userInfo' => UserInfo::newFromUser($user, true),
				'forceUse' => true
			]);
		} else {
			return null;
		}
	}

	public function persistsSessionId() {
		return false;
	}

	public function canChangeUser() {
		return true;
	}

	public function persistSession(SessionBackend $session, WebRequest $request) {
	}

	public function unpersistSession(WebRequest $request) {
	}
}
