<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\getExecutionTime;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\HomogeneousDataCollection;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\MimeType;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;
use finfo;

class ImportCsvFilesUseCase extends SubsequentUseCase{

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$doc = $this->getPredecessor()->getDataOperandClass();
			if(empty($doc)){
				Debug::error("{$f} data operand class is undefined");
			}elseif($print){
				Debug::print("{$f} data operand class is \"{$doc}\"");
			}
			if(! request()->hasRepackedIncomingFiles()){
				Debug::error("{$f} request has no repacked incoming files");
				return $this->setObjectStatus(ERROR_FILE_PARAMETERS);
			}
			$files = request()->getRepackedIncomingFiles();
			if(!array_key_exists(DIRECTIVE_IMPORT_CSV, $files)){
				Debug::error("{$f} file not uploaded");
			}
			$files = $files[DIRECTIVE_IMPORT_CSV];
			$import_us = [];
			foreach($files as $file){
				if(is_array($file)){
					Debug::warning("{$f} file is an array");
					Debug::printArray($files);
					Debug::printStackTrace();
				}
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$tempfilename = $file->getTempName();
				$mime_type = $finfo->file($tempfilename);
				if(MimeType::isCompressed($mime_type)){
					$archive_class = MimeType::getFileDataClass($mime_type);
					$import_us = array_merge($import_us, $archive_class::extractTempFilenames($tempfilename, SECURE_FILE_PRIV));
					unlink($tempfilename);
				}else{
					switch($mime_type){
						case MIME_TYPE_PLAINTEXT:
						case MIME_TYPE_CSV:
							break;
						default:
							Debug::print("{$f} finfo returned mime type \"{$mime_type}\"");
							unlink($tempfilename);
							continue 2;
					}
					if($print){
						Debug::print("{$f} mime type of file \"{$tempfilename}\"");
					}
					array_push($import_us, $file->getTempName());
				}
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if($print){
				$imported = 0;
				$count = count($import_us);
				Debug::print("{$f} about to import CSV from {$count} different files");
			}
			foreach($import_us as $import_me){
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$mime_type = $finfo->file($import_me);
				unset($finfo);
				switch($mime_type){
					case MIME_TYPE_PLAINTEXT:
					case MIME_TYPE_CSV:
						break;
					default:
						Debug::print("{$f} finfo returned mime type \"{$mime_type}\"");
						unlink($import_me);
						continue 2;
				}
				if($print){
					Debug::print("{$f} mime type of file \"{$import_me}\" is \"{$mime_type}\"; about to create a new DataCollection");
				}
				unset($mime_type);
				$collection = new HomogeneousDataCollection($doc);
				if(!$collection->hasDataStructureClass()){
					Debug::error("{$f} somehow, collection's data structure class is undefined");
				}elseif($print){
					Debug::print("{$f} about to import CSV from file \"{$import_me}\"");
				}
				$status = $collection->importCSV($mysqli, $import_me, true);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} importing CSV into collection returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
				// unlink($import_me);
				deallocate($collection);
				if($print){
					$imported ++;
					if(getExecutionTime(true) > 25){
						Debug::error("{$f} execution time exceeds 25 seconds");
					}
					//Debug::checkMemoryUsage("After importing file #{$imported} {$import_me} to CSV");
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
