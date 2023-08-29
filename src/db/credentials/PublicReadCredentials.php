<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

/**
 * credentials that belong to anyone and can only read from the database
 */
class PublicReadCredentials extends PublicDatabaseCredentials{

	public function getPassword():string{
		return PUBLIC_READER_PASSWORD;
	}

	public function getName():string{
		return "reader-public";
	}
}
