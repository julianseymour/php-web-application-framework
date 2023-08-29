<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\nonexistent_uris;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\AccountFirewallResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use Exception;
use mysqli;

class NonexistentUrisUseCase extends InteractiveUseCase{
	
	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return null;
	}

	public function getConditionalElementClasses(): ?array{
		return [
			DATATYPE_NONEXISTENT_URI => NonexistentUriForm::class
		];
	}

	public function getActionAttribute(): ?string{
		return "/nonexistent_uris";
	}

	public function getDataOperandClass(): ?string{
		return NonexistentUriData::class;
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			DATATYPE_NONEXISTENT_URI => NonexistentUriData::class
		];
	}

	public function getConditionalProcessedFormClasses(): ?array{
		return [NonexistentUriForm::class];
	}

	protected function getExecutePermissionClass(){
		return AdminOnlyAccountTypePermission::class;
	}

	public function getProcessedDataType(): ?string{
		return DATATYPE_NONEXISTENT_URI;
	}

	public function isCurrentUserDataOperand(): bool{
		return false;
	}
	
	public function getPageContent(): array{
		$f = __METHOD__;
		try {
			$print = false;
			if($print){
				Debug::print("{$f} entered");
			}
			$status = $this->getObjectStatus();
			if ($status === ERROR_MUST_LOGIN) {
				if($print){
					Debug::warning("{$f} not logged in");
				}
				return parent::getPageContent();
			}
			$ret = [];
			$user = user();
			if (! isset($user)) {
				Debug::error("{$f} user data returned null");
			} elseif ($status !== SUCCESS) {
				array_push($ret, ErrorMessage::getVisualError($status));
			}
			$mode = ALLOCATION_MODE_LAZY;
			array_push($ret, new NonexistentUriListElement($mode, $user));
			if($print){
				Debug::print("{$f} returning normally");
			}
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
	
	public function getResponder(int $status):?Responder{
		$directive = directive();
		switch ($directive) {
			case DIRECTIVE_UPDATE:
				if ($status === SUCCESS) {
					$operand = $this->getDataOperandObject();
					$backup = $this->getOriginalOperand();
					if ($operand->getList() !== $backup->getList()) {
						if ($directive === DIRECTIVE_INSERT) {
							return new InsertAfterResponder();
						} elseif ($directive === DIRECTIVE_UPDATE) {
							return new AccountFirewallResponder();
						}
					}
				}
			default:
				return parent::getResponder($status);
		}
	}
	
	public function getInsertHereElement(?DataStructure $ds = null){
		$f = __METHOD__; //NonexistentUrisUseCase::getShortClass()."(".static::getShortClass().")->getInsertHereElement()";
		$list = $ds->getList();
		$div = new DivElement();
		switch ($list) {
			case POLICY_BLOCK:
				$div->setIdAttribute("blacklist_top");
				return $div;
			case POLICY_ALLOW:
				$div->setIdAttribute("whitelist_top");
				return $div;
			default:
				Debug::warning("{$f} invalid filter policy \"{$list}\"");
				return null;
		}
	}
	
	public function getLoadoutGeneratorClass(?PlayableUser $user=null):?string{
		$f = __METHOD__; //NonexistentUriData::getShortClass()."(".static::getShortClass().")->getLoadoutGeneratorClass()";
		$mysqli = db()->getConnection(PublicReadCredentials::class);
		if(!NonexistentUriData::tableExistsStatic($mysqli)){
			$create = NonexistentUriData::getCreateTableStatementStatic();
			Debug::print($create);
		}
		if(user() instanceof Administrator){
			return NonexistentUriLoadoutGenerator::class;
		}
		return null;
	}
}