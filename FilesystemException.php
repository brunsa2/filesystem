<?php

class FilesystemException extends Exception {
	const FOLDER_DOES_NOT_EXIST = 0x01;
	const PATH_IS_INVALID = 0x02;
	const PATH_DOES_NOT_EXIST = 0x03;
	const ATTEMPT_TO_CIRCUMVENT_ROOT = 0x04;
	const UNKNOWN_ERROR = 0xfe;
	const INVALID= 0xff;
	
	private $exceptionMessages = array(
		self::FOLDER_DOES_NOT_EXIST => 'Folder does not exist',
		self::PATH_IS_INVALID => 'Path is invalid',
		self::PATH_DOES_NOT_EXIST => 'Path does not exist',
		self::UNKNOWN_ERROR => 'Unknown error',
		self::ATTEMPT_TO_CIRCUMVENT_ROOT => 'Attempt to circumvent root', 
		self::INVALID => 'Invalid filesystem exception'
	);
	
	private $extraData;
	
	public function __construct($code, $extraData = null) {
		if(is_int($code) && $code >= 0x00 && $code <= 0xff) {
			$this->code = $code;
			$this->message = $this->exceptionMessages[$code];
			$this->extraData = $extraData;
		} else {
			throw new FilesystemException(self::INVALID, $code);
		}
	}
	
	public function getExtraData() {
		return $this->extraData;
	}
}

?>