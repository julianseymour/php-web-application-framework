<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\generateSpecialTemplateKey;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\region_code;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UsernameData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\EncryptedDatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\language\TranslatedStringData;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\grant\DatabasePrivilege;
use JulianSeymour\PHPWebApplicationFramework\query\routine\StoredRoutine;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\user\DatabaseUserDefinition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;

class InstallFrameworkUseCase extends UseCase{

	/**
	 * Needed because objects generated in DataStructure->deriveFOreignDataStructures must maintain a constrained foreign key reference to the template from which they are derived, even if that template is not something that is stored in the database. This function is used to generate and insert stubs to fulfill that referential integrity.
	 * For example, sales tax is a complicated formula that would be extremely inconvenient to calculate with a stored function, but records of instances where sales tax is applied must still maintain a reference to the placeholder sales tax object.
	 * @param mysqli $mysqli
	 * @return int
	 */
	public final function insertSpecialTemplatePlaceholders(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$placeholders = mods()->getSpecialTemplateClasses();
			if(empty($placeholders)){
				if($print){
					Debug::print("no special template classes");
				}
				return SUCCESS;
			}
			foreach($placeholders as $pc){
				$p = new $pc();
				$p->setIdentifierValue(generateSpecialTemplateKey($pc));
				// $p->setOnDuplicateKeyUpdateFlag(true);
				if($p->preventDuplicateEntry($mysqli) !== SUCCESS){
					Debug::warning("already inserted a special template placeholder for class \"{$pc}\"");
					continue;
				}
				$status = $p->insert($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("inserting placeholder returned error status \"{$err}\"");
					return $status;
				}
			}
			if($print){
				Debug::print("inserted special template placeholders");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public final function createStoredRoutines(mysqli $mysqli): int{
		$csr = new CreateStoredRoutinesUseCase($this);
		$csr->validateTransition();
		$count = $csr->createStoredRoutines($mysqli);
		app()->setUseCase($this);
		if($count < 0){
			$this->setObjectStatus($csr->getObjectStatus());
		}
		deallocate($csr);
		return $count;
	}
	
	public function createDatabases(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false;
		//4, Create databases
		$classes = mods()->getDataStructureClasses();
		if(empty($classes)){
			Debug::error("data structure classes array is empty");
		}elseif($print){
			Debug::printArray($classes);
		}
		$databases = [
			"accounts",
			"cascading",
			"data",
			"embedded",
			"events",
			"files",
			"intersections",
			"security",
			"strings",
			"user_content",
			"usernames"
		];
		foreach($classes as $dsc){
			$db_name = $dsc::getDatabaseNameStatic();
			if(false !== array_search($db_name, $databases)){
				continue;
			}
			array_push($databases, $db_name);
		}
		foreach($databases as $db_name){
			$status = QueryBuilder::createDatabase($db_name)->executeGetStatus($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} creating database {$db_name} returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif(true || $print){
				Debug::print("Successfully created database \"{$db_name}\"");
			}
		}
		return SUCCESS;
	}
	
	public function createTables(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false;
		$classes = mods()->getDataStructureClasses();
		//5. create tables
		foreach($classes as $dsc){
			if($print){
				//Debug::print("about to create table for data structure class \"{$dsc}\"");
			}
			if($dsc::tableExistsStatic($mysqli)){
				if($print){
					Debug::print("{$f} table ".$dsc::getTableNameStatic()." already exists");
				}
				continue;
			}
			$dummy = new $dsc();
			$status = $dummy->createTable($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("creating table for data structure class \"{$dsc}\" returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif(true || $print){
				Debug::print("Successfully created table for data structure class \"{$dsc}\"");
			}
		}
		//6. create associated tables
		foreach($classes as $dsc){
			if($print){
				//Debug::print("about to create associated tables for data structure class \"{$dsc}\"");
			}
			$dummy = new $dsc();
			$status = $dummy->createAssociatedTables($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("creating associated tables for data structure class \"{$dsc}\" returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif(true || $print){
				Debug::print("Successfully created associated tables for data structure class \"{$dsc}\"");
			}
		}
		return SUCCESS;
	}
	
	public function createStringTables(mysqli $mysqli):int{
		$f = __METHOD__;
		$supported = config()->getSupportedLanguages();
		foreach($supported as $language){
			$string = new TranslatedStringData();
			$string->setTableName($language);
			$status = $string->createTable($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} creating string table for language {$language} returned error status \"{$err}\"");
				return $status;
			}
		}
		return SUCCESS;
	}
	
	public function createDirectories():int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$directories = mods()->getInstallDirectories();
		foreach($directories as $dir){
			if(is_dir($dir)){
				if($print){
					Debug::print("{$f} directory \"{$dir}\" already exists");
				}
				continue;
			}elseif($print){
				Debug::print("{$f} install directory \"{$dir}\"");
			}
			if(!starts_with($dir, '/var/www')){
				Debug::error("{$f} all directories must be inside /var/www/");
				return FAILURE;
			}
			$splat = explode('/', $dir);
			$splat = array_slice($splat, 3);
			if(empty($splat)){
				if($print){
					Debug::print("{$f} it is not necessary to make directory /var/www");
				}
				continue;
			}
			$temp = '/var/www';
			foreach($splat as $segment){
				$temp .= '/'.$segment;
				if(!is_dir($temp)){
					if($print){
						Debug::print("{$f} creating directory \"{$temp}\"");
					}
					mkdir($temp, 0777, true);
				}elseif($print){
					Debug::print("{$f} directory \"{$temp}\" was already created");
				}
			}
		}
		return SUCCESS;
	}
	
	public function execute():int{
		$f = __METHOD__;
		try{
			$print = false;
			global $argv;
			$parsed = [];
			$status = config()->beforeInstallHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("beforeInstallHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("beforeInstallHook returned success");
				Debug::printArray($argv);
				if(strstr($argv[1], PHP_EOL)){
					Debug::error("string includes a linebreak");
				}
			}
			//1. create directories
				$status = $this->createDirectories();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("createDIrectories returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully initialized databases");
				}
			//2. combine .po files and convert to binary
				MergeGettextMessageFilesUseCase::mergeGettextMessageFilesStatic();
			//3. parse mysql user credentials and connect
				parse_str($argv[1], $parsed);
				$install_password = str_replace('_', '+', $parsed['install_password']);
				if($print){
					Debug::print("install password is \"{$install_password}\"");
				}
				$mysqli = mysqli_connect("localhost", "installer", $install_password, "mysql");
				if(!isset($mysqli)){
					Debug::print("failed to connect to mysqli");
					$mysqli->close();
					return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
				}elseif($mysqli->error){
					Debug::print("mysqli connection error");
					$mysqli->close();
					return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
				}elseif($print){
					// Debug::print("connected to database. About to create database user definitions.");
				}
				db()->setConnection($mysqli);
				app()->advanceExecutionState(EXECUTION_STATE_AUTHENTICATED);
			//4. initialize databases and tables
				$status = $this->createDatabases($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("initializeDatabases returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully initialized databases");
				}
			//close and reopen database connection
				$mysqli->close();
				unset($mysqli);
				$mysqli = mysqli_connect("localhost", "installer", $install_password, "data");
				db()->setConnection($mysqli);
			//5. create string tables
				$status = $this->createStringTables($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("createStringTables returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully created string tables");
				}
			//6. create stored routines
				$count = $this->createStoredRoutines($mysqli);
				if($count < 0){
					$status = $this->getObjectStatus();
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("createStoredRoutines returned error status \"{$err}\"");
					$mysqli->close();
					return $status;
				}elseif(true || $print){
					if($count > 0){
						Debug::print("Successfully created {$count} stored routines");
					}else{
						Debug::print("No stored routines to create");
					}
				}
			//7. initialize databases and tables
				$status = $this->createTables($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("createTables returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully created tables");
				}
			//8. insert administrator profile
				$username = base64_decode(str_replace('_', '+', $parsed['username']));
				$password = base64_decode(str_replace('_', '+', $parsed['password']));
				$email = base64_decode(str_replace('_', '+', $parsed['email']));
				$admin = $this->insertAdministrator($mysqli, $username, $password, $email);
				if($admin == null){
					$status = $this->getObjectStatus();
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} creating administrator failed. Error status \"{$err}\"");
					return $status;
				}elseif(true || $print){
					Debug::print("Successfully created administrator profile");
				}
			//9. create and authorize database credentials
				$status = $this->insertDatabaseCredentials($mysqli, $admin);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} insertDatabaseCredentials returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif(true || $print){
					Debug::print("Successfully created database credentials");
				}
			//10. insert server keypair
				$status = $this->insertServerKeypair($mysqli, $admin);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} insertServerKeypair returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif(true || $print){
					Debug::print("{$f} Successfully inserted server keypair");
				}
			//11. insert special template placeholders
				$status = $this->insertSpecialTemplatePlaceholders($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("insertSpecialTemplatePlaceholders returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif(true || $print){
					Debug::print("Successfully inserted special template placeholders");
				}
			//12. afterInstallHook for user to define their own post-installation behavior
				$status = config()->afterInstallHook($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("afterInstallHook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("afterInstallHook returned success");
				}
			//13. delete user or revoke privileges;
				$installer = DatabaseUserDefinition::create()->user("installer")->at("localhost");
				if($count > 0){
					if($print){
						Debug::print("{$f} {$count} stored procedures were created. Revoking all privileges from install user");
					}
					$status = QueryBuilder::revoke()->withPrivileges(
						DIRECTIVE_ALTER_ROUTINE,
						DIRECTIVE_ALTER_TABLE,
						DIRECTIVE_CREATE_ROUTINE,
						DIRECTIVE_CREATE_TABLE,
						DIRECTIVE_CREATE_USER,
						DIRECTIVE_DELETE,
						DIRECTIVE_DROP,
						DIRECTIVE_FILE,
						DIRECTIVE_INSERT,
						DIRECTIVE_REFERENCES,
						DIRECTIVE_SELECT,
						DIRECTIVE_SUPER,
						DIRECTIVE_UPDATE,
						DIRECTIVE_GRANT_OPTION
					)->onTable("*", "*")->from($installer)->executeGetStatus($mysqli);
				}else{
					if($print){
						Debug::print("{$f} no stored procedures were created. Deleting install user");
					}
					$status = QueryBuilder::dropUser($installer)->executeGetStatus($mysqli);
				}
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("deleting install user returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully deleted install user");
				}
			$mysqli->close();
			if(true || $print){
				Debug::print("Returning successfully");
			}
			return SUCCESS;
		}catch(Exception $x){
			Debug::warning("Exception thrown: ".$x->__toString());
			return FAILURE;
		}
	}

	public function insertAdministrator(mysqli $mysqli, string $username, string $password, string $email):?Administrator{
		$f = __METHOD__;
		$print = false;
		$admin_class = config()->getAdministratorClass();
		$admin = new $admin_class(ALLOCATION_MODE_SUBJECTIVE);
		$admin->setReceptivity(DATA_MODE_RECEPTIVE);
		$admin->setName($username);
		if($print){
			Debug::print("ultra secret confidential admin password is \"{$password}\"");
		}
		$pwd = PasswordData::generate($password);
		$admin->processPasswordData($pwd);
		deallocate($pwd);
		$admin->setEmailAddress($email);
		$admin->generateKey();
		$admin->setPermission(DIRECTIVE_INSERT, SUCCESS);
		$admin->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
		$region = region_code(SERVER_PUBLIC_IP_ADDRESS);
		if($print){
			Debug::print("{$f} about to assign region code \"{$region}\"");
		}
		$admin->setRegionCode($region);
		app()->setUserData($admin);
		if(!$admin->hasRegionCode()){
			Debug::error("{$f} immediately after setting it, administrator does not have a region code");
		}elseif(!$admin->hasColumnValue("regionCode")){
			Debug::error("{$f} hasRegionCode returns true, but the column's value is undefined");
		}elseif($print){
			Debug::print("{$f} admin region code is ".$admin->getRegionCode());
		}
		$status = $admin->insert($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::print("inserting administrator returned error status \"{$err}\"");
			$mysqli->close();
			$this->setObjectStatus($status);
			return null;
		}elseif($print){
			Debug::print("successfully inserted administrator");
		}
		//8. create and insert admin username data
		$username = new UsernameData();
		$username->setUserData($admin);
		$username->setName($admin->getName());
		$username->setPermission(DIRECTIVE_INSERT, SUCCESS);
		$status = $username->insert($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("inserting admin username returned error status \"{$err}\"");
			$mysqli->close();
			$this->setObjectStatus($status);
			return null;
		}elseif($print){
			Debug::print("successfully inserted admin username data");
		}
		//9. update administrator's username key
		$admin->setUsernameKey($username->getIdentifierValue());
		$status = $admin->update($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("updating admin username key returned error status \"{$err}\"");
			$mysqli->close();
			$this->setObjectStatus($status);
			return null;
		}elseif($print){
			Debug::print("successfully updated administrator's username key");
		}
		return $admin;
	}
	
	public function insertServerKeypair(mysqli $mysqli, Administrator $admin):int{
		$print = false;
		$kp = new ServerKeypair();
		$password_data = PasswordData::generate(base64_encode(random_bytes(32)));
		$kp->setReceptivity(DATA_MODE_PASSIVE);
		$kp->setIpAddress(SERVER_PUBLIC_IP_ADDRESS);
		$kp->setPublicKey($password_data->getPublicKey());
		$kp->setSignaturePublicKey($password_data->getSignaturePublicKey());
		$kp->setPrivateKey($password_data->getPrivateKey());
		$kp->setSignaturePrivateKey($password_data->getSignaturePrivateKey());
		deallocate($password_data);
		$kp->setServerType(SERVER_TYPE_MONOLITHIC);
		$kp->setCurrentServer(true);
		$kp->setServerDomain(DOMAIN_LOWERCASE);
		$kp->setName(WEBSITE_NAME);
		$kp->setPermission(DIRECTIVE_INSERT, SUCCESS);
		$status = $kp->insert($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("inserting server keypair returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("successfully inserted server keypair");
		}
		return SUCCESS;
	}
	
	public function grantPrivileges(mysqli $mysqli):int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$arr = mods()->getGrantArray();
		if($print){
			Debug::printArray($arr);
		}
		foreach($arr as $name => $tablegrants){
			if($print){
				Debug::print("{$f} now issuing grants to user \"{$name}\"");
			}
			$user = DatabaseUserDefinition::create()->user($name)->at("localhost");
			foreach($tablegrants as $dsc => $grants){
				$privileges = [];
				foreach($grants as $g){
					array_push($privileges, new DatabasePrivilege($g));
				}
				if(is_string($dsc)){
					if(class_exists($dsc)){
						if(is_a($dsc, StoredRoutine::class, true)){
							if($print){
								Debug::print("{$f} {$dsc} is a StoredRoutine");
							}
							$grant = QueryBuilder::grant()->withPrivileges($privileges);
							$type = $dsc::getRoutineTypeStatic();
							$grant->setDatabaseName($dsc::getDatabaseNameStatic());
							switch($type){
								case ROUTINE_TYPE_FUNCTION:
									$grant->onFunction($dsc::getNameStatic());
									break;
								case ROUTINE_TYPE_PROCEDURE:
									$grant->onProcedure($dsc::getNameStatic());
									break;
								default:
									Debug::error("{$f} invalid routine type \"{$type}\"");
							}
							$grant->to($user);
							if($print){
								Debug::print("{$f} about to execute grant function \"".$grant->toSQL().'\"');
							}
							$status = $grant->executeGetStatus($mysqli);
							if($status !== SUCCESS){
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("executing grant statement \"" . $grant->toSQL() . "\" returned error status \"{$err}\"");
								$mysqli->close();
								return $this->setObjectStatus($status);
							}elseif($print){
								Debug::print("successfully granted " . implode(',', $grants) . " on {$dsc} to {$name}");
							}
							continue;
						}elseif(is_a($dsc, StaticTableNameInterface::class, true) && !method_exists($dsc, "getTableNameStatic")){
							Debug::error("{$f} class {$dsc}'s table name cannot be determined statically");
						}
						$db = $dsc::getDatabaseNameStatic();
						$table = $dsc::getTableNameStatic();
					}elseif(ends_with($dsc, ".*")){
						$db = substr($dsc, 0, strlen($dsc) - 2);
						$table = "*";
					}else{
						Debug::error("string is \"{$dsc}\". please use data structure classes to index tables for grant arrays");
					}
				}else{
					$gottype = gettype($dsc);
					Debug::error("data structure class is not a string, it's a {$gottype}");
				}
				$grant = QueryBuilder::grant()->withPrivileges($privileges)->onTable($db, $table)->to($user);
				$status = $grant->executeGetStatus($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("executing grant statement \"" . $grant->toSQL() . "\" returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully granted " . implode(',', $grants) . " on {$dsc} to {$name}");
				}
			}
			if($print){
				Debug::print("{$f} done issuing grants to user \"{$name}\"");
			}
		}
		return SUCCESS;
	}
	
	public function insertDatabaseCredentials(mysqli $mysqli, Administrator $admin):int{
		$f = __METHOD__;
		try{
			$print = false;
			// 2. Create public read/write user credentials
			$reader_public = DatabaseUserDefinition::create()->user("reader-public")->at("localhost")->by(PUBLIC_READER_PASSWORD);
			$writer_public = DatabaseUserDefinition::create()->user("writer-public")->at("localhost")->by(PUBLIC_WRITER_PASSWORD);
			$status = QueryBuilder::createUser($reader_public, $writer_public)->executeGetStatus($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("creating users returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("successfully created database users");
			}
			$admin_reader = new AdminReadCredentials();
			$admin_reader->setUserData($admin);
			$admin_reader->setName($admin_reader->getName());
			$admin_reader->setReceptivity(DATA_MODE_RECEPTIVE);
			$admin_reader->setPassword(EncryptedDatabaseCredentials::generateMysqlPassword());
			$admin_reader->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			$status = $admin_reader->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("inserting admin read credentials returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("successfully inserted encrypted reader credentials");
			}
			$admin_writer = new AdminWriteCredentials();
			$admin_writer->setUserData($admin);
			$admin_writer->setName($admin_writer->getName());
			$admin_writer->setReceptivity(DATA_MODE_RECEPTIVE);
			$admin_writer->setPassword(EncryptedDatabaseCredentials::generateMysqlPassword());
			$admin_writer->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			$status = $admin_writer->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("Inserting admin write credentials returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("successfully inserted encrypted writer credentials");
			}
			//11. create admin user credentials
			$admin_reader_user = DatabaseUserDefinition::create()->user($admin_reader->getName())->at("localhost")->by($admin_reader->getPassword());
			$admin_writer_user = DatabaseUserDefinition::create()->user($admin_writer->getName())->at("localhost")->by($admin_writer->getPassword());
			$status = QueryBuilder::createUser($admin_reader_user, $admin_writer_user)->executeGetStatus($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("Unable to create administrative users: got error message \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("successfully created administrative user accounts");
			}
			//12. Authorize read credentials
			foreach([
				$reader_public,
				$writer_public,
				$admin_reader_user,
				$admin_writer_user
			] as $credentials){
				$grant = QueryBuilder::grant()->withPrivileges(new DatabasePrivilege(DIRECTIVE_SELECT))->onTable("*", "*")->to($credentials);
				$status = $grant->executeGetStatus($mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("granting select privileges failed for user " . $credentials->getUsername());
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("successfully granted select privileges to user " . $credentials->getUsername());
				}
			}
			//13. authorize write credentials
			$status = $this->grantPrivileges($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} grantPrivileges returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully authorized write credentials");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function isPageUpdatedAfterLogin():bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/install";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
}
