<?php

namespace Illuminate\Log;

use Closure;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

class LogManager implements LoggerInterface
{
    protected $app;
    protected $channels = [];

    protected $levels = [
        'debug'     => Monolog::DEBUG,
        'info'      => Monolog::INFO,
        'notice'    => Monolog::NOTICE,
        'warning'   => Monolog::WARNING,
        'error'     => Monolog::ERROR,
        'critical'  => Monolog::CRITICAL,
        'alert'     => Monolog::ALERT,
        'emergency' => Monolog::EMERGENCY,
    ];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function channel(?string $channel = null): LoggerInterface
    {
        return $this->driver($channel);
    }

    public function driver(?string $driver = null): LoggerInterface
    {
        return $this->get($driver ?? $this->getDefaultDriver());
    }

    protected function get(string $name): LoggerInterface
    {
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }

        return $this->channels[$name] = $this->resolve($name);
    }

    protected function resolve(string $name): LoggerInterface
    {
        $config = $this->configurationFor($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Log [{$name}] is not defined.");
        }

        $driver = $config['driver'] ?? null;

        return match ($driver) {
            'single' => $this->createSingleDriver($config),
            'daily' => $this->createDailyDriver($config),
            'syslog' => $this->createSyslogDriver($config),
            'errorlog' => $this->createErrorlogDriver($config),
            'stack' => $this->createStackDriver($config),
            default => throw new InvalidArgumentException("Driver [{$driver}] is not supported."),
        };
    }

    protected function createSingleDriver(array $config): Writer
    {
        $monolog = new Monolog($this->parseChannel($config));
        $handler = new StreamHandler(
            $config['path'] ?? $this->app->storagePath() . '/logs/laravel.log',
            $this->level($config)
        );
        $handler->setFormatter($this->formatter());
        $monolog->pushHandler($handler);

        return new Writer($monolog);
    }

    protected function createDailyDriver(array $config): Writer
    {
        $monolog = new Monolog($this->parseChannel($config));
        $handler = new RotatingFileHandler(
            $config['path'] ?? $this->app->storagePath() . '/logs/laravel.log',
            $config['days'] ?? 7,
            $this->level($config)
        );
        $handler->setFormatter($this->formatter());
        $monolog->pushHandler($handler);

        return new Writer($monolog);
    }

    protected function createSyslogDriver(array $config): Writer
    {
        $monolog = new Monolog($this->parseChannel($config));
        $monolog->pushHandler(new SyslogHandler(
            $config['tag'] ?? config('app.name', 'laravel'),
            $config['facility'] ?? LOG_USER,
            $this->level($config)
        ));

        return new Writer($monolog);
    }

    protected function createErrorlogDriver(array $config): Writer
    {
        $monolog = new Monolog($this->parseChannel($config));
        $handler = new ErrorLogHandler(
            ErrorLogHandler::OPERATING_SYSTEM,
            $this->level($config)
        );
        $handler->setFormatter($this->formatter());
        $monolog->pushHandler($handler);

        return new Writer($monolog);
    }

    protected function createStackDriver(array $config): Writer
    {
        $handlers = [];
        foreach ($config['channels'] ?? [] as $channel) {
            $channelInstance = $this->channel($channel);
            if ($channelInstance instanceof Writer) {
                foreach ($channelInstance->getMonolog()->getHandlers() as $handler) {
                    $handlers[] = $handler;
                }
            }
        }

        $monolog = new Monolog($this->parseChannel($config));
        foreach ($handlers as $handler) {
            $monolog->pushHandler($handler);
        }

        return new Writer($monolog);
    }

    protected function configurationFor(string $name): ?array
    {
        return $this->app['config']["logging.channels.{$name}"] ?? null;
    }

    public function getDefaultDriver(): string
    {
        return $this->app['config']['logging.default'] ?? 'syslog';
    }

    protected function level(array $config): int
    {
        $level = $config['level'] ?? 'debug';

        return $this->levels[$level] ?? Monolog::DEBUG;
    }

    protected function parseChannel(array $config): string
    {
        return $config['name'] ?? $this->app['config']['app.name'] ?? 'production';
    }

    protected function formatter(): LineFormatter
    {
        return new LineFormatter(null, null, true, true);
    }

    public function emergency($message, array $context = []): void
    {
        $this->driver()->emergency($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->driver()->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->driver()->critical($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->driver()->error($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->driver()->warning($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->driver()->notice($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->driver()->info($message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->driver()->debug($message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->driver()->log($level, $message, $context);
    }
}
