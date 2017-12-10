<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Effect;
use pocketmine\entity\Living;

class Potion extends Item implements Consumable{

	private static $effects = [];

	public static function init(){
		$data = json_decode(file_get_contents(\pocketmine\PATH . "src/pocketmine/resources/potions.json"), true);

		if(!is_array($data)){
			throw new \RuntimeException("Potions data could not be read");
		}

		foreach($data as $name => $type){
			$effects = [];

			if(!is_array($type["effects"])){
				throw new \RuntimeException("'effects' for potion '$name' is not an array");
			}
			foreach($type["effects"] as $effectData){
				if(!is_string($effectData["name"])){
					throw new \RuntimeException("'name' key for potion '$name' is not a string");
				}

				$effect = Effect::getEffectByName($effectData["name"]);
				if($effect === null){
					throw new \RuntimeException("Found unknown effect for potion '$name': " . $effectData["name"]);
				}

				if(isset($effectData["amplifier"])){
					$effect->setAmplifier((int) $effectData["amplifier"]);
				}

				if(isset($effectData["duration"])){
					$effect->setDuration(((int) $effectData["duration"]) * 20);
				}

				$effects[] = $effect;
			}

			self::$effects[(int) $type["id"]] = $effects;
			self::$effects[$name] = $effects;
		}
	}

	/**
	 * @param int $id
	 * @return Effect[]
	 */
	public static function getPotionEffectsById(int $id) : array{
		if(isset(self::$effects[$id])){
			return array_map(function(Effect $effect) : Effect{ return clone $effect; }, self::$effects[$id]);
		}

		return [];
	}

	/**
	 * @param string $name
	 * @return Effect[]
	 */
	public static function getPotionEffectsByName(string $name) : array{
		if(isset(self::$effects[$name])){
			return array_map(function(Effect $effect) : Effect{ return clone $effect; }, self::$effects[$name]);
		}

		return [];
	}

	public function __construct(int $meta = 0){
		parent::__construct(self::POTION, $meta, "Potion");
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onConsume(Living $consumer){

	}

	public function getAdditionalEffects() : array{
		//TODO: check CustomPotionEffects NBT
		return self::getPotionEffectsById($this->meta);
	}

	public function getResidue(){
		return Item::get(Item::GLASS_BOTTLE);
	}
}
