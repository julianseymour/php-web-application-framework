<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

abstract class DatabaseCredentials extends UserOwned{

	use NameColumnTrait;

	public abstract function getPassword();

	public static function getPhylumName(): string{
		return "database_credentials";
	}

	public static function throttleOnInsert(): bool{
		return false;
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_HASH;
	}

	public static function getCompositeUniqueColumnNames(): ?array{
		return [
			[
				"name",
				"host"
			]
		];
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		$print = false;
		parent::reconfigureColumns($columns, $ds);
		$indices = [
			"userDisplayName",
			"userName",
			"userNameKey",
			"userNormalizedName",
			"userTemporaryRole"
		];
		foreach($indices as $index) {
			if($print) {
				Debug::print("{$f} about to volatilize a column \"{$index}\"");
			}
			if(! array_key_exists($index, $columns)) {
				Debug::printArray($columns);
				Debug::error("{$f} key \"{$index}\" does not exist");
			}
			$columns[$index]->volatilize();
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		parent::declareColumns($columns, $ds);
		$name = new NameDatum("name");
		$host = new TextDatum("host");
		$host->setDefaultValue("host");
		array_push($columns, $name, $host);
	}

	public static function getPrettyClassName():string{
		return _("Database credentials");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}
}
