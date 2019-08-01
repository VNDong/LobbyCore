<?php

namespace FreeGamingHere;

use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\utils\Terminal;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent; 
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\entity\Snowball;
use pocketmine\entity\Egg;
use pocketmine\level\Explosion;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\entity\EffectInstance;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as C;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\inventory\ArmorInventory;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\scheduler\Task as PluginTask;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;

use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\RainSplashParticle;
use pocketmine\level\particle\HeartParticle;


use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\BlazeShootSound;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class LobbyCore extends PluginBase implements Listener {
	
	//Boots
	public $heart = array("HearthBoots");
	public $jump = array("JumpBoots");
	public $speed = array("SpeedBoots");
	public $water = array("WaterBoots");
	
	//Player Visibility
	public $showall = array("1234567890PLAYER");
	public $showvips = array("1234567890PLAYER");
	public $shownone = array("1234567890PLAYER");
	
	//Colorful Armor Gadget
	public $rarmor = [];
	
	//Particles
	public $particle1 = array("RedCircleParticles");
	public $particle2 = array("YellowCircleParticles");
	public $particle3 = array("GreenCircleParticles");
	public $particle4 = array("BlueCircleParticles");
	public $particle5 = array("OringeCircleParticles");
	public $particle6 = array("FireCircleParticles");
	public $particle7 = array("WaterCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle8 = array("DropsCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle9 = array("EnderDropsCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle10 = array("RainParticles");
	public $particle11 = array("LavaParticles");
	public $particle12 = array("FireWingParticles");
	public $particle13 = array("RedstoneWingParticles");
	public $particle14 = array("GreenWingParticles");
	public $particle15 = array("LavaWalkingParticles");
	public $particle16 = array("LavaWalkingParticles"); //Ideas needed
	public $particle17 = array("LavaWalkingParticles"); //Ideas needed
	public $particle18 = array("LavaWalkingParticles"); //Ideas needed
	public $particle19 = array("LavaWalkingParticles"); //Ideas needed
	public $particle20 = array("LavaWalkingParticles"); //Ideas needed
	
	/*
	public $particle = array("Particle");
	*/
	
	//Capes
	public $capes = [
		'MineconCape2011',
		'MineconCape2012',
		'MineconCape2013',
		'MineconCape2015',
		'MineconCape2016',
	];
	
	//Advertising
	public $links = [".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "nethergames", "fallentech", "mineplex"];
	
	public function onEnable() {
		
		//Config
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml");
		$this->saveResource("key.yml");
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$key = new Config($this->getDataFolder() . "key.yml", Config::YAML);
		
		$prefix = $cfg->get("Prefix");
		$network = $cfg->get("ServerName");
		$status = $cfg->get("Status");
		
		if(empty($key->get("key"))){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)! Please message him through Discord (FreeGamingHere#6456) to get the activation key!");
			$this->getServer()->shutdown();
			
		} elseif($key->get("key") !== "trenichd2019"){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)! Please message him through Discord (FreeGamingHere#6456) to get the activation key!");
			$this->getServer()->shutdown();
			
		} elseif($this->getDescription()->getAuthors()[0] !== "FreeGamingHere" or $this->getDescription()->getName() !== "LobbyCore"){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			$this->getServer()->shutdown();
			
		//} elseif(!file_exists($this->getServer()->getDataPath() . "plugins/LobbyCore_v1.0.0.phar")){
			
			//$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			//$this->getServer()->shutdown();
			
		} else {
			
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			
			$this->getLogger()->info(TextFormat::GREEN . "LobbyCore by FreeGamingHere Enabled");
			
			$this->ZMusicBox = $this->getServer()->getPluginManager()->getPlugin("ZMusicBox");
			
			$this->getScheduler()->scheduleRepeatingTask(new ItemsLoad($this), 5);
			
			$this->getScheduler()->scheduleRepeatingTask(new SpawnParticles($this), 10);
			
			//It Requires GD + LibPNG Extensions Installed
			//$this->getScheduler()->scheduleRepeatingTask(new WingParticles($this), 10);
			
			$this->getScheduler()->scheduleRepeatingTask(new TypeType($this), 20);
			
			//$this->getScheduler()->scheduleRepeatingTask(new RainbowArmor($this), 15);
			
			$this->getServer()->getNetwork()->setName(TextFormat::BOLD . TextFormat::RED . $network . TextFormat::RESET . TextFormat::BLUE . "> " . $status . TextFormat::RESET);
			
			$this->getServer()->getDefaultLevel()->setTime(1000);
			$this->getServer()->getDefaultLevel()->stopTime();
			
			$cfg->set("OpenChest1", false);
			$cfg->set("OpenChest2", false);
			$cfg->save();
			
		}
	}
	
	public function onDisable() {
		
		$this->getLogger()->info(TextFormat::RED . "LobbyCore by FreeGamingHere Disabled");
		
		if($this->getDescription()->getAuthors()[0] !== "FreeGamingHere" or $this->getDescription()->getName() !== "LobbyCore"){
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			$this->getServer()->shutdown();
		}
	}
				
	public function onPickup(InventoryPickupItemEvent $event){
		$player = $event->getInventory()->getHolder();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$event->setCancelled();
		}
	}
	
	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$event->setCancelled();
		}
	}
	
	public function onChat(PlayerChatEvent $event) {
		$msg = $event->getMessage();
		$player = $event->getPlayer();
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$prefix = $cfg->get("Prefix");
		foreach($this->links as $links) {
			if(strpos($msg, $links)) {
				$player->sendMessage($prefix . TextFormat::RED . "Do not try to advertise! Advertising will lead you to a perm ban!");
				$event->setCancelled();
				return;
			}
		} 
	}
	
	public function getParticleItems(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$cred = Item::get(351, 1, 1);
		$cred->setCustomName(TextFormat::RESET . TextFormat::RED . "Red " . TextFormat::GOLD . "Circle Particles");
		
		$cblue = Item::get(351, 4, 1);
		$cblue->setCustomName(TextFormat::RESET . TextFormat::BLUE . "Blue " . TextFormat::GOLD . "Circle Particles");
		
		$cyellow = Item::get(351, 11, 1);
		$cyellow->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Yellow " . TextFormat::GOLD . "Circle Particles");
		
		$cgreen = Item::get(351, 2, 1);
		$cgreen->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Green " . TextFormat::GOLD . "Circle Particles");
		
		$coringe = Item::get(351, 14, 1);
		$coringe->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Orange " . TextFormat::GOLD . "Circle Particles");
		
		$cfire = Item::get(377, 0, 1);
		$cfire->setCustomName(TextFormat::RESET . TextFormat::RED . "Fire " . TextFormat::GOLD . "Circle Particles");
		
		$page2 = Item::get(459, 0, 1);
		$page2->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Particles Page 2");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $cred);
		$inv->setItem(1, $cblue);
		$inv->setItem(2, $cgreen);
		$inv->setItem(3, $cyellow);
		$inv->setItem(4, $coringe);
		$inv->setItem(5, $cfire);
		$inv->setItem(7, $page2);
		$inv->setItem(8, $exit);
		
	}
	
	public function getPage2(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$rain = Item::get(353, 0, 1);
		$rain->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Rain " . TextFormat::GOLD . "Particles");
		
		$lava = Item::get(426, 0, 1);
		$lava->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Particles");
		
		$wfire = Item::get(382, 0, 1);
		$wfire->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Fire " . TextFormat::GOLD . "Wing Particles");
		
		$wredstone = Item::get(331, 1, 1);
		$wredstone->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Redstone " . TextFormat::GOLD . "Wing Particles");
		
		$wgreen = Item::get(338, 0, 1);
		$wgreen->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_GREEN . "Green " . TextFormat::GOLD . "Wing Particles");
		
		$wplava = Item::get(351, 3, 1);
		$wplava->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Walking Particles");
		
		$page3 = Item::get(281, 0, 1);
		$page3->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Particles Page 3" . TextFormat::RED . " | Coming Soon...");
		
		$back = Item::get(351, 1, 1);
		$back->setCusto
