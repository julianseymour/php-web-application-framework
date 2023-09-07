<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\CharDatum;
use JulianSeymour\PHPWebApplicationFramework\query\DatabaseVersionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

class DatabaseRoleData extends DataStructure implements SQLInterface, StaticTableNameInterface{

	use DatabaseVersionTrait;
	use StaticTableNameTrait;
	
	public static function create():DatabaseRoleData{
		return new static();
	}

	public static function getDatabaseNameStatic(): string{
		return "mysql";
	}

	public static function getTableNameStatic(): string{
		return "user";
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		// Host
		$Host = new CharDatum("Host", 255);
		$Host->setPrimaryKeyFlag(true);
		$Host->setNullable(false);
		// User
		$User = new CharDatum("User", 32);
		$User->setPrimaryKeyFlag(true);
		$User->setNullable(false);
		// Select_priv
		// Insert_priv
		// Update_priv
		// Delete_priv
		// Create_priv
		// Drop_priv
		// Reload_priv
		// Shutdown_priv
		// Process_priv
		// File_priv
		// Grant_priv
		// References_priv
		// Index_priv
		// Alter_priv
		// Show_db_priv
		// Super_priv
		// Create_tmp_table_priv
		// Lock_tables_priv
		// Execute_priv
		// Repl_slave_priv
		// Repl_client_priv
		// Create_view_priv
		// Show_view_priv
		// Create_routine_priv
		// Alter_routine_priv
		// Create_user_priv
		// Event_priv
		// Trigger_priv
		// Create_tablespace_priv
		// ssl_type
		// ssl_cipher
		// x509_issuer
		// x509_subject
		// max_questions
		// max_updates
		// max_connections
		// max_user_connections
		// plugin
		$plugin = new CharDatum("plugin", 64);
		$plugin->setNullable(false);
		$plugin->setDefaultValue("caching_sha2_password");
		// authentication_string
		// $password = new TextDatum("authentication_string");
		// $password->setNullable(true);
		// $password->setDefaultValue(null);
		// password_expired
		// password_last_changed
		// password_lifetime
		// account_locked
		// Create_role_priv
		// Drop_role_priv
		// Password_reuse_history
		// Password_reuse_time
		// Password_require_current
		// User_attributes
		array_push($columns, $Host, $User, $plugin);
	}

	public static function getPrettyClassName():string{
		return _("Role");
	}

	public function getUsernameHostString():string{
		return single_quote($this->getUsername()) . "@" . single_quote($this->getHost());
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try {
			return $this->getUsernameHostString();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getDataType(): string{
		return DATATYPE_DATABASE_USER_ROLE;
	}

	public static function getPrettyClassNames():string{
		return _("Roles");
	}

	public static function getPhylumName(): string{
		return "roles";
	}

	public function setUsername(string $value):string{
		return $this->setColumnValue("User", $value);
	}

	public function hasUsername():bool{
		return $this->hasColumnValue("User");
	}

	public function getUsername():string{
		return $this->getColumnValue("User");
	}

	public function user($name): DatabaseRoleData{
		$this->setUsername($name);
		return $this;
	}

	public function hasHost():bool{
		return $this->hasColumnValue("Host");
	}

	public function getHost(){
		$f = __METHOD__;
		if (! $this->hasHost()) {
			Debug::error("{$f} host is undefined");
		}
		return $this->getColumnValue("Host");
	}

	public function setHost($host){ // XXX validate host
		$f = __METHOD__;
		if ($host == null) {
			$this->ejectColumnValue("Host");
			return null;
		} elseif (! is_string($host)) {
			Debug::error("{$f} host must be a string");
		}
		return $this->setColumnValue("Host", $host);
	}

	public function at($host): DatabaseRoleData{
		$this->setHost($host);
		return $this;
	}

	public function hasAuthPlugin():bool{
		return $this->hasColumnValue("plugin");
	}

	public function getAuthPlugin(){
		$f = __METHOD__;
		if (! $this->hasAuthPlugin()) {
			Debug::error("{$f} auth plugin is undefined");
		}
		return $this->getColumnValue("plugin");
	}

	public function setAuthPlugin($authPlugin){
		$f = __METHOD__;
		if ($authPlugin == null) {
			$this->ejectColumnValue("plugin");
			return null;
		} elseif (! is_string($authPlugin)) {
			Debug::error("{$f} auth plugin must be a string");
		}
		return $this->setColumnValue("plugin", $authPlugin);
	}

	public function identifiedWith($authPlugin): DatabaseRoleData{
		$this->setAuthPlugin($authPlugin);
		return $this;
	}

	public static function getPermissionStatic(string $name, $data){
		return FAILURE;
	}
}
