<?php

namespace Mysli\Lib;


/**
 * File System Class
 * -----------------------------------------------------------------------------
 * @author     Avrelia.com (Marko Gajst)
 * @copyright  Copyright (c) 2010, Avrelia.com
 * @license    http://framework.avrelia.com/license
 */
class FileSystem
{

    /**
     * Get file's content
     * --
     * @param   string  $fileName
     * @param   string  $create     Create file, if doesn't exists
     * --
     * @return  string
     */
    public static function Read($fileName, $create=false)
    {
        $fileName = ds($fileName);

        if (file_exists($fileName) and !is_dir($fileName))
        {
            if (!$contents = file_get_contents($fileName, 1)) {
                Log::err("Error while reading file: `{$fileName}`.");
                return false;
            }
            return $contents;
        }
        elseif ($create) {
            Log::inf("File doesn't exists: `{$fileName}`, we'll try to create it.");
            self::Write('', $fileName);
            return '';
        }
        else {
            Log::err("Not a valid file: `{$fileName}`.");
            return false;
        }
    }
    //-

    /**
     * Delete All Files / Folders (can be filtered) In Selected Folder, will return number of deleted files
     * --
     * @param   string  $fullPath   If the path is directory, the whole directory will be removed, if it's file, the file will be removed.
     * @param   string  $filter     There are various options available for filter:
     *                              - use '*', for all files, '*-something' or 'something.*' or 'something-*-more'
     *                              - enter filename - to just delete file (as path enter directory)
     *                              - regular expression: /[a-z]/i, matched files will be deleted
     *                              - false, if you want to remove $fullPath itself (if dir all files in it will be removed)
     * @param   boolean $deepScan   If you provided filter, should sub-directories also be checked?
     * @param   boolean $matchDir   If true, then will delete whole directory when its name is matched by filter, otherwise only files will be matched.
     * --
     * @return  integer Number of deleted files / directories; false if failed!
     */
    public static function Remove($path, $filter=false, $deepScan=false, $matchDir=true)
    {
        # Local copy of ignore list
        $fsIgnore = Cfg::get('core/dir/ignore', array());

        # If we don't have filter we must remove just one file / folder
        if (!$filter) {
            if (is_dir($path)) {
                $Files = scandir($path);
                $count = 0;
                foreach ($Files as $file) {
                    if ($file === '.' || $file === '..') { continue; }
                    if (in_array($file, $fsIgnore)) { continue; }

                    $newPath = ds($path . '/' . $file);
                    $count = $count + self::Remove($newPath, false);
                }
                $count = $count + rmdir($path);
                return $count;
            }
            else {
                if (file_exists($path)) {
                    if (unlink($path)) {
                        return 1;
                    }
                    else {
                        Log::war("Failed to remove: `{$path}`!");
                        return false;
                    }
                }
                else {
                    Log::war("File not found: `{$path}`, can't remove it!");
                    return false;
                }
            }
        } # We have filter!
        else {
            # It obviously must exists and be directory, if not,
            # it's just a file and we'll remove it in regular way - if it does exists of course.
            if (!is_dir($path)) {
                return self::Remove($path, false);
            }

            # We don't have regular expression as a filter?
            if (substr($filter, 0, 1) !== '/') {
                # Escape the string
                $filter = preg_quote($filter);

                # Restore * character
                $filter = str_replace('\*', '.*?', $filter);

                # Set appropriate format for regular expression
                $filter = '/^' . $filter . '$/';
            }

            # Scan the directory now
            $Files = scandir($path);

            if (empty($Files)) {
                Log::inf("No files found in directory: `{$path}`, nothing will be removed.");
                return 0;
            }

            $count = 0;

            foreach ($Files as $file) {
                if ($file === '.' || $file === '..') { continue; }
                if (in_array($file, $fsIgnore)) { continue; }

                $newPath = ds($path . '/' . $file);
                # Do we have a match?
                if (preg_match($filter, $file)) {
                    # Remove directory or file
                    if ((is_dir($newPath) && $matchDir) || !is_dir($newPath)) {
                        $count = $count + self::Remove($newPath, false);
                        continue;
                    }
                }

                # No match or is directory which shouldn't be removed, so need to be scanned, perhaps?
                if (is_dir($newPath) && $deepScan) {
                    $count = $count + self::Remove($newPath, $filter, $deepScan, $matchDir);
                }
            }

            return $count;
        }
    }
    //-


