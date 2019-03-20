<?php

namespace DigitSoft\LaravelPpm;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HttpKernel (Not used at now)
 * @package DigitSoft\LaravelPpm
 */
class HttpKernel extends \PHPPM\Bridges\HttpKernel
{
    /**
     * @var array Previously used arguments to bootstrap application
     */
    protected $bootstrapArgs = [];

    /**
     * @inheritdoc
     */
    public function bootstrap($appBootstrap, $appenv, $debug)
    {
        $this->bootstrapArgs = [$appBootstrap, $appenv, $debug];
        parent::bootstrap($appBootstrap, $appenv, $debug);
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = parent::handle($request);
        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->application ? $this->application->getApplication() : null;
        // Handle errors during Laravel's first boot
        if (!$app || !$app->isBooted()) {
            error_log("\nRe-Bootstrap application because app was not booted properly");
            $this->reBootstrap();
        }
        return $response;
    }

    /**
     * Re-bootstrap
     * @return bool
     */
    protected function reBootstrap()
    {
        if (!empty($this->bootstrapArgs)) {
            $this->bootstrap(...$this->bootstrapArgs);
            return true;
        }
        return false;
    }
}
