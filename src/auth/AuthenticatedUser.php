<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\ip_mask;
use function JulianSeymour\PHPWebApplicationFramework\ip_version;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\account\UserNameKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\account\UsernameData;
use JulianSeymour\PHPWebApplicationFramework\account\avatar\ProfileImageData;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\account\login\LoginAttempt;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\Workflow;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\InvalidatedOtp;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\MfaSeedDatum;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\email\EmailNoteworthyInterface;
use JulianSeymour\PHPWebApplicationFramework\email\SpamEmail;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterRespondEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetValueEvent;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationStatusDatumBundle;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\security\SecurityNotificationData;
use JulianSeymour\PHPWebApplicationFramework\security\access\RequestEvent;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\ListedIpAddress;
use JulianSeymour\PHPWebApplicationFramework\security\firewall\UnlistedIpAddressConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\session\BindSessionColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\session\hijack\AntiHijackSessionData;
use Exception;
use mysqli;

abstract class AuthenticatedUser extends PlayableUser{

	use BindSessionColumnsTrait;
	use UserNameKeyColumnTrait;

	/**
	 *
	 * @return FullAuthenticationData
	 */
	public abstract static function getFullAuthenticationDataClass();

	public function getImageData(){
		return $this->getProfileImageData();
	}

	public function getArrayMembershipConfiguration($config_id): array{
		$f = __METHOD__;
		$print = false;
		$config = parent::getArrayMembershipConfiguration($config_id);
		switch($config_id){
			case CONST_DEFAULT:
			default:
				$config['displayName'] = $this->hasDisplayName();
				if($this->hasProfileImageData()){
					// Debug::print("{$f} this user does indeed have an avatar");
					$pid = $this->getProfileImageData();
					$ps = $pid->getObjectStatus();
					if($ps === SUCCESS){
						$s = $pid->getArrayMembershipConfiguration($config_id);
					}else{
						$s = false;
					}
					$config['profileImageKey'] = $s;
				}else{
					if($print){
						Debug::print("{$f} this user does not have an avatar");
					}
					if($print && $this->hasProfileImageKey()){
						Debug::print("{$f} however the key is defined");
					}
				}
				break;
		}
		return $config;
	}

