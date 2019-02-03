<?php

namespace MediaWikiShibboleth;

use MediaWiki\Session\SessionProvider;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;

class ShibbolethSessionProvider extends SessionProvider {
	public function provideSessionInfo(WebRequest $request) {
		$shib = new bKULshib();

		if ($shib->check_login()) {
			global $wgMWSStudentsOnly;
			if ($wgMWSStudentsOnly && (!$shib->is_student() || $shib->is_employee())) {
				return null;
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
				return null;
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
				return null;
			}

			$kulid = ucwords($shib->kulid());
			$user = User::newFromName($kulid);

			if (!$user->getId()) {
				$user = User::createNew($kulid, [
// TODO: Prevent password resets
					"email" => $shib->single_email(),
					"real_name" => $shib->fullname(),
					"email_authenticated" => wfTimestamp(TS_MW) + 100
					]);
				$user->addGroup("Shibboleth");
			}

			return new SessionInfo(SessionInfo::MAX_PRIORITY, [
				'provider' => $this,
				'id' => $this->hashToSessionId($shib->shib_session_id()),
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
		return false;
	}

	public function persistSession(SessionBackend $session, WebRequest $request) {
	}

	public function unpersistSession(WebRequest $request) {
	}
}
