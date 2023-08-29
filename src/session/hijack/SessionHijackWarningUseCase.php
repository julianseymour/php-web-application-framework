<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\hijack;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;

class SessionHijackWarningUseCase extends SubsequentUseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return request()->getRequestURISegment(0);
	}

	private static function getSessionHijackWarningElement(){
		$f = __METHOD__;
		$ahsd = new AntiHijackSessionData();
		if ($ahsd->getIpAddressChanged() && $ahsd->getUserAgentChanged()) {
			$substitution = _("IP address and user agent string");
		} elseif ($ahsd->getIpAddressChanged()) {
			$substitution = _("IP address");
		} elseif ($ahsd->getUserAgentChanged()) {
			$substitution = _("User agent string");
		} else {
			Debug::error("{$f} none of the above");
		}
		$innerHTML = substitute(_("This session has been terminated because it has been accessed by a device with an incorrect %1%, and you configured your account settings to do so under these circumstances."), $substitution);
		return Document::createElement("div")->withInnerHTML($innerHTML);
	}

	public function getResponder(int $status): ?Responder{
		return new SessionHijackWarningResponder();
	}

	public function getPageContent(): ?array{
		return [
			static::getSessionHijackWarningElement()
		];
	}
}
