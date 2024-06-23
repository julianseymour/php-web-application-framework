<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\database;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CollatedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\EncryptionOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\IfNotExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class CreateDatabaseStatement extends QueryStatement{

	use CharacterSetTrait;
	use CollatedTrait;
	use DatabaseNameTrait;
	use EncryptionOptionTrait;
	use IfNotExistsFlagBearingTrait;

	public function __construct($databaseName = null){
		parent::__construct();
		if($databaseName !== null){
			$this->setDatabaseName($databaseName);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"if not exists"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"if not exists"
		]);
	}
	
	public function getQueryStatementString(){
		// CREATE {DATABASE | SCHEMA} [IF NOT EXISTS] db_name [create_option] ...
		$string = "create database ";
		if($this->getIfNotExistsFlag()){
			$string .= "if not exists ";
		}
		$string .= $this->getDatabaseName();
		if($this->hasCharacterSet()){
			$string .= " character set " . $this->getCharacterSet();
		}
		if($this->hasCollationName()){
			$string .= " collate " . $this->getCollationName();
		}
		if($this->hasEncryption()){
			$string .= " encryption " . $this->getEncryption();
		}
		return $string;
		/*
		 * create_option: [DEFAULT] {
		 * CHARACTER SET [=] charset_name
		 * | COLLATE [=] collation_name
		 * | ENCRYPTION [=] {'Y' | 'N'}
		 * }
		 */
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->characterSet, $deallocate);
		$this->release($this->collationName, $deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->encryptionOption, $deallocate);
		$this->release($this->requiredMySQLVersion, $deallocate);
	}
}
