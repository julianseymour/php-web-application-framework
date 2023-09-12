<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;
use JulianSeymour\PHPWebApplicationFramework\query\role\MultipleRolesTrait;
use Exception;

class CreateUserStatement extends UserStatement
{

	use MultipleRolesTrait;

	public function __construct(...$users)
	{
		parent::__construct();
		$this->requirePropertyType("roles", DatabaseRoleData::class);
		if(isset($users)) {
			$this->setUsers($users);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"if not exists"
		]);
	}

	public function setIfNotExistsFlag($value = true)
	{
		return $this->setFlag("if not exists", $value);
	}

	public function getIfNotExistsFlag()
	{
		return $this->getFlag("if not exists");
	}

	public function ifNotExists()
	{
		$this->setIfNotExistsFlag(true);
		return $this;
	}

	public function getQueryStatementString(): string
	{
		$f = __METHOD__; //CreateUserStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		try{
			// CREATE USER
			$string = "create user ";
			// [IF NOT EXISTS]
			if($this->getIfNotExistsFlag()) {
				$string .= "if not exists";
			}
			// user [auth_option] [, user [auth_option]] ...
			$count = 0;
			foreach($this->getUsers() as $user) {
				if($count > 0) {
					$string .= ",";
				}
				$string .= $user->toSQL();
				$count ++;
			}
			// [DEFAULT ROLE role [, role ] ...]
			if($this->hasRoles()) {
				$string .= " default role " . implode(',', $this->getRoles());
			}
			// [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
			// tls_option: { SSL | X509 | CIPHER 'cipher' | ISSUER 'issuer' | SUBJECT 'subject' }
			if($this->getRequireNoneFlag() || $this->hasTLSOptions()) {
				$string .= " " . $this->getTLSOptionsString();
			}
			// [WITH resource_option [resource_option] ...]
			if($this->hasResourceOptions()) {
				$string .= $this->getResourceOptionsString();
			}
			if($this->hasPasswordOptions()) {
				$string .= $this->getPasswordOptionsString();
			}
			// [COMMENT 'comment_string' | ATTRIBUTE 'json_object']
			if(($this->hasComment() && hasMinimumMySQLVersion("8.0.21")) || $this->hasAttribute()) {
				$string .= $this->getCommentAttributeString();
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