	public function getPushNotificationStatus(string $type):bool{
		$f = __METHOD__;
		try{
			if(!$this->getPushAllNotifications()){
				return false;
			}
			$tnc = mods()->getTypedNotificationClass($type);
			return $this->getColumnValue($tnc::getPushStatusVariableName());
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getEmailNotificationStatus(string $type):bool{
		$f = __METHOD__;
		try{
			switch($type){
				case NOTIFICATION_TYPE_REGISTRATION:
				case NOTIFICATION_TYPE_CHANGE_EMAIL:
				case NOTIFICATION_TYPE_LOCKOUT:
					return true;
				case NOTIFICATION_TYPE_UNLISTED_IP:
					return $this->getAuthLinkEnabled();
				default:
					break;
			}
			if(!$this->getEmailAllNotifications()){
				return false;
			}
			$tnc = mods()->getTypedNotificationClass($type);
			if(!$tnc::canDisable()){
				return true;
			}
			return $this->getColumnValue($tnc::getEmailStatusVariableName());
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getProfileImagesArray(){
		return $this->getForeignDataStructureList(ProfileImageData::getPhylumName());
	}

	public function ejectMfaSeed(){
		return $this->ejectColumnValue("MFASeed");
	}

	public function hasProfileImageData():bool{
		return $this->hasForeignDataStructure('profileImageKey');
	}

	public function setProfileImageData($pid){
		return $this->setForeignDataStructure('profileImageKey', $pid);
	}

	public function ejectProfileImageKey(){
		return $this->ejectColumnValue('profileImageKey');
	}

	public function setProfileImageKey(string $pik):string{
		return $this->setColumnValue('profileImageKey', $pik);
	}

	public function getProfileImageKey():string{
		return $this->getColumnValue('profileImageKey');
	}

	public function hasProfileImageKey():bool{
		return $this->hasColumnValue('profileImageKey');
	}

	public function acquireProfileImageData(mysqli $mysqli){
		return $this->acquireForeignDataStructure($mysqli, 'profileImageKey');
	}

	/**
	 *
	 * @return ProfileImageData
	 */
	public function getProfileImageData(){
		return $this->getForeignDataStructure('profileImageKey');
	}

	public function getHasEverAuthenticated(): bool{
		return true;
	}

	protected function getListedIpAddressCount(mysqli $mysqli, ?string $ip_address = null): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($ip_address === null){
				if($print){
					Debug::print("{$f} getting listed IP address count for current IP");
				}
				$ip_address = $_SERVER['REMOTE_ADDR'];
			}elseif($print){
				Debug::print("{$f} getting listed IP address count for {$ip_address}");
			}
			if(strlen($ip_address) === 0){
				Debug::error("{$f} IP address is empty string");
			}
			$select = ListedIpAddress::selectStatic(null, 'ipAddress', "mask")->where(new AndCommand(new WhereCondition('ipAddress', OPERATOR_EQUALS), new WhereCondition('mask', OPERATOR_EQUALS), ListedIpAddress::whereIntersectionalHostKey(static::class, "userKey")));
			$mask = ip_mask($ip_address);
			if($print){
				$params = [
					$ip_address,
					$mask,
					$this->getIdentifierValue(),
					"userKey"
				];
				Debug::print("{$f} about to get result count for query \"{$select}\" and the following params:");
				Debug::printArray($params);
			}
			$count = $select->prepareBindExecuteGetResultCount($mysqli, 'siss', $ip_address, $mask, $this->getIdentifierValue(), "userKey");
			deallocate($select);
			if($print){
				Debug::print("{$f} returning {$count}");
			}
			return $count;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function acquireListedIpAddresses(mysqli $mysqli){
		$f = __METHOD__;
		try{
			$print = false;
			$phylum = ListedIpAddress::getPhylumName();
			if($this->hasForeignDataStructureList($phylum)){
				if($print){
					Debug::print("{$f} we already have all of our listed IP addresses ready");
				}
				return $this->getForeignDataStructureList($phylum);
			}elseif($print){
				Debug::print("{$f} listed IP addresses are not loaded, about to check whether they are cached");
			}
			if(cache()->enabled() && USER_CACHE_ENABLED){
				if($print){
					Debug::print("{$f} cache is enabled");
				}
				$user_key = $this->getIdentifierValue();
				$index = "ip_addresses_{$user_key}";
				if(cache()->hasAPCu($index)){
					if($print){
						Debug::print("{$f} found a cached IP address list \"{$index}\"");
					}
					$results = cache()->getAPCu($index);
					foreach($results as $key => $parsed){
						$cidr = $parsed['cidr'];
						if($print){
							Debug::print("{$f} IP address/range {$r} with key \"{$key}\"");
						}
						$ip = new ListedIpAddress();
						$ip->setUserData($this);
						$list = $parsed['list'];
						$ip->setList($list);
						$ip->setIdentifierValue($key);
						$splat = explode('/', $cidr);
						$mask = $splat[1];
						$range = $splat[0];
						if($print){
							Debug::print("{$f} range {$range} with mask {$mask}");
						}
						$ip->setIpAddress($range);
						$ip->setMask($mask);
						$ip->setIpVersion(ip_version($range));
						$this->setForeignDataStructureListMember($phylum, $ip);
					}
					return $this->getForeignDataStructureList($phylum);
				}elseif($print){
					Debug::print("{$f} nothing cached for \"{$index}\"");
				}
			}elseif($print){
				Debug::print("{$f} user cache is disabled");
			}
			$select = ListedIpAddress::selectStatic()->where(
				ListedIpAddress::whereIntersectionalHostKey(static::class, "userKey")
			)->orderBy(new OrderByClause("mask", DIRECTION_DESCENDING))->withTypeSpecifier('ss')->withParameters($this->getIdentifierValue(), "userKey")->withParentKeyName("userKey");
			if(!$select->hasWhereCondition()){
				Debug::error("{$f} apparently, this query has no where condition");
			}elseif($print){
				Debug::print("{$f} where condition assigned correctly");
			}
			$children = Loadout::loadChildClass($mysqli, $this, $phylum, ListedIpAddress::class, $select, false);
			if($print){
				Debug::print("{$f} returned from loadChildClass");
			}
			deallocate($select);
			if(empty($children)){
				if($print){
					Debug::print("{$f} there are no listed IP addresses for this user");
				}
				return [];
			}elseif($print){
				Debug::print("{$f} ".count($children)." listed IP addresses for this ".$this->getDebugString());
			}
			$cache_me = [];
			foreach($children as $key => $ip){
				if(!$ip->getColumn("userKey")->getRank() === RANK_PARENT){
					Debug::error("{$f} userKey column does not have the parent key flag set for ".$ip->getDebugString()."with key \"{$key}\"");
				}
				if($print){
					Debug::print("{$f} about to call loadForeignDataStructures on ".$ip->getDebugString());
				}
				$status = $ip->loadForeignDataStructures($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} calling loadForeignDataStructures on listed IP address returned error status \"{$err}\"");
					$this->setObjectStatus($status);
				}
				// cache listed IP addresses
				if(!cache()->enabled() || ! USER_CACHE_ENABLED){
					if($print){
						Debug::print("{$f} cache is disabled, continuing");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} about to get cacheable value of ".$ip->getDebugString());
				}
				$ip->configureArrayMembership("cache");
				$cache_me[$ip->getIdentifierValue()] = $ip->toArray();
			}
			if(cache()->enabled() && USER_CACHE_ENABLED){
				cache()->setAPCu($index, $cache_me, $this->getTimeToLive());
			}elseif($print){
				Debug::print("{$f} cache is disabled, skipping cache of listed IP addresses");
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return $this->getForeignDataStructureList($phylum);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function filterIpAddress(mysqli $mysqli, ?string $ip_address = null, bool $skip_insert = false): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($ip_address === null){
				$ip_address = $_SERVER['REMOTE_ADDR'];
			}
			if(strlen($ip_address) === 0){
				Debug::error("{$f} IP address is empty string");
			}
			$user = $this;
			app()->getWorkflow()->addEventListener(EVENT_AFTER_RESPOND, function (AfterRespondEvent $event, Workflow $target) use ($f, $print, $user, $ip_address, $skip_insert){
				$unlisted = false;
				if(!$skip_insert){
					$mysqli = db()->getConnection(PublicWriteCredentials::class);
				} else{
					if($print){
						Debug::print("{$f} skipping check for auto insert");
					}
					$mysqli = db()->getConnection(PublicReadCredentials::class);
				}
				// see if user has this IP listed already
				if(!$mysqli instanceof mysqli){
					Debug::error("{$f} mysqli is not an instanceof mysqli");
				}
				$count = $user->getListedIpAddressCount($mysqli, $ip_address);
				if($count === 0){
					if($print){
						Debug::print("{$f} this is an unlisted IP address");
					}
					$unlisted = true;
				}elseif($print){
					Debug::print("{$f} this IP address is not unlisted");
				}
				if($unlisted && ! $skip_insert){
					if($print){
						Debug::print("{$f} this is a real validation -- automatically inserting new IP ddress");
					}
					$mysqli = db()->getConnection(PublicWriteCredentials::class);
					if(!isset($mysqli)){
						$status = $user->setObjectStatus(ERROR_MYSQL_CONNECT);
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} {$err}");
						return $status;
					}
					$status = $user->listIpAddress($mysqli, $ip_address);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} listIpAddress returned error status \"{$err}\"");
						return $user->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully inserted unlisted IP address");
					}
					if(db()->hasPendingTransactionId()){
						db()->commitTransaction($mysqli, db()->getPendingTransactionId());
					}
				}elseif($print){
					Debug::print("{$f} IP is not unlisted -- skipping auto insert");
				}
				return SUCCESS;
			});
			if($print){
				Debug::print("{$f} about to call acquireListedIpAddresses");
			}
			$children = $this->acquireListedIpAddresses($mysqli);
			if($print){
				Debug::print("{$f} returned from acquireListedIpAddresses");
			}
			$cc = count($children);
			if($cc === 0){
				if($print){
					Debug::print("{$f} 0 children");
				}
			}elseif($print){
				Debug::print("{$f} {$cc} listed IP addresses for this user");
			}
			$policy = $this->getFilterPolicy();
			if(!empty(($children))){
				foreach($children as $listed_ip){
					if($listed_ip->getDeleteFlag()){
						if($print){
							Debug::print("{$f} listed IP address is flagged for deletion");
						}
						continue;
					}elseif($listed_ip->getList() === POLICY_NONE){
						if($print){
							Debug::print("{$f} no policy for this IP address/range");
						}
						continue;
					}elseif($listed_ip->getObjectStatus() === STATUS_SKIP_ME){
						if($print){
							Debug::print("{$f} skipping this IP address");
						}
						continue;
					}elseif(!$listed_ip->hasUserData()){
						Debug::warning("{$f} a listed IP address is missing its user data");
						if($listed_ip->hasUserKey() && $listed_ip->hasUserAccountType()){
							Debug::print("{$f} user key and user account type are both defined");
							if($listed_ip->getColumn("userKey")->getAutoloadFlag()){
								$decl = $listed_ip->getDeclarationLine();
								Debug::error("{$f} user key is flagged for auto load, something went wrong. Listed IP address was instantiated {$decl}");
							}
							Debug::error("{$f} listed IP address's user key column is not flagged for auto load");
						}
						Debug::error("{$f} listed IP address is missing its user key and/or account type");
					}
					$status = $listed_ip->cidrMatchYourself($ip_address);
					if(!is_int($status)){
						Debug::error("{$f} cidrMatchYourself returned non-integer value");
					}
					$err = ErrorMessage::getResultMessage($status);
					if($status !== RESULT_CIDR_UNMATCHED){
						if($print){
							Debug::print("{$f} match found");
						}
						if(!isset($mysqli)){
							Debug::warning("{$f} mysqli object is null, skipping access flag check");
						}elseif(!$listed_ip->getAccessAttempted()){
							if($print){
								Debug::print("{$f} access was not previously attempted -- updating IP listing now");
							}
							$listed_ip->setAccessAttempted(true);
							$listed_ip->setColumnValue("wasAccessAttempted", true);
							$status2 = $listed_ip->update($mysqli);
							if($status2 !== SUCCESS){
								$err = ErrorMessage::getResultMessage($status2);
								Debug::warning("{$f} updating wasAccessAttempted returned error status \"{$err}\"");
								return $this->setObjectStatus($status2);
							}
							if($print){
								Debug::print("{$f} successfully marked IP address as access attempted");
							}
						}elseif($print){
							Debug::print("{$f} IP address was already marked for attempted access");
						}
						if($print){
							Debug::print("{$f} returning status \"{$err}\" with match found");
						}
						return $status;
					}elseif($print){
						Debug::print("{$f} match not found");
					}
				}
			}
			if($print){
				Debug::print("{$f} user's IP address is not explicitly listed -- if they have a whitelist in effect, block this login attempt");
			}
			if($policy === POLICY_BLOCK){
				Debug::warning("{$f} this user has whitelist mode in effect");
				return $this->setObjectStatus(RESULT_BFP_WHITELIST_UNAUTHORIZED);
			}elseif($print){
				Debug::print("{$f} user has filter policy \"{$policy}\"");
			}
			app()->setFlag("validIpAddress");
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function listIpAddress(mysqli $mysqli, ?string $ip_address = null){
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->hasRequestEventObject()){
				Debug::error("{$f} request attempt object is undefined");
			}
			if($ip_address === null){
				$ip_address = $_SERVER['REMOTE_ADDR'];
			}
			$version = ip_version($ip_address);
			switch($version){
				case 4:
					$mask = 32;
					break;
				case 6:
					$mask = 128;
					break;
				default:
					Debug::error("{$f} invalid IP version \"{$version}\"");
			}
			$listed_ip = new ListedIpAddress();
			$listed_ip->setUserData($this);
			if(!$listed_ip->hasUserKey()){
				Debug::error("{$f} listed IP address lacks a user key");
			}
			$listed_ip->setIpAddress($ip_address);
			$listed_ip->setIpVersion($version);
			$listed_ip->setList(POLICY_NONE);
			$listed_ip->setMask($mask);
			$listed_ip->setAccessAttempted(true);
			$attempt = $this->getRequestEventObject();
			if(!isset($attempt)){
				Debug::error("{$f} request attempt object is undefined");
			}
			$reason = $attempt->getReasonLoggedStatic();
			$listed_ip->setReasonLogged($reason);
			if(!$attempt instanceof ReauthenticationEvent){
				$status = $attempt->getObjectStatus();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} request attempt object has error status \"{$err}\"");
				}
				$listed_ip->setLastAttemptObject($attempt);
			}
			$this->setForeignDataStructureListMember(ListedIpAddress::getPhylumName(), $listed_ip);
			$user = $this;
			$attempt->addEventListener(EVENT_AFTER_INSERT, function (AfterInsertEvent $event, RequestEvent $attempt) use ($user, $listed_ip, $f, $print){
				$attempt->removeEventListener($event);
				$mysqli = db()->reconnect(PublicWriteCredentials::class);
				$status = $listed_ip->insert($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} writing new IP to database returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
				$user->setRequestEventObject($listed_ip);
				if($print){
					Debug::print("{$f} successfuly wrote new IP address to database");
				}
				if($user->getFilterPolicy() === POLICY_BLOCK || $user->getAuthLinkEnabled()){
					if($print){
						Debug::print("{$f} auth link is enabled -- about to send this user an UnlistedIpAddressConfirmationCode");
					}
					$status = UnlistedIpAddressConfirmationCode::submitStatic($mysqli, $user);
					if($status === RESULT_CIDR_UNMATCHED){
						if($print){
							Debug::print("{$f} successfully wrote record to database");
						}
					}elseif($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} writeToDatabase returned error status \"{$err}\"");
						return $user->setObjectStatus($status);
					}else{
						Debug::error("{$f} submitStatic should not return success");
					}
				}elseif($print){
					Debug::print("{$f} authorization link email is not permitted");
				}
				$user->setTemporaryRole(USER_ROLE_RECIPIENT);
				$status = $listed_ip->reload($mysqli, false);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} reloading listed IP address returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
				$type = SecurityNotificationData::getNotificationTypeStatic();
				if($user->getEmailNotificationStatus($type) || $user->getPushNotificationStatus($type)){
					$status = $user->notify($mysqli, $listed_ip);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} sending notification returned error status \"{$err}\"");
						return $event->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} returning normally");
					}
				}
				return $this->setObjectStatus(SUCCESS);
			});
			if($attempt instanceof ReauthenticationEvent){
				if($print){
					Debug::print("{$f} attempt is a reauthentication event -- dispatching AfterInsertEvent immediately");
				}
				if($attempt->hasAnyEventListener(EVENT_AFTER_INSERT)){
					$attempt->dispatchEvent(new AfterInsertEvent());
				}
			}elseif($print){
				Debug::print("{$f} attempt is not a reauthentication event");
			}
			if($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setMfaSeed($mfa_seed){
		return $this->setColumnValue('MFASeed', $mfa_seed);
	}

	public function setHardResetCount($count): int{
		return $this->setColumnValue('hardResetCount', $count);
	}

	public function getHardResetCount(): int{
		return $this->getColumnValue('hardResetCount');
	}

	public function hasMfaSeed():bool{
		return $this->hasColumnValue('MFASeed');
	}

	public function getMfaSeed(){
		return $this->getColumnValue('MFASeed');
	}

	public function setMFAStatus($i){
		return $this->setColumnValue('MFAStatus', $i);
	}

	public function setNormalizedEmailAddress(string $email):string{
		return $this->setColumnValue('normalizedEmailAddress', $email);
	}

	/**
	 *
	 * @return string|NULL
	 */
	public function getNormalizedEmailAddress():string{
		$f = __METHOD__;
		try{
			$normal = $this->getColumnValue('normalizedEmailAddress');
			if(!isset($normal)){
				$email = $this->getEmailAddress();
				if(!isset($email)){
					Debug::error("{$f} email address (normal and non-normalized) is undefined");
				}
				// Debug::print("{$f} normalizing email address");
				$normal = static::normalizeEmailAddress($email);
				return $this->setNormalizedEmailAddress($normal);
			}
			return $normal;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getNormalizedNameSelectStatement(string $normalized): SelectStatement{
		$f = __METHOD__;
		$print = false;
		$select = $this->select()->where(
			new WhereCondition(
				'uniqueKey', 
				OPERATOR_EQUALS, 
				null, 
				UsernameData::generateLazyAliasExpression(
					static::class, 
					"userKey", // we are selecting user keys
					QueryBuilder::select('uniqueKey')->from(
						UsernameData::getDatabaseNameStatic(), 
						UsernameData::getTableNameStatic()
					)->where(
						new WhereCondition("normalizedName", OPERATOR_EQUALS)
					)->escape(ESCAPE_TYPE_PARENTHESIS)
				)
			)
		)->withTypeSpecifier("ss")->withParameters([
			$normalized,
			"userKey"
		]);
		if($print){
			Debug::print("{$f} query for selecting user by normalized name: \"{$select}\", with the following parameters");
			$params = $select->getParameters();
			Debug::printArray($params);
		}
		return $select;
	}

	public function hasEmailAddress():bool{
		return $this->hasColumnValue('emailAddress');
	}

	public function sendEmail(mysqli $mysqli, EmailNoteworthyInterface $subject){
		$f = __METHOD__;
		try{
			$print = false;
			$emc = $subject->getEmailNotificationClass();
			if($emc === null){
				if($print){
					Debug::print("{$f} this class does not send email notifications");
				}
				return SUCCESS;
			}elseif(!is_string($emc)){
				Debug::error("{$f} email notification class is not a string");
			}elseif(!class_exists($emc)){
				Debug::error("{$f} email notification class \"{$emc}\" does not exist");
			}elseif(!is_a($emc, SpamEmail::class, true)){
				Debug::error("{$f} email notification class \"{$emc}\" does not extend SpamEmail");
			}elseif($print){
				$sc = $subject->getClass();
				Debug::print("{$f} about to send an email notification of class \"{$emc}\" for subject of class \"{$sc}\"");
			}
			$message = new $emc();
			$message->setRecipient($this);
			$message->setSubjectData($subject);
			if(!$message->hasSubjectData()){
				Debug::error("{$f} immediately after setting subject data, it is undefined");
			}
			return $message->sendAndInsert($mysqli);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getRecoveryTimestamps($mysqli){
		return $this->getVerificationTimestamps($mysqli, ACCESS_TYPE_RESET);
	}

	public function hasLastAuthenticatedIpAddress(){
		return $this->hasColumnValue('lastAuthenticatedIpAddress');
	}

	public function acquireLastAuthenticatedIpAddress($mysqli){
		$f = __METHOD__;
		try{
			if($this->hasLastAuthenticatedIpAddress()){
				return $this->getLastAuthenticatedIpAddress();
			}elseif(!isset($mysqli)){
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
			}
			$identifiers = [
				$this->getIdentifierValue(),
				"userKey",
				SUCCESS
			];
			$where = new AndCommand(LoginAttempt::whereIntersectionalHostKey(static::class, "userKey"), // new WhereCondition('userKey', OPERATOR_EQUALS),
			new WhereCondition('loginResult', OPERATOR_EQUALS));
			$order_by = new OrderByClause("insertTimestamp", DIRECTION_DESCENDING);
			$attempt = new LoginAttempt();
			$attempt->setUserData($this);
			$status = $attempt->load($mysqli, $where, $identifiers, $order_by, 1);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loading last authenticated IP address returned error status \"{$err}\"");
				// $this->setObjectStatus($status);
				return null;
			}
			$ip = $attempt->getInsertIpAddress();
			// Debug::print("{$f} returning \"{$ip}\"");
			return $this->setLastAuthenticatedIpAddress($ip);
		}catch(Exception $x){
			x($f, $x);
			return null;
		}
	}

	public function getLastAuthenticatedIpAddress(){
		$f = __METHOD__;
		if($this->hasLastAuthenticatedIpAddress()){
			return $this->getColumnValue('lastAuthenticatedIpAddress');
		}
		$name = $this->getName();
		Debug::warning("{$f} last authenticated IP address is undefined for user \"{$name}\"");
		return null;
	}

	public function setLastAuthenticatedIpAddress($ip){
		return $this->setColumnValue("lastAuthenticatedIpAddress", $ip);
	}

	/**
	 * invalidates an OTP for a user
	 *
	 * @param mysqli $mysqli
	 * @param string $otp
	 * @return int
	 */
	protected function invalidateOTP(mysqli $mysqli, string $otp){
		$f = __METHOD__;
		try{
			$invalidated = new InvalidatedOtp();
			$invalidated->setUserData($this);
			if(!$this->hasUsernameData()){
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} user lacks username data; instantiated {$decl}");
			}elseif(!$invalidated->hasUserNameKey()){
				Debug::error("{$f} username key is undefined for OTP data");
			}
			// Debug::print("{$f} created invalidated OTP object; about to set its code");
			$invalidated->setOTP($otp);
			// Debug::print("{$f} set invalidated OTP code; about to write to database");
			$status = $invalidated->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} failed to write invalidated MFA: got abnormal failure status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} returning successfully");
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getPushAllNotifications(){
		return $this->getColumnValue('pushAllNotifications');
	}

	public function getEmailAllNotifications(){
		return $this->getColumnValue('emailAllNotifications');
	}

	public function setPushAllNotifications($value){
		return $this->setColumnValue('pushAllNotifications', $value);
	}

	public function setEmailAllNotifications($value){
		return $this->setColumnValue('emailAllNotifications', $value);
	}

	public function getMFAStatus(){
		return $this->getColumnValue('MFAStatus');
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string $otp
	 * @return boolean
	 */
	public function isOTPInvalidated(mysqli $mysqli, string $otp):bool{
		$f = __METHOD__;
		try{
			$count = InvalidatedOtp::selectStatic(null, "otp", "insertTimestamp")->where(new AndCommand(new WhereCondition("otp", OPERATOR_EQUALS), new WhereCondition("insertTimestamp", OPERATOR_GREATERTHAN)))->prepareBindExecuteGetResultCount($mysqli, 'si', $otp, time() - 1440);
			if($count > 0){
				return true;
			}
			$status = $this->invalidateOTP($mysqli, $otp);
			if($status !== SUCCESS){
				$this->setObjectStatus($status);
				return true;
			}
			return false;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);
			$email = new EmailAddressDatum("emailAddress");
			$email->setUserWritableFlag(true);
			// $email->setSensitiveFlag(true);
			$email->setHumanReadableName(_("Email address"));
			$email->addEventListener(EVENT_AFTER_SET_VALUE, function ($event, $target){
				$value = $event->getProperty('value');
				$ds = $target->getDataStructure();
				if(empty($value)){
					$ds->setNormalizedEmailAddress(null);
				}else{
					$ds->setNormalizedEmailAddress(EmailAddressDatum::normalize($value));
				}
			});

			$lastLoginTimestamp = new TimestampDatum("lastLoginTimestamp");
			$lastLoginTimestamp->setUserWritableFlag(true);
			$lastLoginTimestamp->setSensitiveFlag(true);
			$lastLoginTimestamp->setDefaultValue(0);
			$reset = new TimestampDatum("hardResetTimestamp");
			$reset->setUserWritableFlag(true);
			$reset->setSensitiveFlag(true);
			$reset->setNullable(true);
			$mfa = new BooleanDatum("MFAStatus");
			$mfa->setDefaultValue(false);
			// $mfa->setSensitiveFlag(true);
			$mfa->setUserWritableFlag(true);

			$mfa_seed = new MfaSeedDatum('MFASeed');
			$mfa_seed->setUserWritableFlag(true);
			$mfa_seed->setNullable(true);
			$mfa_seed->setNeverLeaveServer(true);

			$notify = new BooleanDatum("pushAllNotifications");
			$notify->setDefaultValue(true);
			$notify->setUserWritableFlag(true);
			$notify->setSensitiveFlag(true);
			$notify->setHumanReadableName(_("All"));

			$send_mail = new BooleanDatum('emailAllNotifications');
			$send_mail->setDefaultValue(true);
			$send_mail->setUserWritableFlag(true);
			$send_mail->setSensitiveFlag(true);
			$send_mail->setHumanReadableName(_("All"));

			$encrypt_email = new BooleanDatum("encryptEmail");
			$encrypt_email->setDefaultValue(false);
			$encrypt_email->setUserWritableFlag(true);
			$encrypt_email->setSensitiveFlag(true);

			$normalized = new TextDatum("normalizedEmailAddress");
			$hard = new UnsignedIntegerDatum("hardResetCount", 32);
			$hard->setSensitiveFlag(true);
			$hard->setDefaultValue(0);
			$block_ipv6 = new BooleanDatum("blockIpv6");
			$block_ipv6->setDefaultValue(false);
			$block_ipv6->setUserWritableFlag(true);
			$filter = new StringEnumeratedDatum("filterPolicy");
			$filter->setDefaultValue(POLICY_NONE);
			$filter->setUserWritableFlag(true);
			$filter->setValidEnumerationMap([
				POLICY_BLOCK,
				POLICY_NONE,
				POLICY_ALLOW
			]);
			$filter->setHumanReadableName(_("Whitelist mode"));
			$filter->setValidationClosure(function ($value, TextDatum $target){
				$user = $target->getDataStructure();
				$backup = $target->getValue();
				$target->setValue($value);
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				$r = $user->filterIpAddress($mysqli, $_SERVER['REMOTE_ADDR'], true);
				$target->setValue($backup);
				switch($r){
					case ERROR_IP_ADDRESS_NOT_AUTHORIZED:
						return ERROR_FILTER_LOCKED_OUT;
					default:
						return $r;
				}
			});

			$auth_link = new BooleanDatum("authLinkEnabled");
			$auth_link->setDefaultValue(true);
			$auth_link->setHumanReadableName(_("Send authorization email"));
			$reset_name = new BooleanDatum("forgotPasswordEnabled");
			$reset_name->setDefaultValue(true);
			$reset_name->setHumanReadableName(_("Reset password with username"));
			$reset_email = new BooleanDatum("forgotUsernameEnabled");
			$reset_email->setDefaultValue(true);
			$reset_email->setHumanReadableName(_("Reset password with email address"));

			$profile_image_key = new ForeignKeyDatum("profileImageKey");
			$profile_image_key->setNullable(true);
			$profile_image_key->setDefaultValue(null);
			$profile_image_key->autoload();
			$profile_image_key->setUpdateBehavior(FOREIGN_UPDATE_BEHAVIOR_DELETE);
			$profile_image_key->setForeignDataStructureClass(ProfileImageData::class);
			$profile_image_key->setConstraintFlag(true);
			$profile_image_key->setOnUpdate(REFERENCE_OPTION_CASCADE);
			$profile_image_key->setOnDelete(REFERENCE_OPTION_SET_NULL);
			$profile_image_key->setConverseRelationshipKeyName("userKey");
			$profile_image_key->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
			if($ds !== null && $ds->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
				$profile_image_key->setTimeToLive(SESSION_TIMEOUT_SECONDS);
				$profile_image_key->setRank(RANK_CHILD);
			}
			$logoutTimestamp = new TimestampDatum("logoutTimestamp");
			$logoutTimestamp->setNullable(true);
			$logoutTimestamp->setDefaultValue(null);
			$push_note_bundle = new NotificationStatusDatumBundle("push");
			$email_note_bundle = new NotificationStatusDatumBundle("email");
			$where = new BinaryExpressionCommand(
				new GetDeclaredVariableCommand("usernames_alias.uniqueKey"), 
				OPERATOR_EQUALSEQUALS,
				new GetDeclaredVariableCommand("t0.userNameKey")
			);
			$name = new NameDatum('name');
			$name->setSubqueryClass(UsernameData::class);
			$name->setSubqueryWhereCondition($where);
			
			$normalized_name = new TextDatum("normalizedName");
			$normalized_name->setSortable(true);
			$normalized_name->setSubqueryClass(UsernameData::class);
			$normalized_name->setSubqueryWhereCondition($where);

			$display_name = new NameDatum("displayName");
			$display_name->setNullable(true);
			$display_name->setDefaultValue(null);
			$display_name->setSubqueryClass(UsernameData::class);
			$display_name->setSubqueryWhereCondition($where);
			
			$bind_ip = new BooleanDatum("bindIpAddress");
			$bind_ip->setDefaultValue(false);
			$bind_ip->setPersistenceMode(PERSISTENCE_MODE_COOKIE);
			$bind_ip->setHumanReadableName(_("Bind IP address"));
			$bind_ip->addEventListener(EVENT_AFTER_SET_VALUE, function (AfterSetValueEvent $event, BooleanDatum $target){
				$f = __METHOD__;
				$print = false;
				if($print){
					Debug::print("{$f} fired event");
				}
				$ahsd = new AntiHijackSessionData();
				$value = $event->getValue();
				if($value){
					if($print){
						Debug::print("{$f} value is set");
					}
					$ahsd->setBoundIpAddress($_SERVER['REMOTE_ADDR']);
				}elseif($print){
					Debug::print("{$f} value is not set");
					$ahsd->unsetBoundIpAddress();
				}
			});
			$bind_ua = new BooleanDatum("bindUserAgent");
			$bind_ua->setDefaultValue(false);
			$bind_ua->setPersistenceMode(PERSISTENCE_MODE_COOKIE);
			$bind_ua->setHumanReadableName(_("Bind user agent"));
			$bind_ua->addEventListener(EVENT_AFTER_SET_VALUE, function (AfterSetValueEvent $event, BooleanDatum $target){
				$f = __METHOD__;
				$print = false;
				if($print){
					Debug::print("{$f} fired event");
				}
				$ahsd = new AntiHijackSessionData();
				$value = $event->getValue();
				if($value){
					if($print){
						Debug::print("{$f} value is set");
					}
					$ahsd->setBoundUserAgent($_SERVER['HTTP_USER_AGENT']);
				}elseif($print){
					Debug::print("{$f} value is not set");
					$ahsd->unsetBoundUserAgent();
				}
			});

			$lastAuthenticatedIpAddress = new IpAddressDatum("lastAuthenticatedIpAddress");
			$lastAuthenticatedIpAddress->volatilize();

			$userNameKey = new ForeignKeyDatum("userNameKey");
			$userNameKey->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
			$userNameKey->setForeignDataStructureClass(UsernameData::class);
			if($ds !== null && $ds->getAllocationMode() === ALLOCATION_MODE_SUBJECTIVE){
				$userNameKey->setRank(RANK_CHILD);
				$userNameKey->autoload();
			}
			$userNameKey->constrain();
			$userNameKey->onUpdate(REFERENCE_OPTION_CASCADE);
			$userNameKey->onDelete(REFERENCE_OPTION_SET_NULL);
			$userNameKey->setConverseRelationshipKeyName("userKey");

			array_push($columns, $email, $normalized, $lastLoginTimestamp, $mfa, $notify, $send_mail, $mfa_seed, $encrypt_email, $hard, $block_ipv6, $filter, $auth_link, $reset_name, $reset_email, $profile_image_key, $logoutTimestamp, $name, $normalized_name, $display_name, $push_note_bundle, $email_note_bundle, $bind_ip, $bind_ua, $lastAuthenticatedIpAddress, $userNameKey, $reset);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function ejectLogoutTimestamp(){
		return $this->ejectColumnValue("logoutTimestamp");
	}

	public function setLogoutTimestamp($timestamp){
		return $this->setColumnValue("logoutTimestamp", $timestamp);
	}

	public function hasLogoutTimestamp():bool{
		return $this->hasColumnValue("logoutTimestamp");
	}

	public function getLogoutTimestamp(){
		return $this->getColumnValue("logoutTimestamp");
	}

	public function getForgotPasswordEnabled():bool{
		return $this->getColumnValue("forgotPasswordEnabled");
	}

	public function getForgotUsernameEnabled():bool{
		return $this->getColumnValue("forgotUsernameEnabled");
	}

	public function updateLogoutTimestamp(mysqli $mysqli, $timestamp){
		$this->setLogoutTimestamp($timestamp);
		return $this->update($mysqli);
	}

	public function getAuthLinkEnabled():bool{
		return $this->getColumnValue("authLinkEnabled");
	}

	public function setAuthLinkEnabled($value){
		return $this->setColumnValue("authLinkEnabled", $value);
	}

	public function setFilterPolicy(string $value){
		return $this->setColumnValue('filterPolicy', $value);
	}

	public static function getPassword(){
		$f = __METHOD__;
		if(!hasInputParameter("password")){
			Debug::error("{$f} password is undefined");
		}
		return getInputParameter('password');
	}

	public function getFilterPolicy(){
		return $this->getColumnValue('filterPolicy');
	}

	public static function getPostedPassword(){
		return getInputParameter("password");
	}

	public function getUnambiguousName(): string{
		$name = $this->getName();
		if(!$this->hasDisplayName()){
			return $name;
		}
		return $this->getDisplayName() . " ({$name})";
	}
}
