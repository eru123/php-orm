<?php

namespace eru123\orm\ORM;

trait Debug
{
    protected $debug = false;
    protected $debug_stack = [];

    public function debug(bool $debug = true)
    {
        $this->debug = $debug;
        return $this;
    }

    public function debug_stack(array $stack = [])
    {
        $this->debug_stack = $stack;
        return $this;
    }

    public function debug_info($msg, $color = 'white')
    {
        if ($this->debug) {
            $this->debug_stack[] = [
                'message' => $msg,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            ];
        }

        if (php_sapi_name() == 'cli' && !empty($msg)) {
            $colors = [
                'black' => '0;30',
                'dark_gray' => '1;30',
                'blue' => '0;34',
                'light_blue' => '1;34',
                'green' => '0;32',
                'light_green' => '1;32',
                'cyan' => '0;36',
                'light_cyan' => '1;36',
                'red' => '0;31',
                'light_red' => '1;31',
                'purple' => '0;35',
                'light_purple' => '1;35',
                'brown' => '0;33',
                'yellow' => '1;33',
                'light_gray' => '0;37',
                'white' => '1;37',
            ];

            if (is_array($msg)) {
                $msg = json_encode($msg, JSON_PRETTY_PRINT);
            }

            if (isset($colors[$color])) {
                $msg = "\033[" . $colors[$color] . "m" . $msg . "\033[0m";
            }

            echo $msg, PHP_EOL;
        }
    }
}