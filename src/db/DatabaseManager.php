<?php

namespace JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_file_line;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\AdminWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\DatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\EncryptedDatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use Redis;
use mysqli;

class DatabaseManager extends Basic{

	protected $beforeResponseAccessCount;

	protected $afterResponseAccessCount;

	/**
	 * active connection to the database
	 *
	 * @var mysqli
	 */
	protected $connection;

	/**
	 * ID of the currently pending database transaction
	 *
	 * @var string
	 */
	protected $pendingTransactionId;

	protected $redisConnection;

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->connection, $deallocate);
		$this->release($this->pendingTransactionId, $deallocate);
		$this->release($this->redisConnection, $deallocate);
	}
	
	public function access():int{
		$f = __METHOD__;
		$print = false;
		if(!isset($this->beforeResponseAccessCount) || ! is_int($this->beforeResponseAccessCount)){
			$this->beforeResponseAccessCount = 0;
		}
		if(app()->getExecutionState() < EXECUTION_STATE_RESPONDED){
			$this->beforeResponseAccessCount++;
			if($print){
				Debug::printStackTraceNoExit("{$f} {$this->beforeResponseAccessCount} database accesses before response");
			}
			return $this->beforeResponseAccessCount;
		}
		if(!isset($this->afterResponseAccessCount) || ! is_int($this->afterResponseAccessCount)){
			$this->afterResponseAccessCount = 0;
		}
		$this->afterResponseAccessCount ++;
		if($print){
			Debug::print("{$f} {$this->beforeResponseAccessCount} database accesses before response, and {$this->afterResponseAccessCount} database accesses after response.");
		}
		return $this->afterResponseAccessCount;
	}

	public function hasRedisConnection(): bool{
		return isset($this->redisConnection); // && $this->redisConnection instanceof Redis;
	}

	public function redis(){
		if($this->hasRedisConnection()){
			return $this->redisConnection;
		}
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);
		$redis->auth(REDIS_PASSWORD);
		return $this->redisConnection = $this->claim($redis);
	}

	public function setPendingTransactionId($id): ?string{
		if($this->hasPendingTransactionId()){
			$this->release($this->pendingTransactionId);
		}
		return $this->pendingTransactionId = $this->claim($id);
	}

	public function hasPendingTransactionId():bool{
		return isset($this->pendingTransactionId);
	}

	public function getPendingTransactionId(): ?string{
		$f = __METHOD__;
		if(!$this->hasPendingTransactionId()){
			Debug::error("{$f} pending database transaction ID is undefined");
		}
		return $this->pendingTransactionId;
	}

	public function beginTransaction(mysqli $mysqli, $id, $flags = null){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasPendingTransactionId()){
			if($print){
				Debug::printStackTraceNoExit("{$f} setting pending transaction ID to \"{$id}\"");
			}
			$this->setPendingTransactionId($id);
			if($flags){
				$begun = $mysqli->begin_transaction($flags);
			}else{
				$begun = $mysqli->begin_transaction();
			}
			if(!$begun){
				Debug::error("{$f} failed to begin transaction: \"" . $mysqli->error . "\"");
			}
		}elseif($print){
			Debug::print("{$f} application instance already has an open database transaction");
		}
		return $mysqli;
	}

	public function flushCurrentTransaction(mysqli $mysqli){
		return $this->commitTransaction($mysqli, $this->getPendingTransactionId());
	}

	public function commitTransaction(mysqli $mysqli, ?string $id = null): mysqli{
		$f = __METHOD__;
		$print = false;
		if($id === null){
			$id = $this->hasPendingTransactionId() ? $this->getPendingTransactionId() : null;
		}
		if($this->hasPendingTransactionId()){
			$pendingId = $this->getPendingTransactionId();
		}else{
			$pendingId = null;
		}
		if($pendingId === null || $id === $pendingId){
			if($print){
				Debug::printStackTraceNoExit("{$f} about to commit transactions...");
			}
			$mysqli->commit();
			if($pendingId !== null){
				$this->setPendingTransactionId(null);
			}
		}elseif($print){
			Debug::print("{$f} transaction ID \"{$id}\" differs from pending database transaction ID \"{$pendingId}\"");
		}
		return $mysqli;
	}

	public function commitCurrentTransaction(mysqli $mysqli){
		if($this->hasPendingTransactionId()){
			return $this->commitTransaction($mysqli, $this->getPendingTransactionId());
		}
		return $mysqli;
	}

	public function rollbackTransaction(mysqli $mysqli){
		$f = __METHOD__;
		$print = false;
		if($this->hasPendingTransactionId()){
			if($print){
				Debug::print("{$f} rolling back pending database transaction");
			}
			$mysqli->rollback();
			$this->setPendingTransactionId(null);
		}elseif($print){
			Debug::print("{$f} no pending transaction to roll back");
		}
		return null;
	}

	public function reconnect($credentials = null): ?mysqli{
		$f = __METHOD__;
		if($this->disconnect()){
			return $this->connect($credentials);
		}
		Debug::error("{$f} disconnection failed");
	}

	public static function detectCredentialsClass(): string{
		$f = __METHOD__;
		$print = false;
		$post = Request::getHTTPRequestMethod() === HTTP_REQUEST_METHOD_POST;
		$admin = app()->hasUserData() && user() instanceof Administrator;
		if($admin){
			if($post){
				if($print){
					Debug::print("{$f} user is an administrator; this is a POST request");
				}
				return AdminWriteCredentials::class;
			}else{
				if($print){
					Debug::print("{$f} user is an administrator; this is a GET request");
				}
				return AdminReadCredentials::class;
			}
		}elseif($post){
			if($print){
				Debug::print("{$f} user is not an administrator; this is a POST request");
			}
			return PublicWriteCredentials::class;
		}elseif($print){
			Debug::print("{$f} user is not an administrator; this is a GET request");
		}
		return PublicReadCredentials::class;
	}

	public function connect($credentials = null): ?mysqli{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasConnection()){
				Debug::error("{$f} connection is already defined; use getConnection instead");
			}
			// get database credentials object
			if($credentials === null){
				$credentials = static::detectCredentialsClass();
			}elseif($print){
				Debug::print("{$f} credentials are something other than null");
			}
			if(is_string($credentials) && is_a($credentials, DatabaseCredentials::class, true)){
				if($print){
					Debug::print("{$f} instantiating credentials of class \"{$credentials}\"");
				}
				$credentials = new $credentials();
				$deallocate = true;
			}elseif(!$credentials instanceof DatabaseCredentials){
				Debug::error("{$f} received credentials must be of class DatabaseCredentials or a string class name thereof");
			}else{
				if($print){
					Debug::print("{$f} received an existing DatabaseCredentials object");
				}
				$deallocate = false;
			}
			$host = "localhost";
			$username = $credentials->getName();
			if(empty($username)){
				$credentials_class = $credentials->getClass();
				Debug::error("{$f} username is undefined for credentials of class \"{$credentials_class}\"");
			}
			// load encrypted credentials if they have not been loaded already
			if($credentials instanceof EncryptedDatabaseCredentials && !$credentials->getLoadedFlag()){
				$tempc = new PublicReadCredentials();
				$mysqli = new mysqli($host, $tempc->getName(), $tempc->getPassword());
				deallocate($tempc);
				$this->access();
				if(!isset($mysqli)){
					Debug::error("{$f} mysqli object is undefined");
				}elseif($print){
					Debug::print("{$f} about to load mysqli credentials for user \"{$username}\" from database");
				}
				$status = $credentials->load($mysqli, "name", $username);
				if($status !== SUCCESS){
					$mysqli->close();
					unset($mysqli);
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} error loading credentials \"{$username}\" from database: got abnormal failure status \"{$err}\"");
					if($deallocate){
						deallocate($credentials);
					}
					return null;
				}elseif($credentials->isRegistrable()){
					if($print){
						Debug::print("{$f} registering registrable credentials");
					}
					registry()->update($credentials->getIdentifierValue(), $credentials);
				}elseif($print){
					Debug::print("{$f} successfully loaded credentials. They are non-registrable");
				}
				$mysqli->close();
				unset($mysqli);
			}elseif($print){
				Debug::print("{$f} credentials are not encrypted or have already been loaded");
			}
			// connect
			$password = $credentials->getPassword();
			if($deallocate){
				deallocate($credentials);
			}
			if(empty($password)){
				Debug::error("{$f} password for '{$username}'@'{$host}' is null or empty string");
			}elseif($print){
				Debug::print("{$f} about to create a new mysqli object for user '{$username}'@'{$host}' with password '{$password}'");
			}
			$db = "data";
			$mysqli = new mysqli($host, $username, $password, $db);
			if($mysqli->connect_error){
				Debug::error("{$f} error creating new mysqli link for user \"{$username}\" with password \"password\": {$mysqli->connect_error}");
				return null;
			}elseif($print){
				Debug::print("{$f} successfully created mysqli object; about to select database \"{$db}\"");
			}
			$this->access();
			if(!$mysqli->select_db($db)){
				Debug::error("{$f} select database error: \"{$mysqli->error}\"");
			}elseif($mysqli->connect_errno){
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}){$mysqli->connect_error}");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return db()->rollbackTransaction($mysqli);
			}elseif(!$mysqli->ping()){
				Debug::error("{$f} mysqli connection failed ping test: \"{$mysqli->error}\"");
				$this->setObjectStatus(ERROR_MYSQL_CONNECT);
				return null;
			}elseif($print){
				$called = get_file_line([
					"connect",
					"reconnect",
					"getConnection"
				]);
				Debug::printStackTraceNoExit("{$f} connected at {$called}");
			}
			return $this->setConnection($mysqli);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasConnection(): bool{
		return isset($this->connection) && $this->connection instanceof mysqli;
	}

	public function setConnection(?mysqli $mysqli):?mysqli{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if($this->hasConnection()){
			$this->release($this->connection);
		}
		return $this->connection = $this->claim($mysqli);
	}

	public function getConnection($credentials = null): ?mysqli{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasConnection()){
			if($print){
				Debug::print("{$f} connecting");
			}
			return $this->connect($credentials);
			Debug::error("{$f} database connection is undefined");
		}elseif($print){
			Debug::print("{$f} already connected");
		}
		return $this->connection;
	}

	public function disconnect(): bool{
		$f = __METHOD__;
		if(!$this->hasConnection()){
			Debug::error("{$f} database connection is undefined");
		}
		$ret = $this->getConnection()->close();
		$this->setConnection(null);
		return $ret;
	}
}
