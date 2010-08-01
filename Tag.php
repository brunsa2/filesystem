<?php

class Tag {
	private $database;
	
	private $id;
	private $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM tags WHERE id = ' . name('id'), 'Tag-GetTag');
		$this->database->bindInteger('id', $id)->executeQuery();
		
		foreach($this->database as $row) {
			$this->name = $row->name;
		}
		
		$this->database->prepareQuery('UPDATE tags SET name = ' . >name('name') . ' WHERE id = ' . name('id'), 'Tag-SetTagName');
		$this->database->prepareQuery('SELECT parenttag FROM tags WHERE id = ' . name('id'), 'Tag-GetParent');
		$this->database->prepareQuery('SELECT id FROM assigns, links WHERE assigns.tag = ' . name('id'), 'Tag-GetFiles');
	}
	
	public function name($name = '') {
		if($name != null && $name != '') {
			$this->database->select('Tag-SetTagName')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	public function id() {
		return $this->id;
	}
	
	public function parentTag() {
		$this->database->select('Tag-GetParent')->bindInteger('id', $this->id);
		
		$parentTag = null;
		
		foreach($this->database->executeQuery() as $row) {
			$parentTag = new Tag($row->parenttag);
		}
		
		return $parentTag;
	}
	
	public function getFiles() {
		$files = array();
		$filesPointer = 0;
		
		$this->database->select('Tag-GetFiles')->bindInteger('id', $this->id);
		
		foreach($this->database->executeQuery() as $row) {
			$files[$filesPointer++] = new File($row->id, true, false, false);
		}
		
		return $files;
	}
	
	public function isDescendantOf($tag) {
		$currentTagID = $this->id;
		
		while(true) {
			$this->database->select('Tag-GetParent')->bindInteger('id', $currentTagID);
			
			foreach($this->database->executeQuery() as $row) {
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