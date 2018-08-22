<?php

namespace DataStructures\Commands;

use Jihel\Library\RBTree\Tree;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Performance extends AbstractCommand
{
    const MAX = 100 * 1000;

    protected function configure()
    {
        $this->setName('perf');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->start();
        //$this->test(\SplMaxHeap::class);
        //$this->test(\SplMinHeap::class);
        $this->test([]);
        $this->test(\SplDoublyLinkedList::class);
        //$this->test(\SplObjectStorage::class);
        $this->test(\SplFixedArray::class);
        $this->test(\ArrayObject::class);
        //$this->test(Tree::class);
        $this->end();
    }
}