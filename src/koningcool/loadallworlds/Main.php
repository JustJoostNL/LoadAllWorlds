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

interface PluginIdentifiableCommand
{
    /**
     * @return Plugin
     */
    public function getPlugin() : Plugin;
}


class Main extends PluginBase
{
    private $allWorldsLoaded;
    private function loadWorlds() : void
    {
        $allWorldsLoaded=true;
        
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName) {
            if (!$this->getServer()->loadLevel($levelName)) {
                $allWorldsLoaded=false;
            }
        }
        if ($allWorldsLoaded===true) {
            $this->getLogger()->info(TextFormat::DARK_RED . "All worlds are already loaded!");
            //
        }
    }
    public function onLoad() : void
    {
        //$this->getLogger()->info(TextFormat::DARK_BLUE . "LoadAllWorlds Loaded!");
    }

    public function onEnable() : void
    {
        //$this->getLogger()->info(TextFormat::DARK_GREEN . "LoadAllWorlds Enabled!");
        $this->saveDefaultConfig();
        $this->reloadConfig();
        if ($this->getConfig()->get("load-on-startup") === true) {
            $this->loadWorlds();
        }
    }

    public function onDisable() : void
    {
        //$this->getLogger()->info(TextFormat::DARK_RED . "LoadAllWorlds Disabled!");
    }
        
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        switch ($command->getName()) {
            case "loadall":
                $this->loadWorlds();
        }
        return true;
    }
}
