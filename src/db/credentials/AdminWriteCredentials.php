<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

class AdminWriteCredentials extends EncryptedDatabaseCredentials{

	public function getName(): string{
		return "writer-admin";
	}
}
