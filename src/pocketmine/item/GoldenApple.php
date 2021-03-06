<?php

namespace pocketmine\item;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class GoldenApple extends Item {
	
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::GOLDEN_APPLE, 0, $count, "Golden Apple");
	}

	public function food(): int {
		return 4;
	}

	public static $food = ['food' => 4, 'saturation' => 9.6];

	public function onConsume(Entity $human){
		$pk = new EntityEventPacket();
		$pk->eid = $human->getId();
		$pk->event = EntityEventPacket::USE_ITEM;
		if($human instanceof Player){
			$human->dataPacket($pk);
		}
		Server::broadcastPacket($human->getViewers(), $pk);

		// food logic
		$human->setFood(min(Player::FOOD_LEVEL_MAX, $human->getFood() + self::$food['food']));
		$human->setSaturation(min ($human->getFood(), $human->getSaturarion() + self::$food['saturation']));

		$position = [ 'x' => $human->getX(), 'y' => $human->getY(), 'z' => $human->getZ() ];
		$human->sendSound("SOUND_BURP", $position, 63);

		$human->addEffect(Effect::getEffect(Effect::REGENERATION)->setAmplifier(1)->setDuration(5 * 20));
		$human->addEffect(Effect::getEffect(Effect::ABSORPTION)->setAmplifier(0)->setDuration(120 * 20));

		$human->setAbsorption(4);

		if($human instanceof Player && $human->getGamemode() === 1){
			return;
		}

		if ($this->count == 1) {
			$human->getInventory()->setItemInHand(Item::get(self::AIR));
		} else {
			--$this->count;

			$human->getInventory()->setItemInHand($this);
		}
	}
}