    /**
     * Save Content To File
     * --
     * @param   string  $content
     * @param   string  $fileName   Full path + file name
     * @param   boolean $fileAppend Add data to existing file
     * @param   boolean $makeDir    Create directory if doesn't exists (set mask or false)
     * --
     * @return  boolean
     */
    public static function Write($content, $fileName, $fileAppend=false, $makeDir=0755)
    {
        $fileName = ds($fileName);

        # Check For File Append
        if ($fileAppend) {
            $fileAppend = FILE_APPEND;
        }
        else {
            $fileAppend = 0;
        }

        # Delete It If Exists
        if ($fileAppend === 0) {
            if (file_exists($fileName)) {
                unlink($fileName);
            }
        }

        # Create directrory if doesn't exists...
        if ($makeDir !== false) {
            $directory = dirname($fileName);
            if (!is_dir($directory)) {
                self::MakeDir($directory, true, $makeDir);
            }
        }

        if ($filePointer = fopen($fileName, ($fileAppend ? 'a' : 'w'))) {
            if (fwrite($filePointer, $content) === false) {
                Log::err("Filed to write to file: `{$fileName}`.");
                return false;
            }

            if (fclose($filePointer) !== false) {
                Log::inf("Saved: `{$fileName}`.");
                return true;
            }
            else {
                Log::err("Filed to close file: `{$fileName}`.");
                return false;
            }
        }
        else {
            Log::err("Faild to open file: `{$fileName}`.");
            return false;
        }
    }
    //-

    /**
     * Copy File...
     * --
     * @param   string  $source         Source fullpath + file / if is dir, the whole folder will be copied
     * @param   string  $destination    Must be only destination folder (full path)
     * @param   string  $newFileName    If false, filename will be taken from source!
     * @param   string  $exists         If file exists: false|'replace'|'rename' (return false | replace original file | rename current file)
     * --
     * @return  boolean
     */
    public static function Copy($source, $destination, $newFileName=false, $exists=false)
    {
        # Should we ignore it?
        $nameOnly = basename($source);
        if (in_array($nameOnly, Cfg::get('core/dir/ignore', array()))) {
            Log::inf("This file/folder was set to be ignored on copy: `{$nameOnly}`!");
            return true;
        }

        if (is_dir($source)) {
            $num = 0;

            if (!is_dir($destination)) {
                if (self::MakeDir($destination)) {
                    Log::inf("Folder was created: `{$destination}`.");
                }
                else {
                    Log::err("Error while creating folder: `{$destination}`.");
                }
            }

            $d = dir($source);

            while (false !== ($entry = $d->read()))
            {
                if ($entry == '.' || $entry == '..') continue;

                $Entry = $source . '/' . $entry;

                if (is_dir($Entry)) {
                    $num = $num + self::Copy($Entry, $destination.'/'.$entry);
                    continue;
                }
                if (!file_exists($destination.'/'.$entry)) {
                    if (!copy($Entry, $destination.'/'.$entry)) {
                        Log::err("Error on copy: `{$Entry}` to: `{$destination}/{$entry}`.");
                    }
                    else {
                        $num++;
                    }
                }
            }

            $d->close();

            return $num;
        }
        else {
            // Copy Files...
            if (!file_exists($source)) {
                Log::war("Source file doesn't exists: `{$source}`.");
                return false;
            }

            if (!is_dir($destination)) {
                Log::war("Destination isn't directory: `{$destination}`.");
                return false;
            }

            # Get source file name
            if (!$newFileName) {
                $sourceFileName = self::FileName($source);
            }
            else {
                $sourceFileName = $newFileName;
            }

            if (file_exists(ds($destination.'/'.$sourceFileName))) {
                Log::inf("File exists: `".ds($destination.'/'.$sourceFileName).'`.');

                if ($exists === false) {
                    Log::war("File exists, we'll return false.");
                    return false;
                }
                elseif($exists == 'replace') {
                    Log::inf("We'll replace original file.");
                }
                elseif ($exists == 'rename') {
                    $sourceFileName = self::UniqueName($sourceFileName, $destination);
                }
            }

            if (!self::IsWritable($destination)) {
                Log::war("Destination folder isn't writable: `{$destination}`.");
                return false;
            }

            if (copy($source, ds($destination.'/'.$sourceFileName))) {
                Log::inf("File was copied: `{$source}`, to: `".ds($destination.'/'.$sourceFileName).'`.');
                return true;
            }
            else {
                Log::err("Error, can't copy file: `{$source}`, to: `".ds($destination.'/'.$sourceFileName).'`.');
                return false;
            }
        }
    }
    //-

