<?php

class FilesystemException extends Exception {
	const FOLDER_DOES_NOT_EXIST_EXCEPTION = 0x01;
	const INVALID_EXCEPTION = 0xff;
	
	private $exceptionMessages = array(
		self::FOLDER_DOES_NOT_EXIST_EXCEPTION => 'Folder does not exist',
		self::INVALID_EXCEPTION => 'Invalid exception'
	);
	
	private $extraData;
	
	public function __construct($code, $extraData = null) {
		if(is_int($code) && $code >= 0x00 && $code < 0x100) {
			$this->code = $code;
			$this->message = $this->exceptionMessages[$code];
			$this->extraData = $extraData;
		} else {
			throw new FilesystemException(self::INVALID_EXCEPTION, $code);
		}
	}
	
	public function getExtraData() {
		return $this->extraData;
	}
}

?>