<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\correspondent;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\UserMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\security\access\UserFingerprint;
use Exception;
use mysqli;

/**
 * objects that have a correspondent key
 *
 * @author j
 */
abstract class UserCorrespondence extends UserFingerprint implements JavaScriptCounterpartInterface
{

	use CorrespondentKeyColumnTrait;
	use JavaScriptCounterpartTrait;

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"recent"
		]);
	}

	public function getRecentFlag()
	{
		return $this->getFlag("recent");
	}

	public function setRecentFlag($value)
	{
		return $this->setFlag("recent", $value);
	}

	public function getUserClass($vn)
	{
		if($vn !== "correspondentKey") {
			return parent::getUserClass($vn);
		}
		return static::getUserAccountClassStatic($this->getCorrespondentAccountType());
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array
	{
		$roles = parent::getUserRoles($mysqli, $user);
		if($user instanceof UserData && $this->hasCorrespondentKey() && $this->getCorrespondentKey() === $user->getIdentifierValue()) {
			$roles["correspondent"] = 'correspondent';
		}
		return $roles;
	}

	public function getArrayMembershipConfiguration($config_id): ?array
	{
		$f = __METHOD__; //UserCorrespondence::getShortClass()."(".static::getShortClass().")->getArrayMembershipConfiguration({$config_id})";
		try{
			$config = parent::getArrayMembershipConfiguration($config_id);
			foreach(array_keys($config) as $column_name) {
				if(!$this->hasColumn($column_name)) {
					Debug::error("{$f} datum \"{$column_name}\" does not exist");
				}
			}
			// Debug::print("{$f} parent function returned the following array:");
			// Debug::printArray($config);
			switch ($config_id) {
				case "default":
					if($this->hasColumn("correspondentDisplayName")) {
						$config['correspondentDisplayName'] = true;
					}
					if($this->hasColumn("correspondentName")) {
						$config['correspondentName'] = true;
					}
					if($this->hasColumn("correspondentKey")) {
						if($this->hasCorrespondentObject()) {
							$config['correspondentKey'] = $this->getCorrespondentObject()->getArrayMembershipConfiguration($config_id);
						}else{
							$config['correspondentKey'] = true;
						}
					}
					if($this->hasColumn("correspondentAccountType")) {
						$config['correspondentAccountType'] = true;
						// $config['correspondentAccountTypeString'] = true;
					}
					$config['recent'] = $this->getRecentFlag();
				default:
					return $config;
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$indices = [
			"reasonLogged",
			"reasonLoggedString"
			// "correspondentMasterAccountKey"
		];
		foreach($indices as $i) {
			$columns[$i]->volatilize();
		}
	}

	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try{
			switch ($column_name) {
				case "correspondentDisplayName":
					if(!$this->hasCorrespondentObject()) {
						// Debug::warning("{$f} correspondent object is undefined");
						return _("Undefined correspondent name");
					}
					return $this->getCorrespondentDisplayName();
				case "correspondentName":
					return $this->getCorrespondentName();
				case "correspondentAccountTypeString":
					return $this->getCorrespondentAccountTypeString();
				case "recent":
					return $this->getRecentFlag();
				default:
					return parent::getVirtualColumnValue($column_name);
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool{
		switch ($column_name) {
			case "correspondentDisplayName":
				return true;
			case "correspondentName":
				return $this->hasCorrespondentObject() && $this->getCorrespondentObject()->hasName();
			case "correspondentAccountTypeString":
				return $this->hasCorrespondentObject() && $this->getCorrespondentObject()->hasAccountType();
			case "recent":
				return $this->getRecentFlag();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$bundle = new UserMetadataBundle("correspondent", $ds);
		$bundle->constrain();
		// $bundle->setAutoloadFlag(true);
		$recent = new VirtualDatum("recent");
		array_push($columns, $bundle, $recent);
	}
}
