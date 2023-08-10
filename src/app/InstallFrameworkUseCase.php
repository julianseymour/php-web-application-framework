<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\generateSpecialTemplateKey;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UsernameData;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\EncryptedDatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\grant\DatabasePrivilege;
use JulianSeymour\PHPWebApplicationFramework\query\routine\CreateRoutineStatement;
use JulianSeymour\PHPWebApplicationFramework\query\user\DatabaseUserDefinition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;
use mysqli;
use ReflectionClass;
use function JulianSeymour\PHPWebApplicationFramework\array_keys_exist;

class InstallFrameworkUseCase extends UseCase{

	public final function insertSpecialTemplatePlaceholders(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$placeholders = mods()->getSpecialTemplateClasses();
			if (empty($placeholders)) {
				if ($print) {
					Debug::print("no special template classes");
				}
				return SUCCESS;
			}
			foreach ($placeholders as $pc) {
				$p = new $pc();
				$p->setIdentifierValue(generateSpecialTemplateKey($pc));
				// $p->setOnDuplicateKeyUpdateFlag(true);
				if ($p->preventDuplicateEntry($mysqli) !== SUCCESS) {
					Debug::warning("already inserted a special template placeholder for class \"{$pc}\"");
					continue;
				}
				$status = $p->insert($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("inserting placeholder returned error status \"{$err}\"");
					return $status;
				}
			}
			if ($print) {
				Debug::print("inserted special template placeholders");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public final function createStoredRoutines(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$routines = mods()->getStoredRoutines();
			if (empty($routines)) {
				if ($print) {
					Debug::print("there are no stored routines to create");
				}
				return SUCCESS;
			}
			foreach ($routines as $r) {
				$name = $r->getName();
				$type = $r->getRoutineType();
				if ($print) {
					Debug::print("{$type} {$name}");
				}
				switch ($type) {
					case ROUTINE_TYPE_FUNCTION:
						$drop = QueryBuilder::dropFunctionIfExists($name);
						break;
					case ROUTINE_TYPE_PROCEDURE:
						$drop = QueryBuilder::dropProcedureIfExists($name);
						break;
					default:
						Debug::error("invalid routine type \"{$type}\"");
				}
				if ($print) {
					$qs = $drop->toSQL();
					Debug::print("about to execute query \"{$qs}\"");
				}
				$status = $drop->executeGetStatus($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("dropping routine \"{$name}\" returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully executed drop statement for routine \"{$name}\"");
				}
				$create = new CreateRoutineStatement($r);
				$create->setDeterministicFlag(true);
				if ($print) {
					$qs = $create->toSQL();
					Debug::print("about to execute query \"{$qs}\"");
				}
				$status = $create->executeGetStatus($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("creating routine \"{$name}\" returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully executed create statement for routine \"{$name}\"");
				}
			}
			if ($print) {
				Debug::print("returning successfully");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function generateLocales():int{
		$uc = new MergeGettextMessageFilesUseCase($this);
		$uc->validateTransition();
		return $uc->execute();
	}
	
	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				// Debug::print("entered. About to attempt mysqli connection");
			}
			global $argv;
			$parsed = [];
			$status = config()->beforeInstallHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("beforeInstallHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("beforeInstallHook returned success");
				Debug::printArray($argv);
				if (strstr($argv[1], PHP_EOL)) {
					Debug::error("string includes a linebreak");
				}
			}
			//1. combine .po files and convert to binary
			$this->generateLocales();
			parse_str($argv[1], $parsed);
			$install_password = str_replace('_', '+', $parsed['install_password']);
			if($print){
				Debug::print("install password is \"{$install_password}\"");
			}
			$mysqli = mysqli_connect("localhost", "installer", $install_password, "mysql");
			if (! isset($mysqli)) {
				Debug::print("failed to connect to mysqli");
				$mysqli->close();
				return $this->setObjectStatus(- 18);
			} elseif ($mysqli->error) {
				Debug::print("mysqli connection error");
				$mysqli->close();
				return $this->setObjectStatus(- 21);
			} elseif ($print) {
				// Debug::print("connected to database. About to create database user definitions.");
			}
			db()->setConnection($mysqli);
			app()->advanceExecutionState(EXECUTION_STATE_AUTHENTICATED);
			// 2. Create public read/write user credentials
			$reader_public = DatabaseUserDefinition::create()->user("reader-public")->at("localhost")->by(PUBLIC_READER_PASSWORD);
			$writer_public = DatabaseUserDefinition::create()->user("writer-public")->at("localhost")->by(PUBLIC_WRITER_PASSWORD);
			$status = QueryBuilder::createUser($reader_public, $writer_public)->executeGetStatus($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("creating users returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully created database users");
			}
			// 3, Create databases
			$classes = mods()->getDataStructureClasses();
			if ($print) {
				Debug::printArray($classes);
			}
			$temp = [];
			foreach ($classes as $index => $value) {
				if (is_array($value)) {
					if ($print) {
						// Debug::printArray($class);
					}
					array_push($temp, ...array_values($value));
				} else {
					array_push($temp, $value);
				}
			}
			$classes = $temp;
			if (emptY($classes)) {
				Debug::error("data structure classes array is empty");
			} elseif ($print) {
				Debug::printArray($classes);
			}
			$databases = [
				"embedded",
				"events",
				"intersections"
			];
			foreach ($classes as $dsc) {
				$db_name = $dsc::getDatabaseNameStatic();
				if (false != array_search($db_name, $databases)) {
					continue;
				}
				array_push($databases, $db_name);
			}
			foreach ($databases as $db_name) {
				$status = QueryBuilder::createDatabase($db_name)->executeGetStatus($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("creating database {$db_name} returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully created database \"{$db_name}\"");
				}
			}
			// 4a. create tables
			foreach ($classes as $dsc) {
				if ($print) {
					// Debug::print("about to create table for data structure class \"{$dsc}\"");
				}
				$dummy = new $dsc();
				$status = $dummy->createTable($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("creating table for data structure class \"{$dsc}\" returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully created table for data structure class \"{$dsc}\"");
				}
			}
			// 4b. create associated tables
			foreach ($classes as $dsc) {
				if ($print) {
					// Debug::print("about to create associated tables for data structure class \"{$dsc}\"");
				}
				$dummy = new $dsc();
				$status = $dummy->createAssociatedTables($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("creating associated tables for data structure class \"{$dsc}\" returned error status \"{$err}\"");
					$mysqli->close();
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully created associated tables for data structure class \"{$dsc}\"");
				}
			}
			// 5a. Create administrator account
			$admin_class = config()->getAdministratorClass();
			$admin = new $admin_class(ALLOCATION_MODE_SUBJECTIVE);
			$admin->setReceptivity(DATA_MODE_RECEPTIVE);
			$username = base64_decode(str_replace('_', '+', $parsed['username']));
			$admin->setName($username);
			$password = base64_decode(str_replace('_', '+', $parsed['password']));
			Debug::print("ultra secret confidential admin password is \"{$password}\"");
			$admin->processPasswordData(PasswordData::generate($password));
			$email = base64_decode(str_replace('_', '+', $parsed['email']));
			$admin->setEmailAddress($email);
			$admin->generateKey();
			$admin->setPermission(DIRECTIVE_INSERT, SUCCESS);
			$admin->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			app()->setUserData($admin);
			$status = $admin->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("inserting administrator returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted administrator");
			}
			// 5b. create and insert admin username data
			$username = new UsernameData();
			$username->setUserData($admin);
			$username->setName($admin->getName());
			$username->setPermission(DIRECTIVE_INSERT, SUCCESS);
			$status = $username->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("inserting admin username returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted admin username data");
			}
			// 5c. update administrator's username key
			$admin->setUsernameKey($username->getIdentifierValue());
			$status = $admin->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("updating admin username key returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully updated administrator's username key");
			}
			// 6a. Encrypt private credentials for administrator
			$admin_reader = new AdminReadCredentials();
			$admin_reader->setUserData($admin);
			$admin_reader->setName($admin_reader->getName());
			$admin_reader->setReceptivity(DATA_MODE_RECEPTIVE);
			$admin_reader->setPassword(EncryptedDatabaseCredentials::generateMysqlPassword());
			$admin_reader->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			$status = $admin_reader->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("inserting admin read credentials returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted encrypted reader credentials");
			}
			$admin_writer = new AdminWriteCredentials();
			$admin_writer->setUserData($admin);
			$admin_writer->setName($admin_writer->getName());
			$admin_writer->setReceptivity(DATA_MODE_RECEPTIVE);
			$admin_writer->setPassword(EncryptedDatabaseCredentials::generateMysqlPassword());
			$admin_writer->setInsertIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			$status = $admin_writer->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("Inserting admin write credentials returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted encrypted writer credentials");
			}
			// 6b. create admin user credentials
			$admin_reader_user = DatabaseUserDefinition::create()->user($admin_reader->getName())->at("localhost")->by($admin_reader->getPassword());
			$admin_writer_user = DatabaseUserDefinition::create()->user($admin_writer->getName())->at("localhost")->by($admin_writer->getPassword());
			$status = QueryBuilder::createUser($admin_reader_user, $admin_writer_user)->executeGetStatus($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("Unable to create administrative users: got error message \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully created administrative user accounts");
			}
			// 7a. Authorize read credentials
			foreach ([
				$reader_public,
				$writer_public,
				$admin_reader_user,
				$admin_writer_user
			] as $credentials) {
				$grant = QueryBuilder::grant()->withPrivileges(new DatabasePrivilege(DIRECTIVE_SELECT))->onTable("*", "*")->to($credentials);
				$status = $grant->executeGetStatus($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("granting select privileges failed for user " . $credentials->getUsername());
					return $this->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("successfully granted select privileges to user " . $credentials->getUsername());
				}
			}
			// 7b. authorize write credentials
			$arr = mods()->getGrantArray();
			foreach ($arr as $name => $tablegrants) {
				$user = DatabaseUserDefinition::create()->user($name)->at("localhost");
				foreach ($tablegrants as $dsc => $grants) {
					$privileges = [];
					foreach ($grants as $g) {
						array_push($privileges, new DatabasePrivilege($g));
					}
					if (is_string($dsc)) {
						if (class_exists($dsc)) {
							$db = $dsc::getDatabaseNameStatic();
							$table = $dsc::getTableNameStatic();
						} elseif (ends_with($dsc, ".*")) {
							$db = substr($dsc, 0, strlen($dsc) - 2);
							$table = "*";
						} else {
							Debug::error("string is \"{$dsc}\". please use data structure classes to index tables for grant arrays");
						}
					} else {
						Debug::error("data structure class is not a string");
					}
					$grant = QueryBuilder::grant()->withPrivileges($privileges)
						->onTable($db, $table)
						->to($user);
					$status = $grant->executeGetStatus($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("executing grant statement \"" . $grant->toSQL() . "\" returned error status \"{$err}\"");
						$mysqli->close();
						return $this->setObjectStatus($status);
					} elseif ($print) {
						Debug::print("successfully granted " . implode(',', $grants) . " on {$dsc} to {$name}");
					}
				}
			}
			// 8. insert server keypair
			$kp = new ServerKeypair();
			$password_data = PasswordData::generate(base64_encode(random_bytes(32)));
			$kp->setReceptivity(DATA_MODE_PASSIVE);
			$kp->setIpAddress(SERVER_PUBLIC_IP_ADDRESS);
			$kp->setPublicKey($password_data->getPublicKey());
			$kp->setSignaturePublicKey($password_data->getSignaturePublicKey());
			$kp->setPrivateKey($password_data->getPrivateKey());
			$kp->setSignaturePrivateKey($password_data->getSignaturePrivateKey());
			$kp->setServerType(SERVER_TYPE_MONOLITHIC);
			$kp->setCurrentServer(true);
			$kp->setServerDomain(DOMAIN_LOWERCASE);
			$kp->setName(WEBSITE_NAME);
			$kp->setPermission(DIRECTIVE_INSERT, SUCCESS);
			$kp->setUserData($admin);
			$status = $kp->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("inserting server keypair returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted server keypair");
			}
			// 9. insert special template placeholders
			$status = $this->insertSpecialTemplatePlaceholders($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("insertSpecialTemplatePlaceholders returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully inserted special template placeholders");
			}
			// 10. create stored routines
			$status = $this->createStoredRoutines($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("createStoredRoutines returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully created stored routines");
			}
			// 11. afterInstallHook for user to define their own post-installation behavior
			$status = config()->afterInstallHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("afterInstallHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("afterInstallHook returned success");
			}
			// 12. delete user
			$status = QueryBuilder::dropUser(DatabaseUserDefinition::create()->user("installer")->at("localhost"))->executeGetStatus($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("deleting install user returned error status \"{$err}\"");
				$mysqli->close();
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("successfully deleted install user");
			}
			$mysqli->close();
			if ($print) {
				Debug::print("returning successfully");
			}
			return SUCCESS;
		} catch (Exception $x) {
			Debug::warning("exception thrown: " . $x->__toString());
			return FAILURE;
		}
	}

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/install";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
}
