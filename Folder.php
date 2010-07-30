<?php

class Folder extends Tag {
	public function __construct($id) {
		parent::__construct($id);
		
		$this->database->prepareQuery('SELECT id FROM links, assigns WHERE links.id = assigns.link AND assigns.tag = ' . $this->database->name('id'))->storeQuery('Folder-GetFiles');
	}
	
	public function getFilesInFolder() {
		$this->database->retrieveQuery('Folder-GetFiles')->bindInteger('id', $this->id)->executeQuery();
		
		$files = array();
		$filesPointer = 0;
		
		foreach($this->database as $row) {
			$files[$filesPointer++] = new File($row->id, true, true, false);
		}
		
		return $files;
	}
}

?>