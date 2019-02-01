<?php

namespace DigitSoft\LaravelPpm;

/**
 * Class Laravel
 * @package DigitSoft\LaravelPpm
 */
class Laravel extends \PHPPM\Bootstraps\Laravel
{
    /**
     * Instantiate the bootstrap, storing the $appenv
     *
     * @param string|null $appenv The environment your application will use to bootstrap (if any)
     * @param boolean     $debug
     */
    public function initialize($appenv, $debug)
    {
        $this->appenv = $appenv;
        $this->debug = $debug;
        putenv("APP_ENV=" . $this->appenv);
    }

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
