<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\getlocale;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\UserMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\email\content\HTMLEmailContent;
use JulianSeymour\PHPWebApplicationFramework\email\content\MultipartAlternativeEmailContent;
use JulianSeymour\PHPWebApplicationFramework\email\content\MultipartEmailContent;
use JulianSeymour\PHPWebApplicationFramework\email\content\MultipartMixedEmailContent;
use JulianSeymour\PHPWebApplicationFramework\email\content\MultipartRelatedEmailContent;
use JulianSeymour\PHPWebApplicationFramework\email\content\PlaintextEmailContent;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\CleartextFileData;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use Exception;
use mysqli;

abstract class SpamEmail extends DataStructure implements StaticElementClassInterface{
	
	protected $htmlContent;

	public static function getDatabaseNameStatic():string{
		return "user_content";
	}
	
	public static function getElementClassStatic(?StaticElementClassInterface $that = null):string{
		return SimpleEmailElement::class;
	}

	public static function getPermissionStatic(string $name, $data){
		if ($name === DIRECTIVE_INSERT) {
			return SUCCESS;
		}
		return parent::getPermissionStatic($name, $data);
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null):void{
		parent::declareColumns($columns, $ds);
		$sender = new UserMetadataBundle("sender", $ds);
		$sender->setNullable(true);
		$recipient = new UserMetadataBundle("recipient", $ds);
		$senderEmailAddress = new EmailAddressDatum("senderEmailAddress");
		$senderEmailAddress->setNullable(true);
		$recipientEmailAddress = new EmailAddressDatum("recipientEmailAddress");
		$accepted = new BooleanDatum("accepted");
		static::pushTemporaryColumnsStatic($columns, $sender, $senderEmailAddress, $recipient, $recipientEmailAddress, $accepted);
	}

	public function hasRecipient():bool{
		return $this->hasForeignDataStructure("recipientKey");
	}
	
	public function getRecipient():DataStructure{
		$f = __METHOD__;
		if(!$this->hasRecipient()){
			Debug::error("{$f} recipient is undefined");
		}
		return $this->getForeignDataStructure("recipientKey");
	}
	
