<?php
/**
 * Created by PhpStorm.
 * User: decent
 * Date: 15/6/18
 * Time: 下午9:44
 */

namespace Mr5\ComposerAccelerator;


use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;

class Accelerator implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Apply plugin modifications to composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::PRE_FILE_DOWNLOAD => array(
                array('onPreFileDownload', 0)
            ),
        );
    }

    public function onPreFileDownload(PreFileDownloadEvent $event)
    {
        $downloadReplacements = $this->composer->getConfig()->get('download-replacements');
        $searched = false;
        foreach ($downloadReplacements as $search => $replacement) {
            if (strpos($event->getProcessedUrl(), $search) === 0) {
                $searched = true;
                break;
            }
        }
        if ($searched) {
            $qiniu = new FileSystem(
                $this->io,
                $this->composer->getConfig(),
                $event->getRemoteFilesystem() ? $event->getRemoteFilesystem()->getOptions() : array()
            );
            $qiniu->setConfig($this->composer->getConfig());
            $event->setRemoteFilesystem($qiniu);
        }
    }
}