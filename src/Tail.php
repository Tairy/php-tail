<?php

/**
 * Class Tail
 */
namespace Tail;

use Illuminate\Filesystem\Filesystem;
use JasonLewis\ResourceWatcher\Tracker;
use JasonLewis\ResourceWatcher\Watcher;

class Tail
{
    /**
     * @var string
     */
    const FIRST_READ_LINES = 5;
    /**
     * @var string
     */
    private $_filepath;

    /**
     * @var integer
     */
    private $_lastPos;

    /**
     * Tail constructor.
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->_filepath = $filepath;
        $this->_lastPos = filesize($this->_filepath);
        $this->_isFirst = false;
    }

    /**
     * @param int $lines
     * @param bool $adaptive
     * @return bool|string
     */
    public function readByLines($lines = 1, $adaptive = true)
    {
        $fp = @fopen($this->_filepath, 'rb');
        if ($fp === false) {
            return false;
        }

        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $buffer = $lines < 2 ? 64 : ($lines < 10 ? 512 : 4096);
        }

        fseek($fp, -1, SEEK_END);
        if (fread($fp, 1) != "\n") {
            $lines -= 1;
        }

        $output = '';
        while (ftell($fp) > 0 && $lines >= 0) {
            $seek = min(ftell($fp), $buffer);
            fseek($fp, -$seek, SEEK_CUR);
            $chunk = fread($fp, $seek);
            $output = $chunk . $output;
            fseek($fp, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            $lines -= substr_count($chunk, "\n");
        }

        while ($lines++ < 0) {
            $output = substr($output, strpos($output, "\n") + 1);
        }
        fclose($fp);
        return trim($output);
    }

    /**
     * @param bool $adaptive
     * @return bool|string
     */
    public function readByPos($adaptive = true)
    {
        $fp = @fopen($this->_filepath, 'rb');
        if ($fp === false) {
            return false;
        }

        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $newStringsCount = filesize($this->_filepath) - $this->_lastPos;
            $buffer = $newStringsCount < 200 ? 64 : ($newStringsCount < 1000 ? 512 : 4096);
        }
        $output = '';
        fseek($fp, $this->_lastPos);
        while (!feof($fp)) {
            $output .= fread($fp, $buffer);
        }

        $this->_lastPos = ftell($fp);
        $this->_isFirst = true;
        fclose($fp);
        return $output;
    }

    /**
     * @param $lines
     */
    public function tail($lines)
    {
        if(!isset($lines)) {
            $lines = self::FIRST_READ_LINES;
        }

        echo $this->readByLines($lines);
    }

    /**
     * tailf()
     */
    public function tailf()
    {
        echo $this->readByLines(self::FIRST_READ_LINES);
        $files = new Filesystem();
        $tracker = new Tracker();
        $watcher = new Watcher($tracker, $files);
        $listener = $watcher->watch($this->_filepath);
        $listener->modify(function () {
            echo $this->readByPos();
        });
        $watcher->start();
    }
}