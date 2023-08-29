<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

class AdminReadCredentials extends EncryptedDatabaseCredentials
{

	public function getName():string{
		return "reader-admin";
	}
}
