<?php


namespace Standard\Controllers\Cron;

use Jobby\BackgroundJob;

/**
 * Class BetterBackgroundJob
 * @package Standard\Controllers\Cron
 *
 * Added because the default BackgroundJob is insufficient
 * @see https://github.com/jobbyphp/jobby/pull/53
 */
class BetterBackgroundJob extends BackgroundJob
{
    public function runFunction()
    {
        $command = $this->getSerializer()->unserialize(
            $this->config['closure']
        );

        ob_start();
        try {
            $retval = $command();
        } catch (\Throwable $e) {
            echo "Error! " . $e->getMessage() . "\n";
        }
        $content = ob_get_contents();
        if ($logfile = $this->getLogfile()) {
            file_put_contents($this->getLogfile(), $content, FILE_APPEND);
        }
        ob_end_clean();

        if ($retval !== true) {
            throw new \Exception(
                "Closure did not return true! Returned:\n" . print_r(
                    $retval, true
                )
            );
        }
    }
}