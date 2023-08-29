<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\account\role\RoleBasedPermission;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\Workflow;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSaveEvent;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InsertAfterResponder;
use JulianSeymour\PHPWebApplicationFramework\use_case\interactive\InteractiveUseCase;
use Exception;
use mysqli;

class AccountFirewallUseCase extends InteractiveUseCase{

	public function getDataOperandClass(): ?string{
		// $f = __METHOD__;
		if (hasInputParameter("dataType", $this)) {
			$classes = $this->getConditionalDataOperandClasses();
			$dataType = getInputParameter('dataType', $this);
			if (array_key_exists($dataType, $classes)) {
				return $classes[$dataType];
			}
		}
		return ListedIpAddress::class;
	}

	public function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object): ?UserData{
		return $owned_object->setUserData(user());
	}

	/**
	 *
	 * @param ListedIpAddress $reloaded_object
	 * {@inheritdoc}
	 * @see InteractiveUseCase::getInsertHereElement()
	 */
	public function getInsertHereElement(?DataStructure $ds = null){
		$f = __METHOD__;
		$list = $ds->getList();
		$div = new DivElement();
		switch ($list) {
			case POLICY_BLOCK:
				$div->setIdAttribute("blacklist_top");
				return $div;
			case POLICY_ALLOW:
				$div->setIdAttribute("whitelist_top");
				return $div;
			case POLICY_NONE:
				$div->setIdAttribute("graylist_top");
				return $div;
			default:
				Debug::warning("{$f} invalid filter policy \"{$list}\"");
				return null;
		}
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		if($status !== SUCCESS){
			return parent::getResponder($status);
		}
		$directive = directive();
		switch ($directive) {
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
				if ($this->getObjectStatus() === SUCCESS) {
					$operand = $this->getDataOperandObject();
					$type = $operand->getDataType();
					if ($type !== DATATYPE_USER) {
						$backup = $this->getOriginalOperand();
						if ($operand->getList() !== $backup->getList()) {
							if ($directive === DIRECTIVE_INSERT) {
								return new InsertAfterResponder();
							} elseif ($directive === DIRECTIVE_UPDATE) {
								return new AccountFirewallResponder();
							}
						}
					}
				}
			default:
				return parent::getResponder($status);
		}
	}

	public function getPageContent(): array{
		$f = __METHOD__;
		try {
			Debug::print("{$f} entered");
			$status = $this->getObjectStatus();
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("{$f} error status \"{$err}\"");
			switch($status){
				case SUCCESS:
					break;
				case ERROR_MUST_LOGIN:
					Debug::warning("{$f} not logged in");
					return parent::getPageContent();
				case RESULT_LOGGED_OUT:
					Debug::warning("{$f} logged out");
					return parent::getPageContent();
				default:
			}
			$ret = [];
			$user = user();
			if (! isset($user)) {
				Debug::error("{$f} user data returned null");
			} elseif ($status !== SUCCESS) {
				array_push($ret, ErrorMessage::getVisualError($status));
			}
			$mode = ALLOCATION_MODE_LAZY;
			array_push($ret, new AccountFirewallElement($mode, $user));
			Debug::print("{$f} returning normally");
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	protected function getExecutePermissionClass(){
		return AuthenticatedAccountTypePermission::class;
	}

	public function getConditionalElementClasses(): ?array{
		return [
			DATATYPE_USER => FilterPolicyForm::class,
			DATATYPE_IP_ADDRESS => CidrIpAddressForm::class
		];
	}

	public function getConditionalDataOperandClasses(): ?array{
		return [
			DATATYPE_USER => config()->getNormalUserClass(),
			DATATYPE_IP_ADDRESS => ListedIpAddress::class
		];
	}

	public function getConditionalProcessedFormClasses(): ?array{
		return [
			FilterPolicyForm::class,
			CidrIpAddressForm::class
		];
	}

	public function getProcessedDataType(): ?string{
		if (hasInputParameter("dataType", $this)) {
			$type = getInputParameter('dataType', $this);
			switch ($type) {
				case DATATYPE_USER:
				case DATATYPE_IP_ADDRESS:
					return $type;
				default:
					break;
			}
		}
		return DATATYPE_IP_ADDRESS;
	}

	public function getProcessedDataListClasses(): ?array{
		return [
			ListedIpAddress::class
		];
	}

	public function reconfigureDataOperand(mysqli $mysqli, object &$object): int{
		$f = __METHOD__;
		$print = false;
		if ($object instanceof ListedIpAddress) {
			$object->disableNotifications();
			if ($print) {
				Debug::print("{$f} the operand is a listed IP address");
			}
			$directive = directive();
			switch ($directive) {
				case DIRECTIVE_INSERT:
				case DIRECTIVE_UPDATE:
					if ($print) {
						Debug::print("{$f} this is an {$directive}");
					}
					// validation closures to prevent locking yourself out
					$object->setValidationClosure(function (ListedIpAddress $target) use ($directive, $f, $print): int {
						$phylum = ListedIpAddress::getPhylumName();
						$user = $target->getUserData();
						if ($directive === DIRECTIVE_INSERT) {
							$user->setForeignDataStructureListMember($phylum, $target);
						}
						if($user->hasDebugId() && user()->hasDebugId() && $user->getDebugId() !== user()->getDebugId()){
							$decl = $user->getDeclarationLine();
							$decl2 = user()->getDeclarationLine();
							Debug::error("{$f} rogue user data was instantiated {$decl}	and actual user data was instantiated {$decl2}");
						}
						$listed_ip_addresses = $user->getForeignDataStructureList($phylum);
						uasort($listed_ip_addresses, function (ListedIpAddress $a, ListedIpAddress $b): int {
							$diff = $a->getMask() - $b->getMask();
							if ($diff === 0) {
								return 0;
							} elseif ($diff > 0) {
								return - 1;
							} else {
								return 1;
							}
						});
						$user->setForeignDataStructureList($phylum, $listed_ip_addresses);
						$r = $target->preventSelfLockout();
						if ($print) {
							$err = ErrorMessage::getResultMessage($r);
							Debug::print("{$f} returning \"{$err}\"");
						}
						return $r;
					});
					// event handlers for refreshing cached IP addresses for zero DB access firewall
					if (cache()->enabled() && USER_CACHE_ENABLED) {
						$object->addEventListener(EVENT_AFTER_SAVE, function (AfterSaveEvent $event, ListedIpAddress $target) use ($f, $print) {
							$target->removeEventListener($event);
							$object = $target;
							app()->getWorkflow()
								->addEventListener(EVENT_AFTER_RESPOND, function (AfterRespondEvent $event, Workflow $target) use ($object, $f, $print) {
								$target->removeEventListener($event);
								$user_key = $object->getIdentifierValue();
								$index = "ip_addresses_{$user_key}";
								if (cache()->hasAPCu($index)) {
									if ($print) {
										Debug::print("{$f} about to set new listed IP address in cache");
									}
									$object->configureArrayMembership('cache');
									$results[$object->getIdentifierValue()] = $object->toArray();
									uasort($results, function ($a, $b) use ($f, $print): int {
										if ($print) {
											Debug::print("{$f} array A:");
											Debug::printArray($a);
											Debug::print("{$f} array B:");
											Debug::printArray($b);
										}
										$diff = $a['mask'] - $b['mask'];
										if ($diff === 0) {
											if ($print) {
												Debug::print("{$f} masks are the same size");
											}
											return 0;
										} elseif ($diff < 0) {
											if ($print) {
												Debug::print("{$f} IP address B has a larger mask, returning 1");
											}
											return 1;
										} elseif ($diff > 0) {
											if ($print) {
												Debug::print("{$f} IP address B has a smaller mask, returning -1");
											}
											return - 1;
										} else {
											Debug::error("{$f} impossible");
										}
									});
									cache()->setAPCu($index, $results);
								} elseif ($print) {
									Debug::print("{$f} cache miss for \"{$index}\"");
								}
							});
						});
					} elseif ($print) {
						Debug::print("{$f} cache is disabled");
					}
					break;
				case DIRECTIVE_DELETE:
					if ($print) {
						Debug::print("{$f} this is a delete");
					}
					$object->setPermission(DIRECTIVE_DELETE, new RoleBasedPermission(DIRECTIVE_DELETE, function (AuthenticatedUser $user, ListedIpAddress $object) {
						$mysqli = db()->getConnection(PublicWriteCredentials::class);
						return $user->filterIpAddress($mysqli);
					}, [
						USER_ROLE_OWNER => POLICY_REQUIRE
					]));
					if (cache()->enabled() && USER_CACHE_ENABLED) {
						$object->addEventListener(EVENT_AFTER_DELETE, function (AfterDeleteEvent $event, ListedIpAddress $target) use ($f, $print) {
							$target->removeEventListener($event);
							$object = $target;
							app()->getWorkflow()
								->addEventListener(EVENT_AFTER_RESPOND, function (AfterRespondEvent $event, Workflow $target) use ($object, $f, $print) {
								$target->removeEventListener($event);
								$index = "ip_addresses_" . $object->getIdentifierValue();
								if (cache()->hasAPCu($index)) {
									if ($print) {
										Debug::print("{$f} about to delete listed IP address in cache");
									}
									$results = cache()->getAPCu($index);
									$results = array_remove_key($results, $object->getIdentifierValue());
									cache()->setAPCu($index, $results);
								} elseif ($print) {
									Debug::print("{$f} cache miss for \"{$index}\"");
								}
							});
						});
					} elseif ($print) {
						Debug::print("{$f} cache is disabled");
					}
					break;
				default:
					Debug::warning("{$f} unusual directive \"{$directive}\"");
			}
		} elseif ($object instanceof AuthenticatedUser) {
			//
		} else {
			Debug::error("{$f} what else gets processed by this use case?");
		}
		return SUCCESS;
	}

	public function getActionAttribute(): string{
		return "/account_firewall";
	}

	public function isCurrentUserDataOperand(): bool{
		return true;
	}
}
