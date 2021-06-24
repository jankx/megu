<?php
namespace Jankx\Megu\Abstracts;

use Jankx\Megu\Constracts\Extension as ExtensionConstract;

abstract class Extension implements ExtensionConstract
{
    /**
     * Call this method after the extension is loaded
     *
     * Default do not run any actions
     */
    public function execute()
    {
    }
}
