<?php
namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;


class CustomConsoleLogger
{
    public function __invoke(array $config = []): Logger
    {
        $logger = new Logger('custom-console-logger');

        $dateFormat = "Y-m-d H:i:s";
        $envName = env('APP_ENV');

        $customFormat = "%datetime% [$envName: %level_name%] %message%\n";
        $outputFormat = LineFormatter::SIMPLE_FORMAT;

        $handler = new StreamHandler('php://stdout');
        $handler->setFormatter(new LineFormatter($customFormat, $dateFormat, true, true));

        $logger->pushHandler($handler);

        return $logger;
    }
}