<?php

class File {
	const SEEK_BEGINNING = -1;
	const SEEK_CURRENT = 0;
	const SEEK_END = 1;

	private $content;
	
	private $open;
	private $size;
	private $position;
	
	public function __construct($content = '') {
		$this->content = $content;
		
		$this->open = false;
		$this->size = strlen($content);
		$this->position = 0;
	}
	
	public function open() {
		if(!$this->open) {
			$this->open = true;
		}
		
		return $this;
	}
	
	public function close() {
		$this->open = false;
	}
	
	public function read($length = 0) {
		$stringReadFromBuffer = $this->peek($length);
		$this->position += strlen($stringReadFromBuffer);
		return $stringReadFromBuffer;
	}
	
	public function peek($length = 0) {
		$length = $length == 0 ? $this->size - $this->position : $length;
		$length = $this->position + $length > $this->size ? $this->size - $this->position : $length;
		return substr($this->content, $this->position, $length);
	}
	
	public function truncate($length) {
		if($length > $this->size) {
			for($currentNullCharacter = 0; $currentNullCharacter < ($length - $this->size); $currentNullCharacter++) {
				$this->content .= chr(0);
			}
		} else {
			$this->content = substr($this->content, 0, $length);
		}
		
		$this->size = $length;
		
		return $this;
	}
	
	public function seek($offset, $relative = self::SEEK_CURRENT) {
		$position = $this->position + $offset;
		
		switch($relative) {
			case self::SEEK_BEGINNING:
				$position = $offset;
				break;
			case self::SEEK_END:
				$position = $this->size + $offset;
				break;
		}
		
		$position = $position >= 0 ? $position : 0;
		$position = $position < $this->size ? $position : $this->size;
		
		$this->position = $position;
		
		return $this;
	}
}

?>