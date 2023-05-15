# php-nats
nats client
## Publish message
```php
<?php
$natsOption = new ConnectOption();
$natsOption->setName('php-nats');
$nats = new NatsTransport("192.168.31.78:4222",$natsOption,5);
$nats->publish('test',111)
```
## Subscribe message
```php
$nats = new NatsTransport("192.168.31.78:4222",$natsOption,5);
$res = $nats->subscribe('test',2555);
while ($msg = $nats->receive()){
    if(str_starts_with($msg, 'MSG test')){
       echo trim($nats->receive());
    }
}
```
