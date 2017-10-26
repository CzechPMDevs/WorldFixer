<?php

namespace worldfixer\editors;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use worldfixer\WorldFixer;

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
        198 => [Block::END_ROD, 0]
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
    public static function fix($x1, $y1, $z1, $x2, $y2, $z2, Level $level):int {
        $count = 0;
        $dataToFill = [];
        for($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
            for ($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
                for ($z = min($z1, $z2); $z <= max($z1, $z2); $z++) {
                    $id = $level->getBlock(new Vector3($x, $y, $z))->getId();
                    var_dump(self::$blocks);
                    if(isset(self::$blocks[$id])) {
                        array_push($dataToFill, [new Position($x, $y, $z, $level), Block::get(self::$blocks[$id][0], self::$blocks[$id][1])]);
                        WorldFixer::getInstance()->getLogger()->debug("§a{$count} block will fixed");
                        $count++;
                    }
                }
            }
        }
        return $count;
    }
}