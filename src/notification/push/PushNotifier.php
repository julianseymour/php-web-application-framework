<?php

namespace JulianSeymour\PHPWebApplicationFramework\notification\push;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationData;
use Exception;

class PushNotifier extends Basic{

	/**
	 * list of push notifications that are transmitted after the response has been returned to whomever initiated the request
	 *
	 * @var array
	 */
	protected $queue;

	/**
	 *
	 * @return NotificationData[]
	 */
	private function getQueue():array{
		$f = __METHOD__;
		if(!$this->hasQueue()){
			Debug::error("{$f} queue is empty");
		}
		return $this->queue;
	}

	public function transmitQueue(): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered");
			}
			if(empty($this->queue)){
				Debug::error("{$f} push notification queue is empty");
			}elseif($print){
				Debug::print("{$f} about to transmit push notification queue");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			if(!isset($mysqli)){
				Debug::error("{$f} mysqli object returned null");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}
			$queue = $this->getQueue();
			$i = 0;
			foreach($queue as $note){
				if($note === null){
					Debug::error("{$f} push notification is null");
				}
				$recipient = $note->getUserData();
				$status = $recipient->transmitPushNotification($mysqli, $note);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} transmitPushNotification returned error status \"{$err}\"");
				}
				$i++;
			}
			if($print){
				Debug::print("{$f} transmitted {$i} push notifications");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasQueue():bool{
		return is_array($this->queue) && !empty($this->queue);
	}

	public function enqueue(...$notes): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			foreach($notes as $note){
				if(!$note instanceof NotificationData){
					Debug::error("{$f} pushed something that is not a notification data");
				}
			}
			if(!is_array($this->queue)){
				$this->queue = [];
			}
			$pusht = array_push($this->queue, ...$notes);
			if($print){
				Debug::print("{$f} pushed {$pusht} notifications");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setQueue(?array $queue):?array{
		if($queue === null){
			unset($this->queue);
			return null;
		}
		return $this->queue = $queue;
	}
}