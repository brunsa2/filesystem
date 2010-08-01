<?php

class Folder {
	protected $database;
	
	protected $id;
	protected $name;
	
	public function __construct($id) {
		$this->id = $id;
		
		$this->database = Database::getDatabase();
		$this->database->prepareQuery('SELECT name FROM folders WHERE id = ' . name('id'), 'Folder-GetFolder');
		$this->database->select('Folder-GetFolder')->bindInteger('id', $id);
		
		$folder = $this->database->executeQuery();
		
		if(count($folder) < 1) {
			throw new FilesystemException(FilesystemException::FOLDER_DOES_NOT_EXIST);
		} elseif(count($folder) > 1) {
			throw new FilesystemException(FilesystemException::UNKNOWN_ERROR);
		}
		
		$this->name = $folder[0]->name;
		
		$this->database->prepareQuery('UPDATE folders SET name = ' . name('name') . ' WHERE id = ' . name('id'), 'Folder-SetFolderName');
		$this->database->prepareQuery('SELECT id FROM links WHERE folder = ' . name('id'), 'Folder-GetFiles');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . name('id'), 'Folder-GetFolders');
		$this->database->prepareQuery('INSERT INTO folders VALUES(NULL, ' . name('id') . ', ' . name('name') . ')', 'Folder-MakeFolder');
		$this->database->prepareQuery('SELECT id FROM folders WHERE parentfolder = ' . name('id') . ' AND name = ' . name('name'), 'Folder-GetSubFolder');
		$this->database->prepareQuery('SELECT parentfolder FROM folders WHERE id = ' . name('id'), 'Folder-GetParent');
	}
	
	public function name($name = '') {
		if($name != null && $name != '') {
			$this->database->select('Folder-SetFolderName')->bindString('name', $name)->bindInteger('id', $this->id)->executeQuery();
			$this->name = $name;
		}
		
		return $this->name;
	}
	
	public function equals($folder) {
		return $this->id == $folder->id;
	}
	
	public function getFilesInFolder() {
		$this->database->select('Folder-GetFiles')->bindInteger('id', $this->id);
		
		$files = array();
		$filesPointer = 0;
		
		foreach($this->database->executeQuery() as $row) {
			$files[$filesPointer++] = new File($row->id, true, true, false);
		}
		
		return $files;
	}
	
	public function getFoldersInFolder() {
		$this->database->select('Folder-GetFolders')->bindInteger('id', $this->id);
		
		$folders = array();
		$foldersPointer = 0;
		
		foreach($this->database->executeQuery() as $row) {
			$folders[$foldersPointer++] = new Folder($row->id);
		}
		
		return $folders;
	}
	
	public function getParent() {
		$this->database->select('Folder-GetParent')->bindInteger('id', $this->id);
		
		$parentFolder = $this->database->executeQuery();
		
		if(count($parentFolder) != 1) {
			throw new FilesystemException(FilesystemException::UNKNOWN_ERROR);
		}
		
		if($parentFolder[0]->parentfolder == null) {
			throw new FilesystemException(FilesystemException::FOLDER_DOES_NOT_EXIST);
		}
		
		return new Folder($parentFolder[0]->parentfolder);
	}
	
	public function getSubfolder($name) {
		$this->database->select('Folder-GetSubFolder')->bindInteger('id', $this->id)->bindString('name', $name);
		
		$subFolder = $this->database->executeQuery();
		
		if(count($subFolder) < 1) {
			throw new FilesystemException(FilesystemException::FOLDER_DOES_NOT_EXIST);
		} elseif(count($subFolder) > 1) {
			throw new FilesystemException(FilesystemException::UNKNOWN_ERROR);
		}
		
		return new Folder($subFolder[0]->id);
	}
	
	public function getFolder($path) {
		$path = trim($path);
		
		if(preg_match('/^\/?([^\/#]+\/?)*$/', $path) == 0) {
			throw new FilesystemException(FilesystemException::PATH_IS_INVALID);
		}
		
		$folders = null;
		preg_match_all('/[a-zA-Z0-9 \.]+/', $path, $folders);
		$folders = $folders[0];
		
		if(substr($path, 0, 1) == '/') {
			$folder = Filesystem::getRoot();
		} else {
			$folder = $this;
		}
		
		foreach($folders as $folderName) {			
			if($folderName == '..') {
				if($folder->equals(Filesystem::getRoot())) {
					throw new FilesystemException(FilesystemException::ATTEMPT_TO_CIRCUMVENT_ROOT);
				}
				
				$folder = $folder->getParent();
			} elseif($folderName != '.') {
				try {
					$folder = $folder->getSubfolder($folderName);
				} catch (FilesystemException $errorThrown) {
					throw new FilesystemException(FilesystemException::PATH_DOES_NOT_EXIST);
				}
			}
		}
		
		return $folder;
	}
	
	public function makeFolder($name) {
		if(!is_string($name) || $name == '') {
			return;
		}
		
		$name = (string) $name;
		
		$currentFolders = $this->getFoldersInFolder();
		
		foreach($currentFolders as $folder) {			
			if((string) $folder == $name) {
				throw new Exception(FilesystemException::FOLDER_EXISTS);
			}
		}
		
		$this->database->select('Folder-MakeFolder')->bindInteger('id', $this->id)->bindString('name', $name)->executeQuery();
		
		return $this->getSubfolder($name);
	}
	
	public function __toString() {
		return $this->name();
	}
}

?>