    /**
     * Will create unique filename. This will check if file/folder with provided name already exists.
     * If you're using this on file, the system will keep the extention, so you can enter $baseFilename with it!
     * --
     * @param   string  $baseFilename   Only filename, not full path!
     * @param   string  $destinationDir Full absolute destination path
     * @param   boolean $isFile         Are we generating UniqueName for file or folder?
     * @param   string  $divider        Divider for new filename, example:
     *                                  if divider is "_" then mynewfile will become mynewfile_1
     * --
     * @return  string
     */
    public static function UniqueName($baseFilename, $destinationDir, $isFile=true, $divider='_')
    {
        if ($isFile)
        {
            if (file_exists(ds($destinationDir."/{$baseFilename}"))) {
                $ext  = self::Extension($baseFilename);
                $base = self::FileName($baseFilename, true);
                $n    = 1;
                do {
                    $baseFilename = $base . $divider . $n . (empty($ext) ? '' : '.' . $ext);
                    $n++;
                }
                while(file_exists(ds($destinationDir.'/'.$baseFilename)));
                Log::inf("New destination filename: `{$baseFilename}`.");
            }

            return $baseFilename;
        }
        else {
            if (is_dir(ds($destinationDir."/{$baseFilename}"))) {
                $n    = 1;
                $base = $baseFilename;
                do {
                    $baseFilename = $base . $divider . $n;
                    $n++;
                }
                while(is_dir(ds($destinationDir.'/'.$baseFilename)));
                Log::inf("New destination filename: `{$baseFilename}`.");
            }

            return $baseFilename;
        }
    }
    //-

    /**
     * Search All Directories For Specific File Type
     * --
     * @param   string  $directory  Absolute path
     * @param   string  $fileType   You can use | to search for more file types example: jpg|jpeg|png|gif|bmp
     * @param   boolean $deepScan   Will search sub directories too
     * @param   string  $filter     (it will take filename without extention, won't apply for directories) *something | something* | *something*
     * --
     * @return  array
     */
    public static function Find($directory, $fileType, $deepScan=true, $filter=false)
    {
        $directory = trim($directory); # Removed \/

        $fileTypeArr = explode('|', $fileType);

        if (!is_dir($directory)) {
            Log::war("Can't find files, directory doesn't exist: `{$directory}`.");
            return false;
        }

        $List = scandir($directory);
        unset($List[0], $List[1]);

        $Files = array();

        if (is_array($List) && !empty($List)) {
            foreach ($List as $item) {
                if (is_dir($directory.'/'.$item) && $deepScan) {
                    $NewFiles = self::Find($directory.'/'.$item, $fileType);
                    if (is_array($NewFiles)) {
                        $Files = array_merge($NewFiles, $Files);
                    }
                }
                else {
                    if (in_array(self::Extension($item), $fileTypeArr)) {

                        if ($filter) {
                            if ((substr($filter,0,1) == '*') && (substr($filter,-1,1) == '*') ) {
                                $type   = 'mid';
                                $find = substr($filter,1,-1);
                            }
                            elseif (substr($filter,0,1) == '*') {
                                $type = 'end';
                                $find = substr($filter,1);
                                $len  = strlen($find);
                            }
                            elseif (substr($filter,-1,1) == '*') {
                                $type = 'start';
                                $find = substr($filter,0,-1);
                                $len  = strlen($find);
                            }
                            else {
                                Log::war("Wrong filter provided: `{$filter}`.");
                                return false;
                            }

                            $baseFileName = basename($item, '.'.self::Extension($item));

                            if ( (($type == 'mid')   && (strpos($baseFileName, $find))) OR
                                 (($type == 'end')   && (substr($baseFileName,-$len,$len) == $find)) OR
                                 (($type == 'start') && (substr($baseFileName,0,$len) == $find)) )
                            {
                                $Files[] = array(
                                    'directory' => $directory,
                                    'file' => $item
                                );
                                continue;
                            }
                            else {
                                continue;
                            }
                        }

                        $ext       = self::Extension($item);
                        $file_only = substr($item, 0, -strlen($ext)-1);

                        $Files[] = array(
                            'full'      => ds($directory.'/'.$item),
                            'directory' => $directory,
                            'file'      => $item,
                            'file_only' => $file_only,
                            'ext'       => $ext,
                        );
                    }
                }
            }
        }

        return $Files;
    }
    //-

