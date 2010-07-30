<?php

class Folder extends Tag {
	public function __construct($id) {
		parent::__construct($id);
		
		$searchForParent = true;
		
		$currentTagID = $id;
		
		while($searchForParent) {
			$this->database->retrieveQuery('Tag-GetParent')->bindInteger('id', $currentTagID)->executeQuery();
			
			foreach($this->database as $row) {
				if($row->parenttag == null) {
					throw new FilesystemException(FilesystemException::FOLDER_DOES_NOT_EXIST_EXCEPTION);
				} else {
					$currentTagID = $row->parenttag;
					
					if($row->parenttag == 2) {
						$searchForParent = false;
					}
				}
			}
		}
		
		$this->database->prepareQuery('SELECT id FROM links, assigns WHERE links.id = assigns.link AND assigns.tag = ' . $this->database->name('id'))->storeQuery('Folder-GetFiles');
		$this->database->prepareQuery('SELECT id FROM tags WHERE parent = ' . $this->database->name('id'))->storeQuery('Folder-GetFolders');
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
	
	public function getFoldersInFolder() {
		$this->database->retrieveQuery('Folder-GetFolders')->bindInteger('id', $this->id)->executeQuery();
		
		$folders = array();
		$foldersPointer = 0;
		
		foreach($this->database as $row) {
			$folders[$foldersPointer++] = new Folder($row->id);
		}
		
		
		return $folders;
	}
}

?>