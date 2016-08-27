<?php
namespace log;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/27
 * Time: 15:10
 */
class ApiInfoLog
{
    const LOG_PATH = 'api_log/api-access.log';
    protected $log;

    public function __construct($name='Controller'){
        $this->log = new Logger($name);
        $handler = new StreamHandler(ApiInfoLog::LOG_PATH, Logger::INFO);
        $handler->setFormatter(new JsonFormatter());
        $this->log->pushHandler($handler);
    }

    public function record($message){
        if(gettype($message)=='array'){
            $this->log->info(json_encode($message));
        } else {
            $this->log->info($message);
        }
    }

}