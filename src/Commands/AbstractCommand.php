<?php

namespace DataStructures\Commands;

use DataStructures\Custom\Monolog\Formatter\CustomLineFormatter;
use Jihel\Library\RBTree\Node;
use Jihel\Library\RBTree\Tree;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    use LoggerAwareTrait;
    protected static $value;
    protected        $valueCounter = 0;

    protected function start()
    {
        $dateFormat = "i\m s\s";
        $format = "[%datetime% %extra.memory_usage%] %message%\n";
        $formatter = new CustomLineFormatter($format, $dateFormat);
        $streamHandler = new StreamHandler('php://stdout', Logger::DEBUG);
        $streamHandler->setFormatter($formatter);
        $this->setLogger(new Logger(
            __CLASS__,
            [$streamHandler],
            [
                new MemoryUsageProcessor()
            ]
        ));
        $this->logger->info(sprintf('Start with %d values', static::MAX));
        $this->logger->info('');
        static::$value = new \stdClass;
    }

    protected function end()
    {
        $this->logger->debug(sprintf('End. Values generated - %d', $this->valueCounter));
    }

    protected function test($className)
    {
        $object = $this->init($className);
        $this->fill($object);
        $this->update($object);
        $this->search($object);
        $this->sort($object);
        $this->extract($object);
        unset($object);
        gc_collect_cycles();
        $this->logger->info('');
    }

    protected function init($in)
    {
        if (is_array($in)) {
            $this->logger->debug('Array');
            $object = $in;
        } else {
            $this->logger->debug($in);
            $object = new $in;
            if ($object instanceof \SplFixedArray) {
                $object->setSize(static::MAX);
            }
        }
        $this->logger->debug('Init');
        return $object;
    }

    protected function fill($in)
    {
        if ($in instanceof \SplHeap) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->insert($i);
            }
        } else if ($in instanceof \SplDoublyLinkedList) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->push($i);
            }
        } else if ($in instanceof \SplFixedArray) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in[$i] = $this->getValue();
            }
        } else if ($in instanceof \SplObjectStorage) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->attach($this->getValue(), $i);
            }
        } else if ($in instanceof \ArrayObject) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->append($this->getValue());
            }
        } else if (is_array($in)) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in[$i] = $this->getValue();
            }
        } else if ($in instanceof Tree) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->insert(
                    new Node($i, $this->getValue())
                );
            }
        } else {
            return;
        }
        $this->logger->debug(sprintf('Fill'));
    }

    protected function update($in)
    {
        if ($in instanceof \SplHeap) {
            return;
        } elseif ($in instanceof \SplDoublyLinkedList) {
            $this->logger->debug(sprintf('Update skipped - 100 times slower than fill'));
        } elseif ($in instanceof \SplFixedArray) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in[$i] = $this->getValue();
            }
        } elseif ($in instanceof \ArrayObject) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in[$i] = $this->getValue();
            }
        } elseif (is_array($in)) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in[$i] = $this->getValue();
            }
        } else {
            return;
        }
        $this->logger->debug(sprintf('Update'));
    }

    protected function search($in)
    {
        if ($in instanceof \SplDoublyLinkedList) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->offsetExists($i);
            }
        } elseif ($in instanceof \SplFixedArray) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->offsetExists($i);
            }
        } elseif ($in instanceof Tree) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->find($i);
            }
        } elseif ($in instanceof \SplObjectStorage) {
            for ($i = 0; $i < static::MAX; $i++) {
                $in->offsetExists($this->getValue());
            }
            $this->logger->debug(sprintf('Search ::offsetExists'));
            return;
        } elseif ($in instanceof \ArrayObject) {
            $this->logger->debug(sprintf('Search ::seek skipped - too slow'));
            for ($i = 0; $i < static::MAX; $i++) {
                $in->offsetExists($i);
            }
            $this->logger->debug(sprintf('Search ::offsetExists'));
            return;
        } elseif (is_array($in)) {
            for ($i = 0; $i < static::MAX; $i++) {
                $test = in_array($i, $in);
            }
            $this->logger->debug(sprintf('Search by value (in_array)'));
            for ($i = 0; $i < static::MAX; $i++) {
                $test = array_key_exists($i, $in);
            }
            $this->logger->debug(sprintf('Search by key (array_key_exists)'));
            return;
        } else {
            return;
        }
        $this->logger->debug(sprintf('Search'));
    }


    protected function sort($in)
    {
        if ($in instanceof \SplHeap) {
            $in->top();
        }elseif(is_array($in)){
            krsort($in);
        }

        $this->logger->debug(sprintf('Sort'));
    }

    protected function extract($in)
    {
        if ($in instanceof \SplHeap) {
            while (!$in->isEmpty()) {
                $in->extract();
            }
        } elseif ($in instanceof \Iterator) {
            while ($in->valid()) {
                $in->current();
                $in->next();
            }
        } elseif ($in instanceof \ArrayObject) {
            $iterator = $in->getIterator();
            for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
                $iterator->current();
            }
        } elseif (is_array($in)) {
            foreach ($in as $k => $v) {
                $v;
            }
        } else {
            return;
        }
        $this->logger->debug(sprintf('Extract'));
    }

    protected function getValue()
    {
        $this->valueCounter++;
        $value = clone static::$value;
        $value->valueCounter = $this->valueCounter;
        return $value;
    }
}