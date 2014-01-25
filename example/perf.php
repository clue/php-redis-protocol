<?php

use Clue\Redis\Protocol\ProtocolBuffer;
use Clue\Redis\Protocol\Factory;

require __DIR__ . '/../vendor/autoload.php';

$factory = new Factory();
$parser = $factory->createParser();
$serializer = $factory->createSerializer();

$n = isset($argv[1]) ? (int)$argv[1] : 10000; // number of dummy messages to parse
$cs = 4096; // pretend we can only read 7 bytes at once. more like 4096/8192 usually

echo 'benchmarking ' . $n . ' messages (chunksize of ' . $cs .' bytes)' . PHP_EOL;

$time = microtime(true);

$stream = '';
for ($i = 0; $i < $n; ++$i) {
    $stream .= $serializer->getRequestMessage(array('set', 'var' . $i, 'value' . $i));
}

echo round(microtime(true) - $time, 3) . 's for serialization' . PHP_EOL;
$time = microtime(true);

for ($i = 0, $l = strlen($stream); $i < $l; $i += $cs) {
    $parser->pushIncoming(substr($stream, $i, $cs));

    if ($parser->hasIncomingModel()) {
        $parser->popIncomingModel();
    }
}

echo round(microtime(true) - $time, 3) . 's for parsing' . PHP_EOL;
