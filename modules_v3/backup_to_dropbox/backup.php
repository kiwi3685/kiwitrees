<?php

class Backup {

    private $dbxClient;
    private $projectFolder;

    /**
     * __construct pass token and project to the client method
     * @param string $token  authorization token for Dropbox API
     * @param string $project       name of project and version
     * @param string $projectFolder name of the folder to upload into
     */
    public function __construct($token, $project, $projectFolder){
        $this->dbxClient = new Dropbox\Client($token, $project);
        $this->projectFolder = $projectFolder;
    }

    /**
     * upload set the file or directory to upload
     * @param  [type] $dirtocopy [description]
     * @return [type]            [description]
     */
    public function upload($dirtocopy, $db_exclude){
		$DB_EXCLUDE = explode(',', $db_exclude);

        if(!file_exists($dirtocopy)){

            exit("File $dirtocopy does not exist");

        } else {

            //if dealing with a file upload it
            if(is_file($dirtocopy)){
                $dirtocopy = str_replace("\\", "/", $dirtocopy);
                $this->uploadFile($dirtocopy);

            } else { //otherwise collect all files and folders
				$dirtocopy	= str_replace("\\", "/", $dirtocopy);
				$exclude	= array_merge($this->ignoreList(), $DB_EXCLUDE);
				$filter		= function ($file, $key, $iterator) use ($exclude) {
					if (!in_array($file->getFilename(), $exclude)) {
						return true;
					}
				};

				$innerIterator = new RecursiveDirectoryIterator(
					$dirtocopy,
					RecursiveDirectoryIterator::SKIP_DOTS
				);

				$iterator = new RecursiveIteratorIterator(
					new RecursiveCallbackFilterIterator($innerIterator, $filter),
	                \RecursiveIteratorIterator::SELF_FIRST,
	                \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
				);

                //loop through all entries
                foreach ($iterator as $pathname => $fileInfo) {
					$file = str_replace(WT_DATA_DIR, "", $fileInfo);
					$file = str_replace("\\", "/", $file);
                    $this->uploadFile($file);
                }

            }
        }
    }

    /**
     * uploadFile upload file to dropbox using the Dropbox API
     * @param  string $file path to file
    */
    public function uploadFile($file){
        $f = fopen($file, "rb");
        $this->dbxClient->uploadFile("/$file", Dropbox\WriteMode::add(), $f);
        fclose($f);
    }

    /**
     * ignoreList array of filenames or directories to ignore
     * @return array
    */
    public function ignoreList(){
        return array(
            '.gitignore',
			'.DS_Store',
            '.htaccess',
            'config.ini.php',
            'cache',
			'index.php'
        );
    }
}
