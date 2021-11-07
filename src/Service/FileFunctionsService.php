<?php
/*
 * Created on Sun Nov 07 2021 Cadot.info,licence webmestre@cadot.info
 * 
 * many licences
 *
 *
 *-------------------------------------------------------------------------- *
 *      FileFunctionsService.php *
 * -------------------------------------------------------------------------- *
 *
 * Usage:
 * - extension (name of file)             extract extension of filename
 * - copydir(source, destination)         copy a directory recursively  
 * - deletedir(directory)                 remove directory recursively
 * - movedir (directory)                  move directory with recursivity
 * - sanitize (filename, remove space?)   clean a filename
 * - beautify (filename)                  for remove space in string
 *
 * Source on:many website, i can add copyrigth, mail me, thanks
 */

namespace Cadotinfo\FileBundle\Service;

use Symfony\Component\HttpFoundation\Response;

/**
 * FileFunctionsService
 * functions manipulate file for symfony 5
 */
class FileFunctionsService
{

    /**
     * extension extract of filename
     *
     * @param  string $filename
     * @return string extension without point
     */
    public function extension(string $filename): string
    {
        return (strtolower(pathinfo($filename, PATHINFO_EXTENSION)));
    }


    /**
     * copydir for copy dir with recursivity
     *
     * @param  string $src source directory
     * @param  string $dst destination
     * @return bool
     */
    function copydir(string $src, string $dst): bool
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copydir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
        return file_exists($dir);
    }


    /**
     * deletedir remove dir with recursivity
     *
     * @param  string $dir
     * @return bool
     */
    function deletedir(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deletedir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * movedir move directory with recursivity
     *
     * @param  string $origine
     * @param  string $destination
     * @return bool
     */
    function movedir(string $origine, string $destination): bool
    {

        //création des réperoitre de destination s'il n'existe pas
        $dir = explode('/', $destination);
        unset($dir[count($dir) - 1]);
        $ddir = '';
        foreach ($dir as $val) {
            $ddir .= $val . '/';
            if (!file_exists($ddir)) mkdir($ddir);
        }
        rename($origine, $destination);
        return file_exists($destination);
    }

    /**
     * sanitize cleaner of filename, remove many character
     * option for remove spaces
     *
     * @param  string $filename
     * @param  bool $beautify for remove sapce
     * @return string
     */
    function sanitize(string $filename, bool $beautify = true): string
    {
        // sanitize filename
        $filename = preg_replace(
            '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
            '-',
            $filename
        );
        // avoids ".", ".." or ".hiddenFiles"
        $filename = ltrim($filename, '.-');
        // optional beautification
        if ($beautify) $filename = $this->beautify($filename);
        // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
        return $filename;
    }

    /**
     * beautify remove space in filename or string
     *
     * @param  mixed $filename
     * @return string
     */
    function beautify(string $filename): string
    {
        // reduce consecutive characters
        $filename = preg_replace(array(
            // "file   name.zip" becomes "file-name.zip"
            '/ +/',
            // "file___name.zip" becomes "file-name.zip"
            '/_+/',
            // "file---name.zip" becomes "file-name.zip"
            '/-+/'
        ), '-', $filename);
        $filename = preg_replace(array(
            // "file--.--.-.--name.zip" becomes "file.name.zip"
            '/-*\.-*/',
            // "file...name..zip" becomes "file.name.zip"
            '/\.{2,}/'
        ), '.', $filename);
        // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
        $filename = mb_strtolower($filename, mb_detect_encoding($filename));
        // ".file-name.-" becomes "file-name"
        $filename = trim($filename, '.-');
        return $filename;
    }
}
