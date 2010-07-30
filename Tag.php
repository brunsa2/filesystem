<?php

class Tag {
	protected $database;
	
	protected $id;
	protected $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM tags WHERE id = ' . $this->database->name('id'))->storeQuery('Tag-GetTag');
		$this->database->bindInteger('id', $id)->executeQuery();
		
		foreach($this->database as $row) {
			$this->name = $row->name;
		}
		
		$this->database->prepareQuery('UPDATE tags SET name = ' . $this->database->name('name') . ' WHERE id = ' . $this->database->name('id'))->storeQuery('Tag-SetTagName');
		$this->database->prepareQuery('SELECT parenttag FROM tags WHERE id = ' . $this->database->name('id'))->storeQuery('Tag-GetParent');
		$this->database->prepareQuery('SELECT id FROM assigns, links WHERE assigns.tag = ' . $this->database->name('id'))->storeQuery('Tag-GetFiles');
	}
	
	public function name($name = '') {
		if($name != null && $name != '') {
			$this->database->retrieveQuery('Tag-SetTagName')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function parentTag() {
		$this->database->retrieveQuery('Tag-GetParent')->bindInteger('id', $this->id)->executeQuery();
		
		$parentTag = null;
		
		foreach($this->database as $row) {
			$parentTag = new Tag($row->parenttag);
		}
		
		return $parentTag;
	}
	
	public function getFiles() {
		$files = array();
		$filesPointer = 0;
		
		$this->database->retrieveQuery('Tag-GetFiles')->bindInteger('id', $this->id)->executeQuery();
		
		foreach($this->database as $row) {
			$files[$filesPointer++] = new File($row->id, true, false, false);
		}
		
		return $files;
	}
	
	public function isDescendantOf($tag) {
		$currentTagID = $this->id;
		
		while(true) {
			$this->database->retrieveQuery('Tag-GetParent')->bindInteger('id', $currentTagID)->executeQuery();
			
			foreach($this->database as $row) {
				if($row->parenttag == null) {
					return false;
				} else {
					$currentTagID = $row->parenttag;
					
					if($row->parenttag = $tag->id) {
						return true;
					}
				}
			}
		}
	}
}

?>