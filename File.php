<?php

class File {
	const SEEK_BEGINNING = -1;
	const SEEK_CURRENT = 0;
	const SEEK_END = 1;

	private $id;
	private $content;
	private $hash;
	
	private $readEnable;
	private $writeEnable;
	private $appendOnly;
	private $size;
	private $position;
	
	public function __construct($id, $readEnable, $writeEnable, $appendOnly) {
		$this->id = $id;
		
		$database = Database::getDatabase();
		$database->prepareQuery('SELECT * FROM files WHERE id = ' . $database->name('id'))->storeQuery('GetFile');
		$database->bindInteger('id', $id)->executeQuery();
		
		foreach($database as $row) {
			$this->content = $row->content;
			$this->hash = $row->hash;
		}
		
		$this->readEnable = $readEnable;
		$this->writeEnable = $writeEnable;
		$this->appendOnly = $appendOnly;
		$this->size = strlen($this->content);
		$this->position = 0;
	}
	
	public function close() {
		$this->readEnable = false;
		$this->writeEnable = false;
		$this->appendOnly = false;
	}
	
	public function read($length = 0) {
		if($this->readEnable) {
			$stringReadFromBuffer = $this->peek($length);
			$this->position += strlen($stringReadFromBuffer);
			return $stringReadFromBuffer;
		} else {
			return false;
		}
	}
	
	public function peek($length = 0) {
		if($this->readEnable) {
			$length = $length == 0 ? $this->size - $this->position : $length;
			$length = $this->position + $length > $this->size ? $this->size - $this->position : $length;
			return substr($this->content, $this->position, $length);
		} else {
			return false;
		}
	}
	
	public function truncate($length) {
		if($this->writeEnable) {
			if($length > $this->size) {
				for($currentNullCharacter = 0; $currentNullCharacter < ($length - $this->size); $currentNullCharacter++) {
					$this->content .= chr(0);
				}
			} else {
				$this->content = substr($this->content, 0, $length);
			}
			
			$this->size = $length;
			
			return true;
		} else {
			return false;
		}
	}
	
	public function seek($offset, $relative = self::SEEK_CURRENT) {
		if($this->readEnable || ($this->writeEnable && !$this->appendOnly)) {
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
			
			return true;
		} else {
			return false;
		}
	}
	
	public function write($data, $length = 0) {
		if($this->writeEnable) {
			$length = $length == 0 ? strlen($data) : $length;
			$position = $this->appendOnly ? $this->size : $this->position;
			$data = substr($data, 0, $length);
			
			if($this->size < $position + $length) {
				$this->truncate($position + $length);
			}
			
			$this->content = substr($this->content, 0, $position) . $data . substr($this->content, $position + $length + 1);
			
			return $length;
		} else {
			return false;
		}
	}
}

?>