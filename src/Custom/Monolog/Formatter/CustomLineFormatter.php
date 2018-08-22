<?php

namespace DataStructures\Custom\Monolog\Formatter;

use Monolog\DateTimeImmutable;
use Monolog\Formatter\LineFormatter;

class CustomLineFormatter extends LineFormatter
{
    private $last;

    protected function formatDate(\DateTimeInterface $date)
    {
        if ($this->dateFormat === self::SIMPLE_DATE && $date instanceof DateTimeImmutable) {
            return (string)$date;
        }

        if (!$this->last) {
            $this->last = $this->getTime($date);
        }
        $diff = round(($this->getTime($date) - $this->last) * 1000, 2);
        $this->last = $this->getTime($date);

        $diff = str_pad($diff, 10, ' ');
        return $diff . 'ms';
    }

    private function getTime(DateTimeImmutable $date)
    {
        return
            $date->format('h') * 60 * 60 +
            $date->format('i') * 60 +
            $date->format('s') +
            $date->format('u') / (1000 * 1000);
    }
}