	/**
	 *
	 * @param UserData $user
	 * @return UserData
	 */
	public function setRecipient(UserData $user):UserData{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasRecipientEmailAddress() && $user->hasEmailAddress()){
			$email = $user->getEmailAddress();
			if($print){
				Debug::print("{$f} assigning recipient email address to \"{$email}\"");
			}
			$this->setRecipientEmailAddress($email);
		}elseif($print){
			Debug::print("{$f} email address was already assigned, or recipient doesn not have one");
		}
		return $this->setForeignDataStructure("recipientKey", $user);
	}
	
	public function hasRecipientEmailAddress(){
		return $this->hasColumnValue("recipientEmailAddress");
	}
	
	public function getRecipientEmailAddress(){
		$f = __METHOD__;
		if (! $this->hasRecipientEmailAddress()) {
			Debug::error("{$f} recipient email address is undefined");
		}
		return $this->getColumnValue("recipientEmailAddress");
	}
	
	public function setRecipientEmailAddress($email){
		return $this->setColumnValue("recipientEmailAddress", $email);
	}
	
	
	public function hasSender():bool{
		return $this->hasForeignDataStructure("senderKey");
	}
	
	public function getSender():DataStructure{
		$f = __METHOD__;
		if(!$this->hasSender()){
			Debug::error("{$f} sender is undefined");
		}
		return $this->getForeignDataStructure("senderKey");
	}
	
	/**
	 *
	 * @param UserData $user
	 * @return USerData
	 */
	public function setSender(UserData $user):UserData{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasSenderEmailAddress() && $user->hasEmailAddress()){
			$email = $user->getEmailAddress();
			if($print){
				Debug::print("{$f} assigning sender email address to \"{$email}\"");
			}
			$this->setSenderEmailAddress($email);
		}elseif($print){
			Debug::print("{$f} email address was already assigned, or sender does not have one");
		}
		return $this->setForeignDataStructure("senderKey", $user);
	}
	
	public function hasSenderEmailAddress(){
		return $this->hasColumnValue("senderEmailAddress");
	}
	
	public function getSenderEmailAddress(){
		$f = __METHOD__;
		if (! $this->hasSenderEmailAddress()) {
			Debug::error("{$f} sender email address is undefined");
		}
		return $this->getColumnValue("senderEmailAddress");
	}
	
	public function setSenderEmailAddress(string $email):string{
		$f = __METHOD__;
		return $this->setColumnValue("senderEmailAddress", $email);
	}

	public function setAccepted($value){
		return $this->setColumnValue("accepted", $value);
	}

	public function embedImage($data){
		$f = __METHOD__;
		if (is_string($data)) {
			$data = new EmbeddedImageData($data);
		} elseif (! $data instanceof ImageData) {
			Debug::error("{$f} embedded image data is not an ImageData");
		} elseif (! $data->hasIdentifierValue()) {
			$status = $data->generateKey();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} generateKey returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		}
		$this->setForeignDataStructureListMember("embeddedImages", $data);
		return $data->getIdentifierValue();
	}

	public function reportEmbeddedImage(ImageData $data){
		return $this->embedImage($data);
	}

	public function hasEmbeddedImages(){
		return $this->hasForeignDataStructureList("embeddedImages");
	}

	public function getEmbeddedImages(){
		$f = __METHOD__;
		if (! $this->hasEmbeddedImages()) {
			Debug::error("{$f} embedded images array is undefined");
		}
		return $this->getForeignDataStructureList("embeddedImages");
	}

	public final function getHTMLBody(){
		return $this->getHTMLContent()->__toString();
	}
	
	public function hasAttachments():bool{
		return $this->hasForeignDataStructureList("attachments");
	}

	public function addAttachment($file){
		$f = __METHOD__;
		if (! $file instanceof CleartextFileData) {
			Debug::error("{$f} please don't pass your garbage to this function");
		}
		return $this->setForeignDataStructureListMember("attachments", $file);
	}

	/**
	 *
	 * @return CleartextFileData[]
	 */
	public function getAttachments(){
		$f = __METHOD__;
		if (! $this->hasAttachments()) {
			Debug::error("{$f} attachments are undefined");
		}
		return $this->getForeignDataStructureList("attachments");
	}

	public function hasHTMLContent(): bool{
		return isset($this->htmlContent);
	}

	public function getHTMLContent(){
		$f = __METHOD__;
		if (! $this->hasHTMLContent()) {
			Debug::error("{$f} html content is undefined");
		}
		return $this->htmlContent;
	}

	public function setHTMLContent($htmlContent){
		if ($htmlContent === null) {
			unset($this->htmlContent);
			return null;
		}
		return $this->htmlContent = $htmlContent;
	}

	public function hasPlaintextContent(): bool{
		return true;
	}
	
	public function buildContentNodes(?bool $mix = null, ?bool $alt = null){
		$f = __METHOD__;
		try {
			$print = false;
			if ($mix === null) {
				$mix = $this->hasAttachments();
				if ($print) {
					if ($mix) {
						Debug::print("{$f} yes, this email has attachments");
					} elseif ($print) {
						Debug::print("{$f} no, this email does not have attachments");
					}
				}
			} elseif ($print) {
				if ($mix) {
					Debug::print("{$f} generating a mixed content node");
				} else {
					Debug::print("{$f} explicitly told to skip mixed content node");
				}
			}
			if ($alt === null) {
				$alt = $this->hasPlaintextContent() && $this->hasHTMLContent();
				if ($print) {
					if ($alt) {
						Debug::print("{$f} this email has both plaintext and HTML content");
					} else {
						Debug::print("{$f} this email lacks plaintext and/or HTML content");
					}
				}
			} elseif ($print) {
				if ($alt) {
					Debug::print("{$f} generating a alternative content node");
				} else {
					Debug::print("{$f} explicitly told to skip alternative content node");
				}
			}
			if ($mix) {
				if ($print) {
					Debug::print("{$f} now generating mixed content node");
				}
				$attachments = [];
				foreach ($this->getAttachments() as $attachment) {
					array_push($attachments, $attachment->attach());
				}
				return new MultipartMixedEmailContent($this->buildContentNodes(false, $alt), ...$attachments);
			} elseif ($alt) {
				if ($print) {
					Debug::print("{$f} now generating alternative content node");
				}
				return new MultipartAlternativeEmailContent(
					new PlaintextEmailContent($this->getPlaintextContent()), 
					$this->buildContentNodes($mix, false)
				);
			} elseif ($this->hasHTMLContent()) {
				if ($print) {
					Debug::print("{$f} HTML content is defined");
				}
				$node = new HTMLEmailContent($this->getHTMLBody());
				if ($this->hasEmbeddedImages()) {
					if ($print) {
						Debug::print("{$f} this email has embedded images");
					}
					$embeds = [];
					foreach ($this->getEmbeddedImages() as $embed) {
						array_push($embeds, $embed->embed());
					}
					return new MultipartRelatedEmailContent($node, ...$embeds);
				}
				return $node;
			} elseif ($this->hasPlaintextContent()) {
				if ($print) {
					Debug::print("{$f} this element has only plaintext content");
				}
				return new PlaintextEmailContent($this->getPlaintextContent());
			}
			Debug::error("{$f} undefined behavior");
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function getPlaintextContent():string{
		return $this->getPlaintextBody();
	}
	
	public function sendAndInsert(mysqli $mysqli){
		$f = __METHOD__;
		$print = false;
		$status = $this->send();
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} send() returned error status \"{$err}\"");
		}elseif($print){
			Debug::print("{$f} successfully sent email");
		}
		// insert email record
		$status = $this->insert($mysqli);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} insert() returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} successfully inserted email record");
		}
		return SUCCESS;
	}

	public function send():int{
		$f = __METHOD__;
		try {
			$print = false;
			$debug = false;
			// generate email body
			$locale = getlocale(LC_MESSAGES);
			$rls = $this->getRecipient()->getLocaleString();
			$set = setlocale(LC_MESSAGES, $rls, "{$rls}.utf8", "{$rls}.UTF8");
			if(false === $set){
				Debug::error("{$f} setting locale failed");
			}elseif($print){
				Debug::print("{$f} successfully set locale to \"{$set}\"");
			}
			$ec = $this->getElementClass();
			$element = new $ec(ALLOCATION_MODE_EMAIL);
			$element->setEmbeddedImageCollector($this);
			$element->bindContext($this);
			$this->setHTMLContent($element);
			$root_node = $this->buildContentNodes();
			if ($print) {
				if (! $root_node instanceof MultipartEmailContent) {
					$rnc = $root_node->getClass();
					Debug::error("{$f} root node is a \"{$rnc}\"");
				}
				$length = strlen("{$root_node}");
				Debug::print("{$f} about to send multipart body \"{$root_node}\" of length {$length}");
			}
			// generate headers
			$content_type = $root_node->getContentType();
			$headers = [
				"MIME-Version:1.0",
				"From:noreply@" . DOMAIN_LOWERCASE,
				// "To: {$address}",
				"Reply-To:".$this->getSenderEmailAddress(),
				"X-Mailer:PHP/" . PHP_VERSION,
				"Content-Type:{$content_type}"
			];
			if ($print) {
				Debug::print("{$f} about to send an email with the following headers:");
				Debug::printArray($headers);
			}
			$headers = implode("\r\n", $headers);
			// send email
			$address = $this->getRecipientEmailAddress();
			if ($print) {
				Debug::print("{$f} about to send email to address \"{$address}\"");
			}
			if ($debug) {
				if($print){
					Debug::print("{$f} skipping mail()");
				}
				$mailed = true;
			} else {
				$mailed = mail($address, $this->getSubjectLine(), $root_node, $headers);
				iF($print){
					Debug::print("{$f} returned from mail()");
				}
			}
			if ($mailed) {
				if ($print) {
					Debug::print("{$f} sendmail reports the message was accepted for delivery");
				}
				$status = SUCCESS;
				$this->setAccepted(true);
			} else {
				if ($print) {
					Debug::warning("{$f} sendmail reports message delivery was unsuccessful");
				}
				$status = ERROR_SENDMAIL;
				$this->setAccepted(false);
			}
			$set = setlocale(LC_MESSAGES, $locale, "{$locale}.utf8", "{$locale}.UTF8");
			if(false === $set){
				Debug::error("{$f} restoring locale failed");
			}elseif($print){
				Debug::print("{$f} successfully restored locale to \"{$set}\"");
			}
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getPrettyClassName():string{
		return _("Email record");
	}

	public static function getTableNameStatic(): string{
		return "email_records";
	}

	public static function getDataType(): string{
		return DATATYPE_EMAIL_RECORD;
	}

	public static function getPrettyClassNames():string{
		return _("Email records");
	}
}

