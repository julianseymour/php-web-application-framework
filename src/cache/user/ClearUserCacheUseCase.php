<?php
namespace JulianSeymour\PHPWebApplicationFramework\cache\user;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class ClearUserCacheUseCase extends UseCase{

	public function execute(): int{
		$f = __METHOD__;
		$print = false;
		$directive = directive();
		if ($directive !== DIRECTIVE_DELETE) {
			if ($print) {
				Debug::print("{$f} not clearing cache today");
			}
			return SUCCESS;
		} elseif ($print) {
			Debug::print("{$f} cleared user cache");
		}
		$user = user();
		$user_key = $user->getIdentifierValue();
		if(cache()->enabled() && USER_CACHE_ENABLED) {
			if ($print) {
				Debug::print("{$f} user cache is enabled");
			}
			if (cache()->hasAPCu($user_key)) {
				if ($print) {
					Debug::print("{$f} deleting cache for user with key \"{$user_key}\"");
				}
				cache()->delete($user_key);
			} elseif ($print) {
				Debug::print("{$f} user key \"{$user_key}\" is not present in APCu cache");
			}
		} elseif ($print) {
			Debug::print("{$f} user cache is not enabled");
		}
		return SUCCESS;
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/user_cache";
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}

	public function getResponder(int $status): ?Responder{
		if($status !== SUCCESS){
			return parent::getResponder($status);
		}elseif (directive() === DIRECTIVE_DELETE) {
			return new Responder();
		}
		return parent::getResponder($status);
	}

	public function getPageContent(): ?array{
		if(!user() instanceof Administrator){
			return [
				ErrorMessage::getVisualError(ERROR_EMPLOYEES_ONLY)
			];
		}
		return [
			new ClearUserCacheForm(ALLOCATION_MODE_LAZY)
		];
	}
}
