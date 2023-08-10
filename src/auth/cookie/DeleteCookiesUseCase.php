<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\cookie;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\unset_cookie;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\account\logout\LogoutResponder;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class DeleteCookiesUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/delete_cookies";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function execute(): int{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		if ($directive !== DIRECTIVE_DELETE) {
			if ($print) {
				Debug::print("{$f} about to delete the following cookies:");
			}
			foreach ($_COOKIE as $key) {
				if ($print) {
					Debug::print("{$f} \"{$key}\"");
				}
				unset_cookie($key);
			}
		} elseif ($print) {
			Debug::print("{$f} not deleting cookies");
		}
		return SUCCESS;
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		if ($directive !== DIRECTIVE_DELETE) {
			if ($print) {
				Debug::print("{$f} nothing to delete");
			}
			$form = new DeleteCookiesForm(ALLOCATION_MODE_LAZY, user());
			return [
				$form
			];
		}
		if ($print) {
			Debug::print("{$f} cookies deleted");
		}
		$innerHTML = _("Cookies deleted");
		$div = new DivElement(ALLOCATION_MODE_LAZY);
		$div->setInnerHTML($innerHTML);
		return [
			$div
		];
	}

	public function getResponder(): ?Responder{
		$directive = directive();
		if ($directive === DIRECTIVE_DELETE && $this->getObjectStatus() === SUCCESS) {
			return new LogoutResponder();
		}
		return null;
	}
}
