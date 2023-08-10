<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\SecretKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\AsymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use Exception;

class EncryptedDatabaseCredentials extends DatabaseCredentials
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$f = __METHOD__; //EncryptedDatabaseCredentials::getShortClass()."(".static::getShortClass().")::declareColumns()";
		parent::declareColumns($columns, $ds);
		$secret_key = new SecretKeyDatum("password");
		$secret_key->setEncryptionScheme(AsymmetricEncryptionScheme::class);
		static::pushTemporaryColumnsStatic($columns, $secret_key);
	}

	public static function validateMysqlPassword($secret_key)
	{
		$f = __METHOD__; //static::class . "::validateMysqlPassword()";
		try {
			if (! preg_match("#[a-z]+#", $secret_key)) {
				Debug::warning("{$f} Password has no lower case letters");
			} elseif (! preg_match("#[A-Z]+#", $secret_key)) {
				Debug::warning("{$f} Password has no capital letters");
			} elseif (! preg_match("#[0-9]+#", $secret_key)) {
				Debug::warning("Password has no numbers");
			} elseif (! preg_match("#\W+#", $secret_key)) {
				Debug::warning("{$f} Password has no nonalphanumeric characters");
			} else {
				return true;
			}
			return false;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getPassword()
	{
		return $this->getColumnValue("password");
	}

	public function hasPassword()
	{
		return $this->hasColumnValue("password");
	}

	public static function generateMysqlPassword()
	{
		$f = __METHOD__; //EncryptedDatabaseCredentials::getShortClass()."(".static::getShortClass().")::generateMysqlPassword()";
		try {
			$secret_key = substr(base64_encode(random_bytes(32)), 0, 32);
			if (static::validateMysqlPassword($secret_key)) {
				if (substr_count($secret_key, "'") > 0) {
					Debug::warning("{$f} password has an apostrophe in it");
					$secret_key = str_replace("'", "?", $secret_key);
				} else {
					// Debug::print("{$f} no apostrophes here");
				}
				// Debug::print("{$f} returning \"{$secret_key}\"");
				return $secret_key;
			}
			Debug::warning("{$f} try again");
			return static::generateMysqlPassword();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setPassword($password)
	{
		return $this->setColumnValue("password", $password);
	}

	public static function getTableNameStatic(): string
	{
		return "encrypted_database_credentials";
	}

	public static final function getDataType(): string
	{
		return DATATYPE_ENCRYPTED_DATABASE_CREDENTIALS;
	}
}
