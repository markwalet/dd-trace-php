<?php

namespace DDTrace\Tests\Integrations\Logs\MonologV1;

use DDTrace\Tests\Integrations\Logs\BaseLogsTest;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MonologV1Test extends BaseLogsTest
{
    protected function getLogger($jsonFormatter = false)
    {
        $logger = new Logger('test');
        $streamHandler = new StreamHandler('/tmp/test.log');

        if ($jsonFormatter) {
            $streamHandler->setFormatter(new JsonFormatter());
        }

        $logger->pushHandler($streamHandler);

        return $logger;
    }

    public function testDebugWithPlaceholders64bit()
    {
        $this->withPlaceholders(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message \[dd.trace_id="\d+" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="debug"\] \[\] \[\]/'
        );
    }

    public function testDebugInContext64bit()
    {
        $this->inContext(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message {"dd.trace_id":"\d+","dd.span_id":"\d+","dd.service":"my-service","dd.version":"4.2","dd.env":"my-env","status":"debug"} \[\]$/'
        );
    }

    public function testDebugAppended64bit()
    {
        $this->appended(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message \[dd.trace_id="\d+" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="debug"\] \[\] \[\]/'
        );
    }

    public function testDebugWithPlaceholders128bit()
    {
        $this->withPlaceholders(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message \[dd.trace_id="192f3581c8461c79abf2684ee31ce27d" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="debug"\] \[\] \[\]/',
            true
        );
    }

    public function testDebugInContext128bit()
    {
        $this->inContext(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message {"dd.trace_id":"192f3581c8461c79abf2684ee31ce27d","dd.span_id":"\d+","dd.service":"my-service","dd.version":"4.2","dd.env":"my-env","status":"debug"} \[\]$/',
            true
        );
    }

    public function testDebugAppended128bit()
    {
        $this->appended(
            'debug',
            $this->getLogger(),
            '/^\[.*\] test.DEBUG: A debug message \[dd.trace_id="192f3581c8461c79abf2684ee31ce27d" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="debug"\] \[\] \[\]/',
            true
        );
    }

    public function testLogWithPlaceholders64bit()
    {
        $this->withPlaceholders(
            'log',
            $this->getLogger(),
            '/^\[.*\] test.WARNING: A warning message \[dd.trace_id="\d+" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="warning"\] \[\] \[\]/',
            false,
            'warning'
        );
    }

    public function testLogInContext64bit()
    {
        $this->inContext(
            'log',
            $this->getLogger(),
            '/^\[.*\] test.ERROR: A error message {"dd.trace_id":"\d+","dd.span_id":"\d+","dd.service":"my-service","dd.version":"4.2","dd.env":"my-env","status":"error"} \[\]$/',
            false,
            'error'
        );
    }

    public function testLogAppended64bit()
    {
        $this->appended(
            'log',
            $this->getLogger(),
            '/^\[.*\] test.NOTICE: A notice message \[dd.trace_id="\d+" dd.span_id="\d+" dd.service="my-service" dd.version="4.2" dd.env="my-env" status="notice"\] \[\] \[\]/',
            false,
            'notice'
        );
    }

    public function testDebugJsonFormatting() {
        $this->usingJson(
            'debug',
            $this->getLogger(true),
            '/^{"message":"A debug message","context":{"dd.trace_id":"\d+","dd.span_id":"\d+","dd.service":"my-service","dd.version":"4.2","dd.env":"my-env","status":"debug"},"level":100,"status":"DEBUG","channel":"test","datetime":{"date":".*","timezone_type":\d,"timezone":".*"},"extra":\[\]}/'
        );
    }

    public function testLogJsonFormatting() {
        $this->usingJson(
            'log',
            $this->getLogger(true),
            '/^{"message":"A critical message","context":{"dd.trace_id":"\d+","dd.span_id":"\d+","dd.service":"my-service","dd.version":"4.2","dd.env":"my-env","status":"critical"},"level":500,"status":"CRITICAL","channel":"test","datetime":{"date":".*","timezone_type":\d,"timezone":".*"},"extra":\[\]}/',
            false,
            'critical'
        );
    }
}
