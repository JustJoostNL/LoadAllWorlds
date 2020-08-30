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
    private $configData = null;
    
    private function loadWorlds(string $excludelist, bool $showInfo) : void
    {
        $loadedLevelsBefore = intval(count($this->getServer()->getLevels()));
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Worlds loaded before: " . $loadedLevelsBefore);
        }
        
        # Get appropriate exclude list (on-load, on-command, default = no list)
        switch ($excludelist) {
            case "on-load":
                $exclude = $this->configData["on-startup"]["exclude"];
                break;
            case "on-command":
                $exclude = $this->configData["on-command"]["exclude"];
                break;
            default:
                $exclude = "";
                break;
        }

        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Exclude mode: " . $excludelist);
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Excluded worlds: " . $exclude);
        }

        # Load the levels
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName) {
            # Only load level if not in exclude list, which can be empty
            $excludeArray = explode(",", $exclude);
            if (!in_array($levelName, $excludeArray)) {
                $this->getServer()->loadLevel($levelName);
            }
        }

        $loadedLevelsAfter = intval(count($this->getServer()->getLevels()));

        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Fishished loading worlds.");
            $this->getLogger()->info(TextFormat::DARK_GREEN . "Worlds loaded after: " . $loadedLevelsAfter);
        }

        if (($loadedLevelsAfter > $loadedLevelsBefore) && ($showInfo === true)) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "One or more worlds were loaded.");
        } else {
            $this->getLogger()->info(TextFormat::DARK_RED . "No extra worlds loaded!");
        }
    }

    public function onLoad() : void
    {
        $this->saveDefaultConfig();
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_BLUE . "LoadAllWorlds Loaded!");
        }
    }

    public function onEnable() : void
    {
        if ($this->debugMode === true) {
            $this->getLogger()->info(TextFormat::DARK_GREEN . "LoadAllWorlds Enabled!");
        }
        $this->reloadConfig();
        $this->configData = $this->getConfig()->getAll();
        $this->migrateConfig();
    }

    public function onDisable() : void
    {
        $this->getConfig()->save();
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

    private function migrateConfig(): void
    {
        if (array_key_exists("config-version", $this->configData)) {
            if ($this->configData["config-version"] === 2) {
                if ($this->configData["on-startup"]["load-worlds"] === true) {
                    $this->loadWorlds("on-load", false); # use on-load exclude list
                }
                $this->debugMode = $this->getConfig()->get("debug");
            }
        } else {
            # Remove old config file
            unlink($this->getConfig()->getPath());
            # Write new config file (default)
            $this->saveDefaultConfig();
            $this->reloadConfig();
            # Set old value in new config and set default empty exclude list
            $oldvalue = array("load-worlds" => $this->configData["load-on-startup"], "exclude" => "");
            $this->getConfig()->set("on-startup", $oldvalue);
            # Save config
            $this->getConfig()->save();
            # Get the new config data in local storage
            $this->configData = $this->getConfig()->getAll();
        }
    }
}
