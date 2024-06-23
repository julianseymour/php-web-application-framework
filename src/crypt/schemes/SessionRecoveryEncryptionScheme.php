<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use JulianSeymour\PHPWebApplicationFramework\crypt\EncryptionComponentDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\NonceDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

class SessionRecoveryEncryptionScheme extends SymmetricEncryptionScheme{

	public function generateComponents(?DataStructure $ds = null): array{
		$components = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$server_secret = new EncryptionComponentDatum("{$vn}ServerSecret");
		$argon_nonce = new NonceDatum("{$vn}ArgonNonce");
		array_push($components, $server_secret, $argon_nonce);
		return $components;
	}

	protected function extractTranscryptionKey(Datum $datum):?string{
		$vn = $datum->getName();
		$xor = new XorEncryptionScheme();
		$ds = $datum->getDataStructure();
		$secret = $ds->getColumnValue("{$vn}ServerSecret");
		$secret64 = base64_encode($secret);
		$hash = $ds->getArgonHash();
		$hash64 = base64_encode($hash);
		$key = $xor->decrypt($secret, $hash);
		deallocate($xor);
		$key64 = base64_encode($key);
		$arr = [
			"secret64" => $secret64,
			"hash64" => $hash64,
			"key64" => $key64
		];
		return $key;
	}
}
