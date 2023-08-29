<?php

namespace JulianSeymour\PHPWebApplicationFramework\db\credentials;

/**
 * credentials that belong to anyone and can only read from the database
 */
class PublicWriteCredentials extends PublicDatabaseCredentials{

	public function getPassword():string{
		return PUBLIC_WRITER_PASSWORD;
	}

	public function getName():string{
		return "writer-public";
	}
}
