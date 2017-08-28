<?php

namespace WorldFixer;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Cache;
use pocketmine\level\Level;

/**
 * Class WorldFixer
 * @package WorldFixer
 */
class WorldFixer extends PluginBase implements Listener{

    /** @var array selectors */
    public $selectors = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @param CommandSender $sender
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {

        if(!$sender instanceof Player){
            $sender->sendMessage("§cThis command can be used only ingame!");
            return false;
        }

        if(in_array($cmd->getName(), ["wf", "worldfixer"])){
            if(empty($args[0])){
                $sender->sendMessage(TextFormat::RED."Use /wf help for help");
                return false;
            }
            switch(strtolower($args[0])){
                case "fix":
                    if(!$sender->hasPermission("wf.command.fix") && !$sender->isOp()){
                        $sender->sendMessage($cmd->getPermissionMessage());
                        return false;
                    }
                    if(!$this->isPosSet($sender)){
                        $sender->sendMessage(TextFormat::RED."You must select both positions first!");
                        return false;
                    }

                    list($x1, $y1, $z1, $level1) = explode(':', $this->selectors[strtolower($sender->getName())]['pos1']);
                    list($x2, $y2, $z2, $level2) = explode(':', $this->selectors[strtolower($sender->getName())]['pos2']);

                    if($level1 !== $level2){
                        $sender->sendMessage(TextFormat::RED."Both positions must be in the same level!");
                        return false;
                    }

                    $level = Server::getInstance()->getLevel($level1);

                    $count = $this->fix($level, $x1, $y1, $z1, $x2, $y2, $z2);

                    $sender->sendMessage(TextFormat::GREEN."Selected area successfully fixed ({$count} block changed)!");
                    return false;
                case "wand":
                    if(!$sender->hasPermission("wf.command.wand") && !$sender->isOp()){
                        $sender->sendMessage($cmd->getPermissionMessage());
                        return false;
                    }
                    if($this->isSelector($sender)){
                        return false;
                    }
                    $this->selectors[strtolower($sender->getName())]['ins'] = $sender;
                    $this->selectors[strtolower($sender->getName())]['block'] = 1;
                    $sender->sendMessage(TextFormat::GREEN."Now select two blocks");
                    return false;
                case "help":
                    $sender->sendMessage("§e> WorldFixer help <\n".
                        "§a/wf wand §7select two positions\n".
                        "§a/wf fix §7fix all blocks and slabs in world");
                    return false;
                default:
                    $sender->sendMessage(TextFormat::RED."Use /wf help for help");
                    return false;
            }
        }
    }

    /**
     * @param Player $p
     * @return bool
     */
    public function isSelector(Player $p){
        return isset($this->selectors[strtolower($p->getName())]['block']) && $this->selectors[strtolower($p->getName())]['block'] > 0;
    }

    /**
     * @param BlockBreakEvent $e
     */
    public function onBlockBreak(BlockBreakEvent $e){
        $p = $e->getPlayer();
        $b = $e->getBlock();
        if($this->isSelector($p)){
            if($this->selectors[strtolower($p->getName())]['block'] === 1){
                $e->setCancelled();
                $this->selectors[strtolower($p->getName())]['pos1'] = "$b->x:$b->y:$b->z:{$b->level->getId()}";
                $this->selectors[strtolower($p->getName())]['block'] = 2;
                $p->sendMessage(TextFormat::GREEN."Selected the first position at $b->x, $b->y, $b->z");
                return;
            }
            if($this->selectors[strtolower($p->getName())]['block'] === 2){
                $e->setCancelled();
                $this->selectors[strtolower($p->getName())]['pos2'] = "$b->x:$b->y:$b->z:{$b->level->getId()}";
                $this->selectors[strtolower($p->getName())]['block'] = 0;
                $p->sendMessage(TextFormat::GREEN."Selected the second position at $b->x, $b->y, $b->z");
                return;
            }
        }
    }

    /**
     * @param Player $p
     * @return bool
     */
    public function isPosSet(Player $p){
        if(isset($this->selectors[strtolower($p->getName())]['pos1']) && isset($this->selectors[strtolower($p->getName())]['pos2'])){
            return true;
        }
        return false;
    }

    /**
     * @param Level $level
     * @param $x1
     * @param $y1
     * @param $z1
     * @param $x2
     * @param $y2
     * @param $z2
     * @return int $count
     */
    public function fix(Level $level, $x1, $y1, $z1, $x2, $y2, $z2):int{

        if(!$level instanceof Level) $level = $this->getServer()->getLevel(intval($level));

        $pos1 = new Vector3(min($x1, $x2), min($y1, $y2), min($z1, $z2));
        $pos2 = new Vector3(max($x1, $x2), max($y1, $y2), max($z1, $z2));

        $temporalVector = new Vector3();

        $count = 0;
        $maxCount = abs($pos1->x - $pos2->x) * abs($pos1->y - $pos2->y) * abs($pos1->z - $pos2->z);

        for($x = $pos1->x; $x < $pos2->x; $x++){
            for($z = $pos1->z; $z < $pos2->z; $z++) {

                $count++;

                $this->getLogger()->info("\rMaking ".$count."/".$maxCount." ...");

                for ($y = $pos1->y; $y < $pos2->y; $y++) {

                    $id = $level->getBlockIdAt($x, $y, $z);
                    $meta = $level->getBlockDataAt($x, $y, $z);

                    $temporalVector->setComponents($x, $y, $z);

                    switch($id){
                        case 126:
                            $level->setBlockIdAt($x, $y, $z, 158);
                            break;
                        case 95:
                            $level->setBlockIdAt($x, $y, $z, 20);
                            break;
                        case 160:
                            $level->setBlockIdAt($x, $y, $z, 102);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                        case 125:
                            $level->setBlockIdAt($x, $y, $z, 157);
                            break;
                        case 188:
                            $level->setBlockIdAt($x, $y, $z, Item::FENCE);
                            $level->setBlockDataAt($x, $y, $z, 1);
                            break;
                        case 189:
                            $level->setBlockIdAt($x, $y, $z, Item::FENCE);
                            $level->setBlockDataAt($x, $y, $z, 2);
                            break;
                        case 190:
                            $level->setBlockIdAt($x, $y, $z, Item::FENCE);
                            $level->setBlockDataAt($x, $y, $z, 3);
                            break;
                        case 191:
                            $level->setBlockIdAt($x, $y, $z, Item::FENCE);
                            $level->setBlockDataAt($x, $y, $z, 4);
                            break;
                        case 192:
                            $level->setBlockIdAt($x, $y, $z, Item::FENCE);
                            $level->setBlockDataAt($x, $y, $z, 5);
                            break;
                        case 166:
                            // barrier -> invisible bedrock
                            $level->setBlockIdAt($x, $y, $z, 95);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                        case 144:
                            // mob heads -> air
                            $level->setBlockIdAt($x, $y, $z, 0);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                        case 84:
                            $level->setBlockIdAt($x, $y, $z, 25);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                        case Item::END_ROD:
                            $level->setBlockIdAt($x, $y, $z, Item::GRASS_PATH);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                        case Item::GRASS_PATH:
                            $level->setBlockIdAt($x, $y, $z, Item::END_ROD);
                            $level->setBlockDataAt($x, $y, $z, 0);
                            break;
                    }
                }
            }
        }

        return $count;
    }
}