    /**
     * Will scan folder, and return array of (md5) signatures.
     * --
     * @param   string  $directory      Directory which you want to scan
     * @param   boolean $deepScan       Should sub-directories be included too?
     * @param   string  $subDirectory   You can leave this as it is -- since this will be used in recursion
     * --
     * @return  array
     */
    public static function GetSignatures($directory, $deepScan=true, $subDirectory=null)
    {
        $Directory = scandir($directory.'/'.$subDirectory);
        $Files = array();
        foreach ($Directory as $d) {
            if (substr($d,0,1)=='.') continue;
            if (is_dir(ds($directory.'/'.$subDirectory.'/'.$d))) {
                if (!$deepScan) continue;
                $Files = array_merge($Files, self::GetSignatures($directory, $deepScan, ds($subDirectory.'/'.$d)));
            }
            else {
                $Files[ds($subDirectory.'/'.$d)] = md5_file(ds($directory.'/'.$subDirectory.'/'.$d));
            }
        }
        return $Files;
    }
    //-

    /**
     * Return File Extension
     * --
     * @param   string  $fileName
     * --
     * @return  string
     */
    public static function Extension($fileName)
    {
        preg_match('/\.([a-zA-Z0-9]+)$/i', $fileName, $fileExt);
        $fileExt = (isset($fileExt[1])) ? strtolower($fileExt[1]) : '';
        return $fileExt;
    }
    //-

    /**
     * Get only filename from full path (example: /my_dir/sample/another/my_file.ext => my_file.ext | my_file)
     * --
     * @param   string  $fullPath
     * @param   boolean $noExtension    No ext?
     * --
     * @return  string
     */
    public static function FileName($fullPath, $noExtension=false)
    {
        $name = basename($fullPath);

        if ($noExtension) {
            $ext  = self::Extension($name);
            $name = basename($name, '.'.$ext);
        }

        return $name;
    }
    //-

    /**
     * Generates uniqute filename, you must provide full aboslute path.
     * Note, this method doesn't check if file already exists; it just md5 the filename,
     * and based on that make sure, that two files on filesytem can't have same filename.
     *
     * If you need actually unique filename (with checking of existance),
     * then use method UniqueName()
     * --
     * @param   string  $file
     * --
     * @return  string
     */
    public static function UniqueFilename($file)
    {
        # Make Sha1
        $file_sha  = vString::Hash($file, false);

        # Get Just Base Filename, without extention!
        $file_base = basename($file);
        $file_base = Str::clean($file_base, 'aA1', false, 100);

        # Get All Directories as array, and select the one,
        # in which is file
        $File = ds($file);
        $File = explode(DIRECTORY_SEPARATOR, $file);
        if (is_array($File)) {
            $dir_before = $File[count($File)-2];
            $dir_before = Str::clean($dir_before, 'aA1', false, 100);
        }

        # Create New Filename
        $newFilename = $file_sha . '_' . $dir_before . '_' . $file_base;
        $newFilename = Str::clean($newFilename, 'aA1', '_', false);

        return $newFilename;
    }
    //-

