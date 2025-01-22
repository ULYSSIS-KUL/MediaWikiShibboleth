<?php

namespace MediaWikiShibboleth;

use WebRequest;
use User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use MediaWiki\Session\SessionProvider;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use MediaWiki\Session\SessionBackend;
use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use MediaWiki\MediaWikiServices;

class ShibbolethSessionProvider extends SessionProvider {
	public function provideSessionInfo(WebRequest $request) {
		$shib = new bKULshib();

		if ($shib->check_login()) {
			global $wgMWSStudentsOnly;
			if ($wgMWSStudentsOnly && (!$shib->is_student() || $shib->is_employee())) {
				return null;
			}

			$kulid = $shib->kulid();

			global $wgMWSAllowedKULids;
			// Only check the allowed KUL ids if the array is set and non-empty.
			if (!empty($wgMWSAllowedKULids)) {
				$found = FALSE;
				foreach (explode(',', $wgMWSAllowedKULids) as $KULid) {
					if (trim($KULid) === $kulid) {
						$found = TRUE;
						break;
					}
				}

				if (!$found) {
					return null;
				}
			}

			global $wgMWSAllowedDegrees;
			// Only check allowed degrees if the array is set and non-empty.
			if (!empty($wgMWSAllowedDegrees)) {
				$oplIDs = $shib->oplID();
				// Fail immediately if no the shib account has no degrees
				if (empty($oplIDs)) {
					return null;
				}
				$found = FALSE;
				foreach (explode(',', $wgMWSAllowedDegrees) as $degree) {
					if (in_array(trim($degree), $oplIDs)) {
						$found = TRUE;
						break;
					}
				}

				if (!$found) {
					return null;
				}
			}
			
			$services = MediaWikiServices::getInstance();
			$userFactory = $services->getUserFactory();
			
			$kulid = ucwords($kulid);
			$user = $userFactory->newFromName($kulid);

			if (!$user->getId()) {
				$user = User::createNew($kulid, [
//					'email' => $shib->single_email(),
					'real_name' => $shib->fullname(),
					'email_authenticated' => wfTimestamp(TS_MW) + 100
					]);
				$userGroupManager = $services->getUserGroupManager();
				$userGroupManager->addUserToGroup($user, "Shibboleth");
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
		return true;
	}

	public function persistSession(SessionBackend $session, WebRequest $request) {
	}

	public function unpersistSession(WebRequest $request) {
	}
}
