<?php
declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Bean\Resource;

use Swoft\Helper\ComponentHelper;

/**
 * {@inheritDoc}
 */
class ServerAnnotationResource extends AnnotationResource
{
    /**
     * {@inheritDoc}
     */
    public function registerNamespace()
    {
        $hostDir = \dirname(__FILE__, 4);
        if (\in_array(\basename($hostDir), ['swoft', 'src'])) {
            // Install via Composer
            $componentDirs = scandir($hostDir);
        } else {
            // Independent
            $componentDirs = ['swoft-framework'];
        }
        foreach ($componentDirs as $component) {
            if ($component == '.' || $component == '..') {
                continue;
            }

            $componentDir = $hostDir . DS . $component;
            $componentCommandDir = $componentDir . DS . 'src';
            if (! is_dir($componentCommandDir)) {
                continue;
            }

            $namespace = ComponentHelper::getComponentNamespace($component, $componentDir);
            $this->componentNamespaces[] = $namespace;

            // Console component
            if ($component === $this->consoleName) {
                $this->scanNamespaces[$namespace] = $componentCommandDir;
                continue;
            }

            foreach ($this->serverScan as $dir) {
                $scanDir = $componentCommandDir . DS . $dir;
                if (! is_dir($scanDir)) {
                    continue;
                }

                $scanNamespace = $namespace . '\\' . $dir;
                $this->scanNamespaces[$scanNamespace] = $scanDir;
            }
        }
    }
}
