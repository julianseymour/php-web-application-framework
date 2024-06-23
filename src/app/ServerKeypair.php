<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\KeypairColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\SignatureKeypairColumnsTrait;
use JulianSeymour\PHPWebApplicationFramework\crypt\SodiumKeypair;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\NameColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\Base64Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UrlDatum;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use Exception;

class ServerKeypair extends SodiumKeypair implements StaticTableNameInterface{

	use KeypairColumnsTrait;
	use NameColumnTrait;
	use SignatureKeypairColumnsTrait;
	use StaticTableNameTrait;
		
	public static function getDatabaseNameStatic():string{
		return "data";
	}

	public static function getTableNameStatic(): string{
		return "server_keys";
	}

	public function generateKeypairs(): int{
		$f = __METHOD__;
		try{
			$keypair = sodium_crypto_box_keypair();
			$privateKey = sodium_crypto_box_secretkey($keypair);
			$this->setPrivateKey($privateKey);
			$publicKey = sodium_crypto_box_publickey($keypair);
			$this->setPublicKey($publicKey);
			$signature_kp = sodium_crypto_sign_keypair();
			$signaturePrivateKey = sodium_crypto_sign_secretkey($signature_kp);
			$this->setSignaturePrivateKey($signaturePrivateKey);
			$signaturePublicKey = sodium_crypto_sign_publickey($signature_kp);
			$this->setSignaturePublicKey($signaturePublicKey);
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function beforeGenerateInitialValuesHook(): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::beforeGenerateInitialValuesHook();
		if($this->getCurrentServer()){
			if($print){
				Debug::print("{$f} this is the current server");
			}
			$this->generateKeypairs();
		}elseif($print){
			Debug::print("{$f} this is not the current server");
		}
		return $ret;
	}

	public static function generateCurrentServerKeypair($server_name){
		return static::generateKeypairStatic($server_name, $_SERVER['SERVER_ADDR'], 1);
	}

	public function getServerDomain(){
		return $this->getColumnValue('serverDomain');
	}

	public function setServerDomain($value){
		return $this->setColumnValue('serverDomain', $value);
	}

	public function loadForeignDataStructures($mysqli, $lazy = false, $recursion_depth = 0, bool $subordinate=false): int{
		return SUCCESS;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			parent::declareColumns($columns, $ds);
			$ip = new IpAddressDatum("ipAddress");
			$ip->setHumanReadableName(_("IP address"));
			$publicKey = new Base64Datum("publicKey");
			$signaturePublicKey = new Base64Datum("signaturePublicKey");
			$privateKey = new Base64Datum("privateKey");
			$privateKey->setNullable(true);
			$privateKey->setDefaultValue(null);
			$privateKey->setNeverLeaveServer(true);
			$signaturePrivateKey = new Base64Datum("signaturePrivateKey");
			$signaturePrivateKey->setNullable(true);
			$signaturePrivateKey->setDefaultValue(null);
			$signaturePrivateKey->setNeverLeaveServer(true);
			$server_type = new StringEnumeratedDatum("serverType");
			$server_type->setValueLabelStringsMap([
				SERVER_TYPE_MONOLITHIC => _("Monolithic"),
				SERVER_TYPE_BACKUP => _("Backup"),
				SERVER_TYPE_DATABASE => _("Database"),
				SERVER_TYPE_EMAIL => _("Email"),
				SERVER_TYPE_HOTWALLET => _("Hot wallet"),
				SERVER_TYPE_WEB => _("Web")
			]);
			$server_type->setValidEnumerationMap([
				SERVER_TYPE_DATABASE,
				SERVER_TYPE_WEB,
				SERVER_TYPE_BACKUP,
				SERVER_TYPE_HOTWALLET,
				SERVER_TYPE_EMAIL,
				SERVER_TYPE_MONOLITHIC
			]);
			$server_type->setHumanReadableName(_("Server type"));
			$is_current = new BooleanDatum("isCurrentServer");
			$is_current->setDefaultValue(false);
			$is_current->setHumanReadableName(_("Is current server"));
			$server_domain = new UrlDatum("serverDomain");
			$server_domain->setHumanReadableName(_("Domain"));
			$name = new NameDatum("name");
			// $name->setUniqueFlag(true);
			array_push($columns, $ip, $publicKey, $signaturePublicKey, $privateKey, $signaturePrivateKey, $server_type, $is_current, $server_domain, $name);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setCurrentServer($value){
		return $this->setColumnValue("isCurrentServer", $value);
	}

	public function getCurrentServer():bool{
		return $this->getColumnValue("isCurrentServer");
	}

	public function setServerType($value){
		return $this->setColumnValue("serverType", $value);
	}

	public function getServerType(){
		return $this->getColumnValue("serverType");
	}

	public function setIpAddress(string $ip):string{
		return $this->setColumnValue("ipAddress", $ip);
	}

	public function getIpAddress():string{
		return $this->getColumnValue("ipAddress");
	}

	public static function getPrettyClassName():string{
		return _("Server keypair");
	}

	public static function getPrettyClassNames():string{
		return _("Server keypairs");
	}

	protected function nullPrivateKeyHook(): int{
		$f = __METHOD__;
		Debug::error("{$f} private key is null");
		return FAILURE;
	}
}

