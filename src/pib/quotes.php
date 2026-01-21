<?php
namespace pib;

class Quotes
{
    private static $quotes = [
        "JS it's cool, but PHP is cooler",
        "PHP imageboard now with PHP inside",
        "Powered by questionable life choices (and php)",
        "No ads, no tracking, just PHP",
        "Caught in (the) web removed from the world",
        "You're plucked to the mass parched with thirst for Js frameworks",
        "Sun birds leave their dark recesses, shadows gild the archways",
        "That's just like, your opinion, man"
    ];

    public static function getRandom(): string
    {
        return self::$quotes[array_rand(self::$quotes)];
    }
}
