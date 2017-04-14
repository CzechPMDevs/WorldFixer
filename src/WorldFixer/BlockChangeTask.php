<?php

namespace WorldFixer;

use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class BlockChangeTask extends AsyncTask{

    private $chunks;
    private $pos1;
    private $pos2;
    private $levelId;
    private $chunkClass;

    private $color;
    private $slabs;

    public function __construct(array $chunks, Vector3 $pos1, Vector3 $pos2, $levelId, $chunkClass, $slabs = true, $color = true){
        $this->chunks = serialize($chunks);
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
        $this->levelId = $levelId;
        $this->chunkClass = $chunkClass;
        $this->slabs = $slabs;
        $this->color = $color;
    }

    public function onRun(){
        $chunkClass = $this->chunkClass;
        /** @var  Chunk[] $chunks */
        $chunks = unserialize($this->chunks);
        foreach($chunks as $hash => $binary){
            $chunks[$hash] = $chunkClass::fromBinary($binary);
        }

        for($x = $this->pos1->x; $x <= $this->pos2->x; $x++){
            for($z = $this->pos1->z; $z <= $this->pos2->z; $z++){
                $hash = Level::chunkHash($x >> 4, $z >> 4);
                $chunk = null;

                if(isset($chunks[$hash])){
                    $chunk = $chunks[$hash];
                }

                if($chunk !== null && $this->color){
                    $chunk->setBiomeColor($x, $z, 108, 151, 47);
                }

                for($y = $this->pos1->y; $y <= $this->pos2->y; $y++){
                    if($chunk !== null){
                        $id = $chunk->getBlockId($x, $y, $z);
                        $meta = $chunk->getBlockData($x, $y, $z);

                        switch($id){
                            case 126:
                                $chunk->setBlock($x, $y, $z, 158, $meta);
                                break;
                            case 95:
                                $chunk->setBlock($x, $y, $z, 20);
                                break;
                            case 188:
                                $chunk->setBlock($x, $y, $z, Item::FENCE, 1);
                                break;
                            case 189:
                                $chunk->setBlock($x, $y, $z, Item::FENCE, 2);
                                break;
                            case 190:
                                $chunk->setBlock($x, $y, $z, Item::FENCE, 3);
                                break;
                            case 191:
                                $chunk->setBlock($x, $y, $z, Item::FENCE, 4);
                                break;
                            case 192:
                                $chunk->setBlock($x, $y, $z, Item::FENCE, 5);
                                break;
                            case 166:
                                $chunk->setBlock($x, $y, $z, 95);
                                break;
                            case 144:
                                $chunk->setBlock($x,$y,$z,0);
                                break;
                            case 84:
                                $chunk->setBlock($x,$y,$z,25);
                                break;
                        }
                    }
                }
            }
        }

        $this->setResult($chunks);
    }

    public function onCompletion(Server $server)
    {
        $chunks = $this->getResult();
        $level = $server->getLevel($this->levelId);
        if ($level != null) {
            foreach ($chunks as $hash => $chunk) {
                Level::getXZ($hash, $x, $z);
                $level->setChunk($x, $z, $chunk);
            }
        }
    }
}
