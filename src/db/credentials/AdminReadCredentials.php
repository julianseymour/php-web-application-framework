<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

class AdminReadCredentials extends EncryptedDatabaseCredentials
{

	public function getName()
	{
		return "reader-admin";
	}
}
