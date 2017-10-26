<?php

namespace worldfixer\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use worldfixer\editors\Fixer;
use worldfixer\WorldFixer;

/**
 * Class WorldFixerCommand
 * @package worldfixer\command
 */
class WorldFixerCommand extends Command implements PluginIdentifiableCommand, Listener {

    /** @var  array $selectors */
    private $selectors;

    public function __construct() {
        parent::__construct("worldfixer", "WorldFixer commands", null, ["wf"]);
        Server::getInstance()->getPluginManager()->registerEvents($this, $this->getPlugin());
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cThis command can be used only ingame!");
            return;
        }

        if (empty($args[0])) {
            $sender->sendMessage("§cUse /wf help for help");
            return;
        }
        switch (strtolower($args[0])) {
            case "fix":
                if (!$sender->hasPermission("wf.command.fix") && !$sender->isOp()) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    return;
                }
                if (!$this->isPosSet($sender)) {
                    $sender->sendMessage("§cYou must select both positions first!");
                    return;
                }
                list($x1, $y1, $z1, $level1) = explode(':', $this->selectors[strtolower($sender->getName())]['pos1']);
                list($x2, $y2, $z2, $level2) = explode(':', $this->selectors[strtolower($sender->getName())]['pos2']);
                if ($level1 !== $level2) {
                    $sender->sendMessage("§cBoth positions must be in the same level!");
                    return;
                }
                $level = Server::getInstance()->getLevel($level1);
                $count = Fixer::fix($x1, $y1, $z1, $x2, $y2, $z2, $level, $sender);
                $sender->sendMessage("§aSelected area successfully fixed ({$count} block changed)!");
                return;
            case "wand":
                if (!$sender->hasPermission("wf.command.wand") && !$sender->isOp()) {
                    $sender->sendMessage("§cYou have not permissions to use this command!");
                    return;
                }
                if ($this->isSelector($sender)) {
                    return;
                }
                $this->selectors[strtolower($sender->getName())]['ins'] = $sender;
                $this->selectors[strtolower($sender->getName())]['block'] = 1;
                $sender->sendMessage("§aNow select two blocks");
                return;
            case "help":
                $sender->sendMessage("§e> WorldFixer help <\n" .
                    "§a/wf wand §7select two positions\n" .
                    "§a/wf fix §7fix blocks and slabs in selected area");
                return;
            default:
                $sender->sendMessage("§cUse /wf help for help");
                return;
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
                $p->sendMessage("§aSelected the first position at $b->x, $b->y, $b->z");
                return;
            }
            if($this->selectors[strtolower($p->getName())]['block'] === 2){
                $e->setCancelled();
                $this->selectors[strtolower($p->getName())]['pos2'] = "$b->x:$b->y:$b->z:{$b->level->getId()}";
                $this->selectors[strtolower($p->getName())]['block'] = 0;
                $p->sendMessage("§aSelected the second position at $b->x, $b->y, $b->z");
                return;
            }
        }
    }

    /**
     * @param Player $p
     * @return bool
     */
    public function isPosSet(Player $p){
        return isset($this->selectors[strtolower($p->getName())]['pos1']) && isset($this->selectors[strtolower($p->getName())]['pos2']);
    }

    public function getPlugin(): Plugin {
        return WorldFixer::getInstance();
    }

}
