<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

namespace Concrete\Package\PdfDesigner\Src;

defined('C5_EXECUTE') or die("Access Denied.");

use File;
use Core;

class Helpers
{

    /**
     *
     * @return string
     */
    public static function getTempFolder()
    {
        return sys_get_temp_dir();
    }

    public static function generateURL($path)
    {
        return BASE_URL . "/" . DIR_REL . "/" . $path;
    }

    /**
     *
     * @return string
     */
    public static function generateTempFile()
    {
        return tempnam(self::getTempFolder(), '');
    }

    /**
     * @param string $fileData
     * @return string
     */
    public function createTempFile($fileData)
    {
        /** @var $fh \Concrete\Core\File\Service\File */
        $fh = Core::make("helper/file");
        $fileName = self::generateTempFile();
        $fh->append($fileName, $fileData);
        return $fileName;
    }

    /**
     *
     * @param Concrete\Core\File\Version $fileObject
     *
     * @return mixed
     */
    public static function getAbsolutePath($fileObject)
    {
        $basePath = DIR_APPLICATION;

        if (is_object($fileObject)) {
            $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

            $currentLocation = $fileObject->getFileStorageLocationObject();

            if (is_object($currentLocation)) {
                $currentFilesystem = $currentLocation->getFileSystemObject();

                if (is_object($currentFilesystem)) {
                    $adapter = $currentFilesystem->getAdapter();

                    if (is_object($adapter)) {
                        $basePath = $adapter->getPathPrefix();
                    }
                }
            }

            $cf = $app->make('helper/concrete/file');

            $path = $cf->prefix($fileObject->getPrefix(), $fileObject->getFileName());

            $path = $basePath . \League\Flysystem\Util::normalizePath($path);

            return $path;
        } else {
            return false;
        }
    }

    /**
     *
     * @param integer $fID
     *
     * @return string
     */
    public static function getAbsolutePathByFileId($fID)
    {
        $filePath = "";

        if (intval($fID) > 0) {
            $fileObject = File::getById($fID);

            if (is_object($fileObject)) {
                $filePath = self::getAbsolutePath($fileObject);
            }
        }

        return $filePath;
    }

    /**
     *
     * @param string $url
     * @return string
     */
    public static function fetchUrl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response === false) {
            return "";
        } else {
            return $response;
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @return string
     */
    public static function convertSimpleXmlObjectToText($xml)
    {
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
