<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\table\MultipleTableNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;

class FlushStatement extends QueryStatement{

	use LocalFlagBearingTrait;
	use MultipleTableNamesTrait;

	public function __construct(){
		parent::__construct();
		$this->requirePropertyType("channelRelayLogs", 's');
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"binary logs",
			"engine logs",
			"error logs",
			"export",
			"general logs",
			"hosts",
			"logs",
			"optimizer costs",
			"privileges",
			"read lock",
			"relay logs",
			"slow logs",
			"status",
			"tables",
			"user_resources"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"binary logs",
			"engine logs",
			"error logs",
			"export",
			"general logs",
			"hosts",
			"logs",
			"optimizer costs",
			"privileges",
			"read lock",
			"relay logs",
			"slow logs",
			"status",
			"tables",
			"user_resources"
		]);
	}
	
	public function setBinaryLogsFlag(bool $value = true):bool{
		return $this->setFlag('binary logs', $value);
	}

	public function getBinaryLogsFlag():bool{
		return $this->getFlag('binary logs');
	}

	public function binaryLogs(bool $value=true):FlushStatement{
		$this->setBinaryLogsFlag($value);
		return $this;
	}

	public function setEngineLogsFlag(bool $value = true):bool{
		return $this->setFlag('engine logs', $value);
	}

	public function getEngineLogsFlag():bool{
		return $this->getFlag('engine logs');
	}

	public function engineLogs(bool $value=true):FlushStatement{
		return $this->withFlag("engine logs", $value);
	}

	public function setErrorLogsFlag(bool $value = true):bool{
		return $this->setFlag('error logs', $value);
	}

	public function getErrorLogsFlag():bool{
		return $this->getFlag('error logs');
	}

	public function errorLogs(bool $value=true):FlushStatement{
		return $this->withFlag("error logs", $value);
	}

	public function setGeneralLogsFlag(bool $value = true):bool{
		return $this->setFlag('general logs', $value);
	}

	public function getGeneralLogsFlag():bool{
		return $this->getFlag('general logs');
	}

	public function generalLogs(bool $value=true):FlushStatement{
		return $this->withFlag("general logs", $value);
	}

	public function setHostsFlag(bool $value = true):bool{
		return $this->setFlag('hosts', $value);
	}

	public function getHostsFlag():bool{
		return $this->getFlag('hosts');
	}

	public function hosts(bool $value=true):FlushStatement{
		return $this->withFlag("hosts", $value);
	}

	public function setLogsFlag(bool $value = true):bool{
		return $this->setFlag('logs', $value);
	}

	public function getLogsFlag():bool{
		return $this->getFlag('logs');
	}

	public function logs(bool $value=true):FlushStatement{
		return $this->withFlag('logs', $value);
	}

	public function setPrivilegesFlag(bool $value = true):bool{
		return $this->setFlag('privileges', $value);
	}

	public function getPrivilegesFlag():bool{
		return $this->getFlag('privileges');
	}

	public function privileges(bool $value=true):FlushStatement{
		return $this->withFlag("privileges", $value);
	}

	public function setOptimizerCostsFlag(bool $value = true):bool{
		return $this->setFlag("optimizer costs", $value);
	}

	public function getOptimizerCostsFlag():bool{
		return $this->getFlag("optimizer costs");
	}

	public function optimizerCosts(bool $value=true):FlushStatement{
		return $this->withFlag("optimizer costs", $value);
	}

	public function setRelayLogsFlag(bool $value = true):bool{
		return $this->setFlag("relay logs", $value);
	}

	public function getRelayLogsFlag():bool{
		return $this->getFlag("relay logs");
	}

	public function relayLogs(...$values):FlushStatement{
		if(isset($values)){
			$this->setChannelRelayLogs($values);
		}
		return $this->withFlag("relay logs", true);
	}

	public function setSlowLogsFlag(bool $value = true):bool{
		return $this->setFlag("slow logs", $value);
	}

	public function getSlowLogsFlag():bool{
		return $this->getFlag("slow logs");
	}

	public function slowLogs(bool $value=true):FlushStatement{
		return $this->withFlag("slow logs", $value);
	}

	public function setStatusFlag(bool $value = true):bool{
		return $this->setFlag("status", $value);
	}

	public function getStatusFlag():bool{
		return $this->getFlag("status");
	}

	public function setUserResourcesFlag(bool $value = true):bool{
		return $this->setFlag("user_resources", $value);
	}

	public function getUserResourcesFlag():bool{
		return $this->getFlag("user_resources");
	}

	public function userResources(bool $value=true):FlushStatement{
		return $this->withFlag("user_resources", $value);
	}

	public function setTablesFlag(bool $value = true):bool{
		return $this->setFlag("tables", $value);
	}

	public function getTablesFlag():bool{
		return $this->getFlag("tables");
	}

	public function setReadLockFlag(bool $value = true):bool{
		return $this->setFlag("read lock", $value);
	}

	public function getReadLockFlag():bool{
		return $this->getFlag("read lock");
	}

	public function withReadLock(bool $value=true):FlushStatement{
		return $this->withFlag("read lock", $value);
	}

	public function setExportFlag(bool $value = true):bool{
		$f = __METHOD__;
		if($value && ! $this->hasTableNames()){
			Debug::error("{$f} do not call this function unless table names have already been defined");
		}
		return $this->setFlag("export", $value);
	}

	public function getExportFlag():bool{
		return $this->getFlag("export");
	}

	public function forExport(bool $value=true):FlushStatement{
		return $this->withFlag("export", $value);
	}

	public function setChannelRelayLogs($values){
		return $this->setArrayProperty("channelRelayLogs", $values);
	}

	public function hasChannelRelayLogs():bool{
		return $this->hasArrayProperty("channelRelayLogs");
	}

	public function pushChannelRelayLogs(...$values):int{
		return $this->pushArrayProperty("channelRelayLogs", ...$values);
	}

	public function mergeChannelRelayLogs($values){
		return $this->mergeArrayProperty("channelRelayLogs", $values);
	}

	public function getChannelRelayLogs(){
		return $this->getProperty("channelRelayLogs");
	}

	public function getChannelRelayLogsCount():int{
		return $this->getArrayPropertyCount("channelRelayLogs");
	}

	public function getQueryStatementString(): string{
		$f = __METHOD__;
		// FLUSH
		$string = "flush ";
		// [NO_WRITE_TO_BINLOG | LOCAL]
		if($this->getLocalFlag()){
			$string .= "local ";
		}
		if($this->getTablesFlag()){
			// tables_option: {
			// TABLES
			// | TABLES tbl_name [, tbl_name] ...
			// | TABLES WITH READ LOCK
			// | TABLES tbl_name [, tbl_name] ... WITH READ LOCK
			// | TABLES tbl_name [, tbl_name] ... FOR EXPORT
			// }
			$string .= "tables";
			if($this->hasTableNames()){
				$string .= " ";
				$i = 0;
				foreach($this->getTableNames() as $table){
					if($i++ > 0){
						$string .= ",";
					}
					if($table instanceof SQLInterface){
						$string .= $table->toSQL();
					}elseif(is_string($table) || $table instanceof StringifiableInterface){
						$string .= $table;
					}else{
						Debug::error("{$f} invalid table name");
					}
				}
			}
			if($this->getReadLockFlag()){
				$string .= " with read lock";
			}elseif($this->getExportFlag()){
				$string .= " for export";
			}
		}else{
			// BINARY LOGS
			if($this->getBinaryLogsFlag()){
				$string .= " binary logs";
			}
			// | ENGINE LOGS
			if($this->getEngineLogsFlag()){
				$string .= " engine logs";
			}
			// | ERROR LOGS
			if($this->getErrorLogsFlag()){
				$string .= " error logs";
			}
			// | GENERAL LOGS
			if($this->getGeneralLogsFlag()){
				$string .= " general logs";
			}
			// | HOSTS
			if($this->getHostsFlag()){
				$string .= " hosts";
			}
			// | LOGS
			if($this->getLogsFlag()){
				$string .= " logs";
			}
			// | PRIVILEGES
			if($this->getPrivilegesFlag()){
				$string .= " privileges";
			}
			// | OPTIMIZER_COSTS
			if($this->getOptimizerCostsFlag()){
				$string .= " optimizer costs";
			}
			// | RELAY LOGS [FOR CHANNEL channel]
			if($this->hasChannelRelayLogs()){
				foreach($this->getChannelRelayLogs() as $channel){
					$string .= " relay logs for {$channel}";
				}
			}elseif($this->getRelayLogsFlag()){
				$string .= " relay logs";
			}
			// | SLOW LOGS
			if($this->getSlowLogsFlag()){
				$string .= " slow logs";
			}
			// | STATUS
			if($this->getStatusFlag()){
				$string .= " status";
			}
			// | USER_RESOURCES
			if($this->getUserResourcesFlag()){
				$string .= " user_resources";
			}
		}
		return $string;
	}
}
