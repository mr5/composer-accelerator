<?php
/**
 * Created by PhpStorm.
 * User: decent
 * Date: 15/6/18
 * Time: 下午10:28
 */

namespace Mr5\ComposerAccelerator;


use Composer\Config;
use Composer\Util\RemoteFilesystem;

class QiniuFileSystem extends RemoteFilesystem
{
    /**
     * @var Config
     */
    protected $config;

    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function get($originUrl, $fileUrl, $additionalOptions = array(), $fileName = null, $progress = true)
    {
        $downloadReplacements = $this->getConfig()->get('download-replacements');
        foreach ($downloadReplacements as $search => $replacement) {
            if (strpos($fileUrl, $search) === 0) {
                $fileUrl = $replacement . substr($fileUrl, strlen($search));
                $originUrl = parse_url($replacement)['host'];
                break;
            }
        }

        return parent::get($originUrl, $fileUrl, $additionalOptions, $fileName, $progress);
    }
}