<?php

class File {
	const SEEK_BEGINNING = -1;
	const SEEK_CURRENT = 0;
	const SEEK_END = 1;
	
	private $database;

	private $id;
	private $content;
	private $hash;
	private $name;
	
	private $readEnable;
	private $writeEnable;
	private $appendOnly;
	private $size;
	private $position;
	
	public function __construct($id, $readEnable, $writeEnable, $appendOnly) {
		$this->id = $id;

		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT hash, content FROM links, files WHERE links.id = ' . name('id') . ' AND links.file = files.id', 'File-GetFile');
		$this->database->bindInteger('id', $id);
		
		foreach($this->database->executeQuery() as $row) {
			$this->content = $row->content;
			$this->size = strlen($row->content);
			$this->hash = $row->hash;
		}
		
		$this->database->prepareQuery('SELECT name FROM links WHERE id = ' . name('id'), 'File-GetName');
		$this->database->bindInteger('id', $id);
		
		foreach($this->database->executeQuery() as $row) {
			$this->name = $row->name;
		}
		
		$this->readEnable = $readEnable;
		$this->writeEnable = $writeEnable;
		$this->appendOnly = $appendOnly;
		$this->size = strlen($this->content);
		$this->position = 0;
		
		$this->database->prepareQuery('SELECT hash, content FROM links, files WHERE links.id = ' . name('id') . ' AND links.file = files.id AND hash != ' . name('hash'), 'File-GetChangedFile');
		$this->database->prepareQuery('UPDATE links, files SET files.content = ' . name('content') . ', files.hash = SHA1(' . name('content') . ') WHERE links.id = ' . name('id') . ' AND links.file = files.id', 'File-StoreFile');
		$this->database->prepareQuery('UPDATE links SET name = ' . name('name') . ' WHERE id = ' . name('id'), 'File-Rename');
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
		$this->updateFileContents();
		
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
			$this->updateFileContents();
			
			if($length > $this->size) {
				for($currentNullCharacter = 0; $currentNullCharacter < ($length - $this->size); $currentNullCharacter++) {
					$this->content .= chr(0);
				}
			} else {
				$this->content = substr($this->content, 0, $length);
			}
			
			$this->size = $length;
			
			$this->database->select('File-StoreFile')->bindInteger('id', $this->id)->bindInteger('content', $this->content)->executeQuery();
			
			return true;
		} else {
			return false;
		}
	}
	
	public function seek($offset, $relative = self::SEEK_CURRENT) {
		if($this->readEnable || ($this->writeEnable && !$this->appendOnly)) {
			$this->updateFileContents();
			
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
			$this->updateFileContents();
			
			$length = $length == 0 ? strlen($data) : $length;
			$position = $this->appendOnly ? $this->size : $this->position;
			$data = substr($data, 0, $length);
			
			if($this->size < $position + $length) {
				$this->truncate($position + $length);
			}
			
			$this->content = substr($this->content, 0, $position) . $data . substr($this->content, $position + $length);
			
			$this->database->select('File-StoreFile')->bindInteger('id', $this->id)->bindInteger('content', $this->content)->executeQuery();
			
			return $length;
		} else {
			return false;
		}
	}
	
	public function name($name = '') {
		if($name != null & $name != '') {
			$this->database->select('File-Rename')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	private function updateFileContents() {
		$this->database->select('File-GetChangedFile')->bindInteger('id', $this->id)->bindString('hash', $this->hash);
		
		foreach($this->database->executeQuery() as $row) {
			$this->content = $row->content;
			$this->size = strlen($row->content);
			$this->hash = $row->hash;
		}
	}
}

?>