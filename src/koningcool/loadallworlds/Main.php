<?php

declare(strict_types=1);

namespace koningcool\loadallworlds;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use function array_diff;
use function scandir;
use pocketmine\plugin\Plugin;


interface PluginIdentifiableCommand{

	/**
	 * @return Plugin
	 */
	public function getPlugin() : Plugin;
}



class Main extends PluginBase{
    public $cfg;  

    private function loadWorlds() : void{
        foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName){
            if($this->getServer()->loadLevel($levelName));           
        }    
    }    

    public function onLoad() : void{
            $this->getLogger()->info(TextFormat::DARK_BLUE . "LoadAllWorlds Loaded!");}

    public function onEnable() : void{
        $this->getLogger()->info(TextFormat::DARK_GREEN . "LoadAllWorlds Enabled!");

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
        if($this->cfg["load-on-startup"] === true){
            $this->getLogger()->info(TextFormat::DARK_GREEN . "true");
                $this->loadWorlds(); 
            
        }
 }

    public function onDisable() : void{
        $this->getLogger()->info(TextFormat::DARK_RED . "LoadAllWorlds Disabled!");}

        
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch($command->getName()){
            case "loadall":
                $this->loadWorlds();              
        }
        return true;   
	}    
}
