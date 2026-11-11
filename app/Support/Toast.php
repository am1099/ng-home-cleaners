<?php

namespace App\Support;

final class Toast
{
    /**
     * Flash a toast for the next full-page response (redirects, controllers).
     */
    public static function flash(string $type, string $title, ?string $message = null): void
    {
        session()->flash('toast', [
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public static function success(string $title, ?string $message = null): void
    {
        self::flash('success', $title, $message);
    }

    public static function error(string $title, ?string $message = null): void
    {
        self::flash('error', $title, $message);
    }

    public static function info(string $title, ?string $message = null): void
    {
        self::flash('info', $title, $message);
    }
}
