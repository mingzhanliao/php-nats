<?php

namespace tests\stream;

use kaycn\PhpNats\config\ConnectOption;
use kaycn\PhpNats\stream\NatsTransport;
use PHPUnit\Framework\TestCase;

class NatsTransportTest extends TestCase
{
    public function testNew(): NatsTransport
    {
        $natsOption = new ConnectOption();
        $natsOption->setName('php-nats');
        $nats = new NatsTransport("192.168.31.78:4222",$natsOption,5);
        $this->assertNotEmpty($nats);
        $this->assertIsArray($nats->getServerInfo(),'need array');
        return $nats;
    }

    public function testReceive()
    {

    }
    /**
     * @depends getNew
     */
    public function testPing()
    {

    }
    public function testClose()
    {

    }
    /**
     * @depends testNew
     **/
    public function testPublish(NatsTransport $nats)
    {

        $this->assertTrue($nats->publish('test',111));
        return $nats;

    }
    /**
     * @depends testNew
     **/
    public function testSubscribe(NatsTransport $natsPush){
        $natsOption = new ConnectOption();
        $nats = new NatsTransport("192.168.31.78:4222",$natsOption,5);
        $res = $nats->subscribe('test',2555);
        $natsPush->publish('test','1111');
        sleep(2);
        $need = '';
        while ($msg = $nats->receive()){
            if(str_starts_with($msg, 'MSG test')){
                $need = trim($nats->receive());
            }
        }
        $this->assertTrue($need == '1111');
    }

}
