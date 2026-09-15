<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use RuntimeException;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Whether the local web server for Dusk has already been started.
     */
    protected static bool $webServerStarted = false;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
            static::startWebServer();
        }
    }

    /**
     * Boot an isolated Laravel dev server (using the "dusk" env) for Dusk.
     */
    protected static function startWebServer(): void
    {
        if (static::$webServerStarted) {
            return;
        }

        static::$webServerStarted = true;

        $port = env('DUSK_WEB_PORT', '8089');

        $projectRoot = dirname(__DIR__);

        $process = Process::fromShellCommandline(
            'php artisan serve --env=dusk --host=127.0.0.1 --port='.$port,
            $projectRoot
        );

        $process->start();

        register_shutdown_function(static function () use ($process) {
            $process->stop(5);
        });

        $deadline = microtime(true) + 20.0;

        while (microtime(true) < $deadline) {
            if (@file_get_contents('http://127.0.0.1:'.$port.'/up') !== false) {
                return;
            }

            usleep(200_000);
        }

        throw new RuntimeException(
            'Dusk web server failed to start on port '.$port.".\n".$process->getOutput()
        );
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
