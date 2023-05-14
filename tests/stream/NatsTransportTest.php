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
        var_dump($nats->getServerInfo());
        $nats->connect();
        sleep(10);
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
    /**
     * @depends testNew
     */
    public function testConnect(NatsTransport $transport)
    {
        var_dump($transport->connect());

    }

    public function testClose()
    {

    }
    /**
     * @depends testNew
     **/
    public function testPublish(NatsTransport $nats)
    {
        $nats->connect();
        $nats->publish();

    }

    public function testWrite()
    {

    }
    /**
     * @depends testNew
     **/
    public function testSubscribe(NatsTransport $nats){
        $nats->connect();
        $res = $nats->subscribe('test',2555);
        $nats_pub = new NatsTransport();
        $nats_pub->connect();
        echo $nats_pub->publish('test',"55551");
//        sleep(2);
        var_dump($nats->receive(2));
        sleep(20);
//        var_dump($nats->receive());
    }

}
