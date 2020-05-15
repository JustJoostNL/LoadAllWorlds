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
     /** @var Config */
    public $myConfig;
    public $cfg;  

    private function loadWorlds() : void{
        foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName){
            if($this->getServer()->loadLevel($levelName));           
        }    
    }    
    public function onLoad() : void{
            //$this->getLogger()->info(TextFormat::DARK_BLUE . "LoadAllWorlds Loaded!");
    }

    public function onEnable() : void{
        //$this->getLogger()->info(TextFormat::DARK_GREEN . "LoadAllWorlds Enabled!");
        if (file_exists($this->getDataFolder() . "config.yml")){
            $this->getLogger()->info(TextFormat::DARK_GREEN . $this->getConfig()->get("load-on-startup"));
            if($this->getConfig()->get("load-on-startup") === true){

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
        if($this->cfg["load-on-startup"] === true){
            $this->getLogger()->info(TextFormat::DARK_GREEN . "true");
                $this->loadWorlds(); 
            }  
        }
        else{
            @mkdir($this->getDataFolder());
            $this->saveResource("config.yml");
            $this->myConfig = new Config($this->getDataFolder() . "config.yml", Config::YAML);
            $this->saveDefaultConfig();
            $this->getConfig()->get("load-on-startup");

        }
        }
    }

    public function onDisable() : void{
        //$this->getLogger()->info(TextFormat::DARK_RED . "LoadAllWorlds Disabled!");
    }
        
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch($command->getName()){
            case "loadall":
                $this->loadWorlds();              
        }
        return true;   
	}    
}