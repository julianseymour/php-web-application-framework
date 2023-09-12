<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\settings\timezone;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\command\element\GetElementByIdCommand;
use JulianSeymour\PHPWebApplicationFramework\command\element\SetStylePropertiesCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\Choice;
use JulianSeymour\PHPWebApplicationFramework\input\choice\SelectInput;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpandingMenuNestedForm;
use Exception;

class TimezoneSettingsForm extends ExpandingMenuNestedForm
{

	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->setStyleProperties(["padding" => "1rem"]);
	}
	
	public static function getFormDispatchIdStatic(): ?string{
		return "timezone";
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_UPDATE:
				$button = $this->generateGenericButton($name);
				$button->setInnerHTML(_("Update timezone"));
				$mode = $this->getAllocationMode();
				$autodetect = new ButtonInput($mode);
				$autodetect->setInnerHTML(_("Autodetect"));
				$autodetect->setOnclickAttribute("autodetectTimezone()");
				if(! Request::isAjaxRequest()) {
					$autodetect->setStyleProperty("display", "none");
				}
				$autodetect->setIdAttribute("autodetect_timezone");
				$props = [
					"display" => "block",
					"margin-top" => "1rem",
					"positon" => "relative"
				];
				$autodetect->setStyleProperties($props);
				$button->pushPredecessor($autodetect);
				$button->setStyleProperties($props);
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid button name \"{$name}\"");
		}
	}

	public static function getActionAttributeStatic(): ?string{
		return '/settings';
	}

	public function getFormDataIndices(): ?array{
		return [
			"timezone" => SelectInput::class
		];
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_UPDATE
		];
	}

	public static function getMaxHeightRequirement(){
		return 999;
	}

	public static function getExpandingMenuLabelString($context){
		return _("Timezone");
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$vn = $input->getColumnName();
			switch ($vn) {
				case "timezone":
					$input->setIdAttribute("timezone_select");
					break;
				default:
					break;
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public static function getExpandingMenuRadioButtonIdAttribute(){
		return "radio_settings-timezone";
	}

	public function generateFormFooter(): void{
		$f = __METHOD__;
		try{
			if(! Request::isAjaxRequest()) {
				$script = new ScriptElement($this->getAllocationMode());
				$element = new GetElementByIdCommand('autodetect_timezone');
				$element->setParseType(TYPE_STRING);
				$script->appendChild(new SetStylePropertiesCommand($element, [
					'display' => 'inline'
				]));
				$this->appendChild($script);
			}
			parent::generateFormFooter();
		}catch(Exception $x) {
			x($f, $x);
		}
	}
	
	public function generateChoices($input): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$column_name = $input->getColumnName();
			$ds = $input->getContext()->getDataStructure();
			switch ($column_name) {
				case "timezone":
					// thanks to Maulik Gangani
					// https://stackoverflow.com/questions/39263321/javascript-get-html-timezone-dropdown
					$keyvalues = [
						"Etc/GMT+12" => "(GMT-12:00) International Date Line West",
						"Pacific/Midway" => "(GMT-11:00) Midway Island, Samoa",
						"Pacific/Honolulu" => "(GMT-10:00) Hawaii",
						"US/Alaska" => "(GMT-09:00) Alaska",
						"America/Los_Angeles" => "(GMT-08:00) Pacific Time (US & Canada)",
						"America/Tijuana" => "(GMT-08:00) Tijuana, Baja California",
						"US/Arizona" => "(GMT-07:00) Arizona",
						"America/Chihuahua" => "(GMT-07:00) Chihuahua, La Paz, Mazatlan",
						"US/Mountain" => "(GMT-07:00) Mountain Time (US & Canada)",
						"America/Managua" => "(GMT-06:00) Central America",
						"US/Central" => "(GMT-06:00) Central Time (US & Canada)",
						"America/Mexico_City" => "(GMT-06:00) Guadalajara, Mexico City, Monterrey",
						"Canada/Saskatchewan" => "(GMT-06:00) Saskatchewan",
						"America/Bogota" => "(GMT-05:00) Bogota, Lima, Quito, Rio Branco",
						"US/Eastern" => "(GMT-05:00) Eastern Time (US & Canada)",
						"US/East-Indiana" => "(GMT-05:00) Indiana (East)",
						"Canada/Atlantic" => "(GMT-04:00) Atlantic Time (Canada)",
						"America/Caracas" => "(GMT-04:00) Caracas, La Paz",
						"America/Manaus" => "(GMT-04:00) Manaus",
						"America/Santiago" => "(GMT-04:00) Santiago",
						"Canada/Newfoundland" => "(GMT-03:30) Newfoundland",
						"America/Sao_Paulo" => "(GMT-03:00) Brasilia",
						"America/Argentina/Buenos_Aires" => "(GMT-03:00) Buenos Aires, Georgetown",
						"America/Godthab" => "(GMT-03:00) Greenland",
						"America/Montevideo" => "(GMT-03:00) Montevideo",
						"America/Noronha" => "(GMT-02:00) Mid-Atlantic",
						"Atlantic/Cape_Verde" => "(GMT-01:00) Cape Verde Is.",
						"Atlantic/Azores" => "(GMT-01:00) Azores",
						"Africa/Casablanca" => "(GMT+00:00) Casablanca, Monrovia, Reykjavik",
						"Etc/Greenwich" => "(GMT+00:00) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London",
						"Europe/Amsterdam" => "(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
						"Europe/Belgrade" => "(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague",
						"Europe/Brussels" => "(GMT+01:00) Brussels, Copenhagen, Madrid, Paris",
						"Europe/Sarajevo" => "(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb",
						"Africa/Lagos" => "(GMT+01:00) West Central Africa",
						"Asia/Amman" => "(GMT+02:00) Amman",
						"Europe/Athens" => "(GMT+02:00) Athens, Bucharest, Istanbul",
						"Asia/Beirut" => "(GMT+02:00) Beirut",
						"Africa/Cairo" => "(GMT+02:00) Cairo",
						"Africa/Harare" => "(GMT+02:00) Harare, Pretoria",
						"Europe/Helsinki" => "(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius",
						"Asia/Jerusalem" => "(GMT+02:00) Jerusalem",
						"Europe/Minsk" => "(GMT+02:00) Minsk",
						"Africa/Windhoek" => "(GMT+02:00) Windhoek",
						"Asia/Kuwait" => "(GMT+03:00) Kuwait, Riyadh, Baghdad",
						"Europe/Moscow" => "(GMT+03:00) Moscow, St. Petersburg, Volgograd",
						"Africa/Nairobi" => "(GMT+03:00) Nairobi",
						"Asia/Tbilisi" => "(GMT+03:00) Tbilisi",
						"Asia/Tehran" => "(GMT+03:30) Tehran",
						"Asia/Muscat" => "(GMT+04:00) Abu Dhabi, Muscat",
						"Asia/Baku" => "(GMT+04:00) Baku",
						"Asia/Yerevan" => "(GMT+04:00) Yerevan",
						"Asia/Kabul" => "(GMT+04:30) Kabul",
						"Asia/Yekaterinburg" => "(GMT+05:00) Yekaterinburg",
						"Asia/Karachi" => "(GMT+05:00) Islamabad, Karachi, Tashkent",
						"Asia/Calcutta" => "(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi",
						"Asia/Calcutta" => "(GMT+05:30) Sri Jayawardenapura",
						"Asia/Katmandu" => "(GMT+05:45) Kathmandu",
						"Asia/Almaty" => "(GMT+06:00) Almaty, Novosibirsk",
						"Asia/Dhaka" => "(GMT+06:00) Astana, Dhaka",
						"Asia/Rangoon" => "(GMT+06:30) Yangon (Rangoon)",
						"Asia/Bangkok" => "(GMT+07:00) Bangkok, Hanoi, Jakarta",
						"Asia/Krasnoyarsk" => "(GMT+07:00) Krasnoyarsk",
						"Asia/Hong_Kong" => "(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi",
						"Asia/Kuala_Lumpur" => "(GMT+08:00) Kuala Lumpur, Singapore",
						"Asia/Irkutsk" => "(GMT+08:00) Irkutsk, Ulaan Bataar",
						"Australia/Perth" => "(GMT+08:00) Perth",
						"Asia/Taipei" => "(GMT+08:00) Taipei",
						"Asia/Tokyo" => "(GMT+09:00) Osaka, Sapporo, Tokyo",
						"Asia/Seoul" => "(GMT+09:00) Seoul",
						"Asia/Yakutsk" => "(GMT+09:00) Yakutsk",
						"Australia/Adelaide" => "(GMT+09:30) Adelaide",
						"Australia/Darwin" => "(GMT+09:30) Darwin",
						"Australia/Brisbane" => "(GMT+10:00) Brisbane",
						"Australia/Canberra" => "(GMT+10:00) Canberra, Melbourne, Sydney",
						"Australia/Hobart" => "(GMT+10:00) Hobart",
						"Pacific/Guam" => "(GMT+10:00) Guam, Port Moresby",
						"Asia/Vladivostok" => "(GMT+10:00) Vladivostok",
						"Asia/Magadan" => "(GMT+11:00) Magadan, Solomon Is., New Caledonia",
						"Pacific/Auckland" => "(GMT+12:00) Auckland, Wellington",
						"Pacific/Fiji" => "(GMT+12:00) Fiji, Kamchatka, Marshall Is.",
						"Pacific/Tongatapu" => "(GMT+13:00) Nuku'alofa"
					];
					$choices = [];
					$timezone = $ds->getTimezone();
					if($print) {
						Debug::print("{$f} this user's timezone is \"{$timezone}\"");
					}
					foreach($keyvalues as $key => $value) {
						$choices[$key] = new Choice($key, $value, $timezone === $key);
					}
					return $choices;
				default:
					return parent::generateChoices($input);
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
