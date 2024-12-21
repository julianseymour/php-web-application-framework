<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

trait ReplacementKeyRequestableTrait{
	
	public function getReplacementKeyRequested():bool{
		return $this->hasColumn("replacementKeyRequested") && $this->getColumnValue("replacementKeyRequested");
	}
	
	/**
	 * call this to request a replacement decryption key (needed after hard password reset)
	 * to be filfilled by some other samaritan who has access to that key
	 *
	 * @return int
	 */
	public function requestReplacementDecryptionKey():int{
		$f = __METHOD__;
		try{
			$print = false;
			$this->setObjectStatus(ERROR_REPLACEMENT_KEY_REQUESTED);
			if($this->getReplacementKeyRequested()){
				if($print){
					Debug::print("{$f} replacement key was already requested");
				}
				return $this->getObjectStatus();
			}elseif($print){
				Debug::print("{$f} replacement key was not already requested");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$column = $this->getColumn("replacementKeyRequested");
			$column->setValue(true);
			$column->setUpdateFlag(true);
			$status = $this->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully wrote key replacement request");
			}
			return $this->getObjectStatus();
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function fulfillReplacementKeyRequest():int{
		$f = __METHOD__;
		try{
			$this->setColumnValue("replacementKeyRequested", 0);
			$replica = $this->replicate();
			$replica->setReceptivity(DATA_MODE_RECEPTIVE);
			foreach(array_keys($this->getColumns()) as $column_name){
				$replica->setColumnValue($column_name, $this->getColumnValue($column_name));
				$column = $replica->getColumn($column_name);
				$column->setUpdateFlag(true);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $replica->update($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} updating replica returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}