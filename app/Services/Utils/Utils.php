<?php

namespace App\Services\Utils;

class Utils
{
    public static function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return 'کمتر از 1 دقیقه';
        }

        if ($seconds < 3600) {
            $minutes = (int) ceil($seconds / 60);

            return $minutes.' دقیقه';
        }

        $hours = (int) ceil($seconds / 3600);
        $minutes = (int) ceil(($seconds % 3600) / 60);

        return "{$hours} ساعت و {$minutes} دقیقه";
    }
}
