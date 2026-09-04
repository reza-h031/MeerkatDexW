<?php
namespace App\Utils;

class DataFormatter{
    public static function formatDurationShort(int $seconds): string
    {
        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%d', $minutes, $secs);
    }
}
