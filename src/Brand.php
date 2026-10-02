<?php

namespace UniqueWorkbench\SharedUi;

/**
 * The Unique Workbench logo (resources/images), inlined as a data URI so apps don't need
 * to publish anything: logo-dark.png (white "unique") for the dark chrome, logo-light.png for light pages.
 */
class Brand
{
    private static array $logos = [];

    public static function logo(string $background = 'dark'): string
    {
        $file = $background === 'light' ? 'logo-light.png' : 'logo-dark.png';

        return self::$logos[$file] ??= 'data:image/png;base64,'
            . base64_encode(file_get_contents(__DIR__ . '/../resources/images/' . $file));
    }
}
