<?php

namespace worldfixer;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use worldfixer\command\WorldFixerCommand;
use worldfixer\task\FixTask;

/**
 * Class WorldFixer
 * @package WorldFixer
 */
class WorldFixer extends PluginBase implements Listener{

    /** @var array selectors */
    public $selectors = [];

    /** @var  WorldFixer $instance */
    private static $instance;

    public function onEnable(){
        self::$instance = $this;
        $this->getServer()->getCommandMap()->register("WorldFixer", new WorldFixerCommand);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new FixTask, 1);
    }

    public static function getInstance() {
        return self::$instance;
    }
}
