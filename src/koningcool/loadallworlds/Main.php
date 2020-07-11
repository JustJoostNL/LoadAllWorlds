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
    private $debugMode = false;
    
    private function loadWorlds(string $excludelist, bool $showInfo) : void
    {
        $loadedLevelsBefore = intval(count($this->getServer()->getLevels()));
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Worlds loaded before: " . $loadedLevelsBefore);
        }
        
        # Get appropriate exclude list (on-load, on-command, default = no list)
        switch ($excludelist) {
            case "on-load":
                $exclude = $this->getConfig()["on-startup"]["exclude"];
                break;
            case "on-command":
                $exclude = $this->getConfig()["on-command"]["exclude"];
                break;
            default:
                $exclude = "";
                break;
        }

        # Load the levels
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName) {
            # Only load level if not in exclude list, which can be empty
            if (!in_array($levelName, $exclude)) {
                $this->getServer()->loadLevel($levelName);
            }
        }

        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "All worlds are loaded!");
        }
        $loadedLevelsAfter = intval(count($this->getServer()->getLevels()));
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Worlds loaded after: " . $loadedLevelsAfter);
        }
        if ($this->debugMode === true) {
            $loadedLevelsDiff = $loadedLevelsAfter - $loadedLevelsBefore;
        }
        if ($loadedLevelsAfter > $loadedLevelsBefore) {
            if ($showInfo === true) {
                $this->getLogger()->info(TextFormat::DARK_GREEN . "All worlds are loaded.");
            }
        } else {
            if ($showInfo === true) {
                $this->getLogger()->info(TextFormat::DARK_RED . "No extra worlds loaded!");
            }
        }
        if ($showInfo === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "All worlds are loaded!");
        }
    }

    public function onLoad() : void
    {
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_BLUE . "LoadAllWorlds Loaded!");
        }
    }

    public function onEnable() : void
    {
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "LoadAllWorlds Enabled!");
        }
        $this->saveDefaultConfig();
        $this->reloadConfig();
        if ($this->getConfig()["on-startup"]["load-worlds"] === true) {
            $this->loadWorlds("on-load", false); # use on-load exclude list
        }
        $this->debugMode = $this->getConfig()->get("debug");
    }

    public function onDisable() : void
    {
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_RED . "LoadAllWorlds Disabled!");
        }
    }
        
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        switch ($command->getName()) {
            case "loadall":
                $this->loadWorlds("", true); # do not use any exclude list
                break;
            case "loadworlds":
                $this->loadWorlds("on-command", true); # use on-command exclude list
                break;
        }
        return true;
    }
}
