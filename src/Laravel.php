<?php

namespace DigitSoft\LaravelPpm;

use Illuminate\Auth\AuthManager;
use Illuminate\Session\SessionManager;

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

    /**
     * Create a Laravel application
     */
    public function getApplication()
    {
        if (file_exists('bootstrap/autoload.php')) {
            require_once 'bootstrap/autoload.php';
        } elseif (file_exists('vendor/autoload.php')) {
            require_once 'vendor/autoload.php';
        }

        // Laravel 5 / Lumen
        $isLaravel = true;
        if (file_exists('bootstrap/app.php')) {
            $this->app = require 'bootstrap/app.php';
            if (substr($this->app->version(), 0, 5) === 'Lumen') {
                $isLaravel = false;
            }
        }

        // Laravel 4
        if (file_exists('bootstrap/start.php')) {
            $this->app = require 'bootstrap/start.php';
            $this->app->boot();

            return $this->app;
        }

        if (!$this->app) {
            throw new \RuntimeException('Laravel bootstrap file not found');
        }

        $kernel = $this->app->make($isLaravel ? 'Illuminate\Contracts\Http\Kernel' : 'Laravel\Lumen\Application');

        $this->app->afterResolving('auth', function ($auth) {
            /** @var AuthManager $auth */
            $auth->extend('session', function ($app, $name, $config) {
                $provider = $app['auth']->createUserProvider($config['provider']);
                $guard = new \PHPPM\Laravel\SessionGuard($name, $provider, $app['session.store'], null, $app);
                if (method_exists($guard, 'setCookieJar')) {
                    $guard->setCookieJar($this->app['cookie']);
                }
                if (method_exists($guard, 'setDispatcher')) {
                    $guard->setDispatcher($this->app['events']);
                }
                if (method_exists($guard, 'setRequest')) {
                    $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
                }

                return $guard;
            });
        });

        $app = $this->app;
        $this->app->extend('session.store', function () use ($app) {
            /** @var SessionManager $manager */
            $manager = $app['session'];
            return $manager->driver();
        });

        return $kernel;
    }
}
