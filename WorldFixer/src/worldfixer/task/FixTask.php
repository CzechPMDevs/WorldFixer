<?php

namespace worldfixer\task;

use pocketmine\block\Block;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

/**
 * Class FixTask
 * @package worldfixer\task
 */
class FixTask extends Task {

    /** @var int $fillTime */
    private static $fillTime = 4;

    /** @var array $toFix */
    public static $toFix = [];

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        $blocks = 0;
        foreach (self::$toFix as $playerName => $data) {
            foreach ($data as $d) {
                $position = $d[0];
                $block = $d[1];
                if($position instanceof Position && $block instanceof Block) {
                    $position->getLevel()->setBlock($position->asVector3(), $block);
                    $blocks++;
                }
            }
            if(count($data) == 1) {
                $player = Server::getInstance()->getPlayerExact($playerName);
                if($player instanceof Player){
                    $player->sendMessage("§aSelected area successfully fixed!");
                }
            }
            if($blocks == self::$fillTime) return;
        }
    }

    /**
     * @param array $data
     * @param Player $player
     */
    public static function addToFix(array $data, Player $player) {
        if(isset(self::$toFix[strtolower($player->getName())])) {
            $player->sendMessage("§cYou are now fixing anything, please wait.");
        }
        else {
            self::$toFix[strtolower($player->getName())] = $data;
        }
    }
}
