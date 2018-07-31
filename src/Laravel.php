<?php

namespace DigitSoft\LaravelPpm;

/**
 * Class Laravel
 * @package DigitSoft\LaravelPpm
 */
class Laravel extends \PHPPM\Bootstraps\Laravel
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function postHandle($app)
    {
        // Check if this is a lumen framework, if so, do not reset
        // Note that lumen does not have the getProvider method
        if (method_exists($app, 'getProvider')) {
            //reset debugbar if available
            $this->resetProvider('\Illuminate\Redis\RedisServiceProvider');
            $this->resetProvider('\Illuminate\Cookie\CookieServiceProvider');
            $this->resetProvider('\Barryvdh\Debugbar\ServiceProvider');
        }
    }
}