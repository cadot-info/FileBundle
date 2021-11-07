<?php

namespace Cadotinfo\FileBundle\Service;

use Symfony\Component\HttpFoundation\Response;

class FileFunctionsService
{
    // pour extraire l'extension d'un fichier
    public function extension($file): string
    {
        return (strtolower(pathinfo($file, PATHINFO_EXTENSION)));
    }

    // for copy dir with recursivity
    function copydir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copydir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    $this->copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    //remove dir with recursivity
    function deletedir($dir)
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
    //move directory with recursivity
    function movedir($origine, $destination)
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
    }
    /* ------------------------------------------------------------------------------------------------------------------ */
    /*                                                                                POUR AVOIR UN NOM DE FICHIER PROPRE */
    /* ------------------------------------------------------------------------------------------------------------------ */
    function sanitize($filename, $beautify = true)
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
    /* ------------------------------------------------------------------------------------------------------------------ */
    /*                                                                       POUR RENDRE LE NOM DE FICHIER SANS ESPACE... */
    /* ------------------------------------------------------------------------------------------------------------------ */
    function beautify($filename)
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
