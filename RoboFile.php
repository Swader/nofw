<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    public function assetsWatch()
    {
        $this->taskWatch()
            ->monitor('assets', function () {
                $this->assetsBuild();
            })->run();
    }
    public function assetsBuild($opts = ['clear' => false])
    {
        $this->say(date('H:i:s').": starting rebuild");
        if ($opts['clear']) {
            $this->say(date('H:i:s').": Clearing old files!");
            $this->_exec('vendor/bin/mini_asset clear --config assets/assets.ini');
        }
        $this->_exec('vendor/bin/mini_asset build --config assets/assets.ini');
        $this->say(date('H:i:s').": rebuild done!");
    }
}