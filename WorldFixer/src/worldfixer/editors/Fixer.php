<?php

namespace worldfixer\editors;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use worldfixer\task\FixTask;

class Fixer {

    private static $blocks = [
        158 => [Block::WOODEN_SLAB, 0],
        125 => [Block::DOUBLE_WOODEN_SLAB, 0],
        188 => [Block::FENCE, 0],
        189 => [Block::FENCE, 1],
        190 => [Block::FENCE, 2],
        191 => [Block::FENCE, 3],
        192 => [Block::FENCE, 4],
        193 => [Block::FENCE, 5],
        166 => [Block::INVISIBLE_BEDROCK, 0],
        144 => [Block::AIR, 0], // mob heads
        208 => [Block::GRASS_PATH, 0],
        198 => [Block::END_ROD, 0],
        #3 => [Block::PODZOL, 0],
        126 => [Block::WOODEN_SLAB, 0],
        95 => [Block::STAINED_GLASS, ""],
        #160 => [Block::STAINED_GLASS_PANE, 0],
        199 => [Block::CHORUS_PLANT, 0],
        202 => [Block::PURPUR_BLOCK, 0],
        251 => [Block::CONCRETE, 0],
        204 => [Block::PURPUR_BLOCK, 0]
    ];

    /**
     * @param $x1
     * @param $y1
     * @param $z1
     * @param $x2
     * @param $y2
     * @param $z2
     * @param Level $level
     * @return int
     */
    public static function fix($x1, $y1, $z1, $x2, $y2, $z2, Level $level, Player $player):int {
        $count = 0;
        $dataToFill = [];
        for($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
            for ($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
                for ($z = min($z1, $z2); $z <= max($z1, $z2); $z++) {
                    $id = $level->getBlock(new Vector3($x, $y, $z))->getId();
                    if(isset(self::$blocks[$id])) {
                        $meta = self::$blocks[$id][1];
                        if(is_string($meta)) $meta = $level->getBlock(new Vector3($x, $y, $z))->getDamage();
                        array_push($dataToFill, [new Position($x, $y, $z, $level), Block::get(self::$blocks[$id][0], $meta)]);
                        $count++;
                    }
                }
            }
        }
        FixTask::addToFix($dataToFill, $player);
        return $count;
    }
}
