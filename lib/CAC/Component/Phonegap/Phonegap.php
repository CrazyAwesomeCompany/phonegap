<?php
namespace CAC\Component\Phonegap;


use CAC\Component\PhonegapApi\PhonegapApi;

class Phonegap
{
    /**
     *
     * @var PhonegapApi
     */
    private $api;

    /**
     *
     * @var ReleaseBuilder
     */
    private $builder;

    public function __construct($api, $builder)
    {
        $this->api = $api;
        $this->builder = $builder;
    }

    public function build($id, $name, $version, $contents, $saveDir)
    {
        // Create the release package
        $package = $this->createPackage($name, $version, $contents, $saveDir);
        // Send the new package to Phonegap
        $result = $this->api->updateApplicationPackage($id, $package);

        return $result;
    }

    /**
     * Create a package ready to be send to Phonegap build service
     *
     * @param string $name    Name of the package
     * @param string $version Version number of the package
     * @param string $path    Path to files to put in archive
     * @param string $saveDir Directory where package should be placed
     */
    public function createPackage($name, $version, $path, $saveDir)
    {
        // Create the package archive name
        $packageName = sprintf('%s-%s.zip', $name, $version);

        $saveFile = $saveDir . DIRECTORY_SEPARATOR . $packageName;
        return $this->builder->package($path, $saveFile, $version);
    }
}