<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

interface ReplacementKeyRequestableInterface{
	
	public function getReplacementKeyRequested():bool;
	
	public function requestReplacementDecryptionKey():int;
	
	public function fulfillReplacementKeyRequest():int;
}
