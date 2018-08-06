<?php
namespace Swoft\Bean\Resource;

use Swoft\Helper\ComposerHelper;

trait CustomComponentsRegister
{
    /**
     * Register the custom components namespace
     * @author limx
     */
    public function registerCustomComponentsNamespace()
    {
        foreach ($this->customComponents as $ns => $componentDir) {
            if (is_int($ns)) {
                $ns = $componentDir;
                $componentDir = ComposerHelper::getDirByNamespace($ns);
            }

            $this->componentNamespaces[] = $ns;

            foreach ($this->serverScan as $dir) {
                $scanDir = $componentDir . DS . $dir;
                if (!is_dir($scanDir)) {
                    continue;
                }

                $scanNs = $ns . "\\" . $dir;
                $this->scanNamespaces[$scanNs] = $scanDir;
            }
        }
    }
}