<?php

use MediaWiki\Session\SessionProvider;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;

class ShibbolethSessionProvider extends ImmutableSessionProviderWithCookie {
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

			if ($this->sessionCookieName === null) {
				$id = $this->hashToSessionId(join( "\n", [
					$shib->single_email(),
					$shib->fullname(),
					wfWikiID()
				]));
				$persisted = false;
				$forceUse = true;
			} else {
				$id = $this->getSessionIdFromCookie($request);
				$persisted = $id !== null;
				$forceUse = false;
			}

			return new SessionInfo(SessionInfo::MAX_PRIORITY, [
				'provider' => $this,
				'id' => $id,
				'userInfo' => UserInfo::newFromUser($user, true),
				'persisted' => $persisted,
				'forceUse' => $forceUse
			]);
		} else {
			return null;
		}
	}

	public function canChangeUser() {
		return true;
	}
}