    /**
     * Convert size (from bytes) to nicer (human readable) value (kb, mb)
     * --
     * @param   integer $size   (bytes)
     * --
     * @return  string
     */
    public static function FormatSize($size)
    {
        if ($size < 1024) {
            return $size . ' bytes';
        }
        elseif ($size < 1048576) {
            return round($size/1024) . ' KB';
        }
        else {
            return round($size/1048576, 1) . ' MB';
        }
    }
    //-

    /**
     * Create Directory
     * --
     * @param   string  $folderName Must be full path, + new folder's name
     * @param   boolean $recursive
     * @param   integer $mode       0755 Read and write for owner, read for everybody else
     * --
     * @return  boolean
     */
    public static function MakeDir($folderName, $recursive=true, $mode=0755)
    {
        $folderName = ds($folderName);

        if (!is_dir($folderName)) {
            $oldumask = umask(0);
            if ( mkdir($folderName, $mode, $recursive) ) {
                Log::inf("Folder: `{$folderName}` was added.");
                $return = true;
            }
            else {
                Log::err("Error while creating folder: `{$folderName}`.");
                $return = false;
            }
            umask($oldumask);
            return $return;
        }
        else {
            Log::war("Folder already exists: `{$folderName}`.");
            return false;
        }
    }
    //-

    /**
     * Create Many Directories
     * --
     * @param   string  $root           Root dir
     * @param   array   $Directories    Array of directories to create (you can
     *                                  enter whole path like this:
     *                                  array('mydir', 'another/something_else', 'first/second/third'))
     * @param   integer $mode           0755 Read and write for owner, read for everybody else
     * --
     * @return  integer Number of creted directories
     */
    public static function MakeDirTree($root, $Directories, $mode=0755)
    {
        $result = 0;

        if (!is_dir($root)) {
            Log::err("Root is not valid directory: `{$root}`.");
            return false;
        }

        if (is_array($Directories) and !empty($Directories)) {
            foreach ($Directories as $dir) {
                if (self::MakeDir($root.'/'.$dir, true, $mode)) {
                    $result++;
                }
            }
        }
        else {
            Log::err("Provided list of directory was empty - or wasn't an array.");
            return false;
        }

        return $result;
    }
    //-

    /**
     * Check If Directory Is Writable
     * --
     * @param   string  $directory
     * ---
     * @return  boolean
     */
    public static function IsWritable($directory)
    {
        $directory = ds($directory);

        Log::inf("Check if directory is writable: `{$directory}`.");

        # Check If Provided Path Is Valid
        if (!is_dir($directory)) {
            Log::war("Invalid path was provided.");
            return false;
        }

        # Default function - if returns false,
        # then we know it isn't writable...
        if (!is_writable($directory)) {
            return false;
        }

        # In other case, we'll check (by trying create an directory)
        # Set Dir Name (must be unique - and shouldn't exists)
        do {
            $dir = ds($directory.'/___write_test_dir_avrelia_'.rand(0,200).time());
        }
        while(!is_dir($directory));

        # Now, try to create it, check if exists, delete it and check if doesn't exists (anymore) - if that goes fine - we'll return true...
        if (@mkdir($dir)):
            if (is_dir($dir)):
                if (@rmdir($dir)):
                    if (!is_dir($dir)) return true;
                endif;
            endif;
        endif;
        # Funny --- -----------

        return false;
    }
    //-

    /**
     * Select file or folder (return object, with file / folder related methods)
     * --
     * @param   string  $path
     * --
     * @return  FileSystemSelect
     */
    public static function Select($path)
    {
        return new FileSystemSelect($path);
    }
    //-
}
//--