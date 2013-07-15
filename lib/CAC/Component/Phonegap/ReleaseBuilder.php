<?php
namespace CAC\Component\Phonegap;


class ReleaseBuilder
{
    /**
     * Create a package ready to be send to Phonegap Builder
     *
     * @param string $contents Path to the files
     * @param string $package  Package name to create
     * @param string $version  Version number of the package
     */
    public function package($contents, $package, $version)
    {
        // Create the package
        $this->createArchive($package, $contents);
        // Adjust the version number
        $this->setVersionNumber($package, $version);

        return $package;
    }

    /**
     * Update the version number in the package to the given number.
     *
     * This method opens the archive to locate the config.xml file. When found it will adjust the version
     * number to the given one.
     *
     * @param string $archive Path to archive
     * @param string $version Version number
     *
     */
    public function setVersionNumber($archive, $version)
    {
        $zip = new \ZipArchive();
        $zip->open($archive, \ZipArchive::CHECKCONS);

        $index = $zip->locateName('config.xml', \ZIPARCHIVE::FL_NODIR);
        $filename = $zip->getNameIndex($index);
        $data = $zip->getFromIndex($index);

        //echo $data;
        $xml = simplexml_load_string($data);
        //print_r($xml);
        $xml->attributes()->version = $version;

        $zip->addFromString($filename, $xml->saveXML());

        $zip->close();
    }

    public function compressFiles($archive)
    {
        $zip = new \ZipArchive();
        $zip->open($archive, \ZipArchive::CHECKCONS);

        $compressor = new GZipCompress();
        $numFiles = $zip->numFiles;
        for ($i=0; $i < $numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extension = pathinfo($filename, \PATHINFO_EXTENSION);

            switch ($extension) {
                case 'css':
                    $data = $zip->getFromIndex($i);
                    $compressed = $compressor->minifyCss($data);
                    $zip->addFromString($filename, $compressed);
                    break;
            }
        }
    }



    public function createArchive($archive, $path)
    {
        $zip = new \ZipArchive();
        $result = $zip->open($archive, \ZipArchive::OVERWRITE);

        if ($result) {
            $zip = $this->addDirectoryToZip($zip, $path, realpath($path . '/../') . '\\');
            $zip->close();
        }

        return $zip;
    }

    protected function addDirectoryToZip($zip, $dir, $base)
    {
        $newFolder = str_replace($base, '', $dir);
        $zip->addEmptyDir($newFolder);
        foreach(glob($dir . '/*') as $file)
        {
            if(is_dir($file))
            {
                $zip = $this->addDirectoryToZip($zip, $file, $base);
            }
            else
            {
                if (strpos($file, 'cordova.js') !== false || strpos($file, 'phonegap.js') !== false) {
                    continue;
                }
                $newFile = str_replace($base, '', $file);
                $zip->addFile($file, $newFile);
            }
        }
        return $zip;
    }
}