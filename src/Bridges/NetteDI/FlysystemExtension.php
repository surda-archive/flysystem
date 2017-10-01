<?php

namespace Surda\Flysystem\Bridges\NetteDI;


use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;


class FlysystemExtension extends CompilerExtension
{

    const
        TAG_FLYSYSTEM_PLUGIN = 'flysystem.plugin';

    /** @var array */
    public $defaults = [
        'filesystem'   => [
            'adapter' => null,
            'plugins' => [],
            'config'  => null,
        ],
        'mountmanager' => [
            'filesystems' => [],
            'plugins'     => [],
        ],
        'plugins'      => [],

    ];

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        /**
         * Default plugins
         */
        foreach ($config['plugins'] as $pluginName => $plugin) {
            $this->buildPlugin($plugin, 'plugin.' . $pluginName, self::TAG_FLYSYSTEM_PLUGIN);
        }

        /**
         * Filesystem
         */
        if ($config['filesystem']['adapter'] !== null) {

            $this->buildAdapter($config['filesystem'], 'filesystem.adapter');
            $fileSystem = $this->buildFilesystem($config['filesystem'], 'filesystem', 'filesystem.adapter', true);

            foreach ($builder->findByTag(self::TAG_FLYSYSTEM_PLUGIN) as $serviceName => $meta) {
                $fileSystem->addSetup('addPlugin', ['@' . $serviceName]);
            }

            foreach ($config['filesystem']['plugins'] as $key => $item) {
                $this->buildPlugin($item, 'filesystem.plugin.' . $key);
                $fileSystem->addSetup('addPlugin', [$this->prefix('@filesystem.plugin.' . $key)]);
            }
        }

        /**
         * Mount manager
         */
        if (count($config['mountmanager']['filesystems'])) {
            $mountManager = $builder->addDefinition($this->prefix('mountmanager'))
                ->setClass(MountManager::class);

            foreach ($config['mountmanager']['filesystems'] as $filesystemPrefixName => $item) {
                if (isset($item['adapter'])) {

                    $this->buildAdapter($item, 'mountmanager.filesystem.' . $filesystemPrefixName . '.adapter');
                    $fileSystem = $this->buildFilesystem($config['filesystem'], 'mountmanager.filesystem.' . $filesystemPrefixName, 'mountmanager.filesystem.' . $filesystemPrefixName . '.adapter');

                    foreach ($builder->findByTag(self::TAG_FLYSYSTEM_PLUGIN) as $serviceName => $meta) {
                        $fileSystem->addSetup('addPlugin', ['@' . $serviceName]);
                    }

                    if (isset($item['plugins'])) {
                        foreach ($item['plugins'] as $pluginName => $plugin) {
                            $this->buildPlugin($plugin, 'mountmanager.filesystem.' . $filesystemPrefixName . '.plugin.' . $pluginName);
                            $fileSystem->addSetup('addPlugin', [$this->prefix('@mountmanager.filesystem.' . $filesystemPrefixName . '.plugin.' . $pluginName)]);
                        }
                    }

                    $mountManager->addSetup('mountFilesystem', [$filesystemPrefixName, $fileSystem]);
                }
            }

            foreach ($config['mountmanager']['plugins'] as $pluginName => $plugin) {
                $this->buildPlugin($plugin, 'mountmanager.plugin.' . $pluginName);
                $mountManager->addSetup('addPlugin', [$this->prefix('@mountmanager.plugin.' . $pluginName)]);
            }
        }

    }

    /**
     * @param Statement|string $plugin
     * @param string           $prefix
     * @param string|null      $tag
     * @param bool             $autowired
     * @return ServiceDefinition
     */
    public function buildPlugin($plugin, $prefix, $tag = null, $autowired = false)
    {
        $builder = $this->getContainerBuilder();
        $service = $builder->addDefinition($this->prefix($prefix))->setAutowired($autowired);

        if ($tag !== null) {
            $service->addTag($tag);
        }

        if (is_string($plugin)) {
            $service->setClass((new Statement($plugin))->getEntity(), []);
        }
        else {
            $service->setClass($plugin->getEntity(), $plugin->arguments);
        }

        return $service;
    }

    /**
     * @param array  $config
     * @param string $prefix
     * @param bool   $autowired
     * @return ServiceDefinition
     */
    public function buildAdapter($config, $prefix, $autowired = false)
    {
        $builder = $this->getContainerBuilder();
        $service = $builder->addDefinition($this->prefix($prefix))->setAutowired($autowired);

        if (is_string($config['adapter'])) {
            if (isset($config['arguments'])) {
                $service->setClass((new Statement($config['adapter']))->getEntity(), [$config['arguments']]);
            }
            else {
                $service->setClass((new Statement($config['adapter']))->getEntity());
            }
        }
        else {
            if (isset($config['arguments'])) {
                $service->setClass($config['adapter']->getEntity(), [$config['arguments']]);
            }
            else {
                $service->setClass($config['adapter']->getEntity(), $config['adapter']->arguments);
            }
        }

        return $service;
    }

    /**
     * @param array  $config
     * @param string $filesystemPrefix
     * @param string $adapterPrefix
     * @param bool   $autowired
     * @return ServiceDefinition
     */
    public function buildFilesystem($config, $filesystemPrefix, $adapterPrefix, $autowired = false)
    {
        $builder = $this->getContainerBuilder();
        $service = $builder->addDefinition($this->prefix($filesystemPrefix))->setAutowired($autowired);

        $service->setClass(Filesystem::class, [$this->prefix('@' . ltrim($adapterPrefix, '@')), isset($config['config']) ? $config['config'] : null]);

        return $service;
    }
}
