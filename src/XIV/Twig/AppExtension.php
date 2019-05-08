<?php

namespace XIV\Twig;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Delight\Cookie\Cookie;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use XIV\Service\Redis\Redis;
use XIV\Utils\Language;
use XIV\Utils\SiteVersion;
use XIV\Utils\Time;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('date', [$this, 'getDate']),
            new TwigFilter('dateSimple', [$this, 'getDateSimple']),
            new TwigFilter('bool', [$this, 'getBoolVisual']),
            new TwigFilter('max', [$this, 'getMaxValue']),
            new TwigFilter('min', [$this, 'getMinValue']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('siteVersion', [$this, 'getApiVersion']),
            new TwigFunction('favIcon', [$this, 'getFavIcon']),
            new TwigFunction('cache', [$this, 'getCached']),
            new TwigFunction('timezone', [$this, 'getTimezone']),
            new TwigFunction('timezones', [$this, 'getTimezones']),
            new TwigFunction('cookie', [$this, 'getCookie']),
        ];
    }

    /**
     * Get date in a nice format.
     */
    public function getDate($unix)
    {
        $unix       = is_numeric($unix) ? $unix : strtotime($unix);
        $difference = abs(time() - $unix);
        $carbon     = $unix > time()
            ? Carbon::now()->addSeconds($difference)->setTimezone(new CarbonTimeZone(Time::timezone()))
            : Carbon::now()->subSeconds($difference)->setTimezone(new CarbonTimeZone(Time::timezone()));

        if ($difference > (60 * 60 * 10)) {
            return $carbon->format('jS M, H:i:s');
        }

        return $carbon->diffForHumans();
    }

    /**
     * Get date in a nice format.
     */
    public function getDateSimple($unix)
    {
        $unix       = is_numeric($unix) ? $unix : strtotime($unix);
        $difference = time() - $unix;
        $carbon     = Carbon::now()->subSeconds($difference)->setTimezone(new CarbonTimeZone(Time::timezone()));

        if ($difference > (60 * 60 * 10)) {
            return $carbon->format('j M, H:i');
        }

        return $carbon->diffForHumans();
    }

    /**
     * Get Users Timezone
     */
    public function getTimezone()
    {
        return Time::timezone();
    }

    /**
     * Get supported timezones
     */
    public function getTimezones()
    {
        return Time::timezones();
    }

    public function getCookie($value, $defaultValue = null)
    {
        return Cookie::get($value) ?: $defaultValue;
    }

    /**
     * Renders a tick or cross for bool visuals
     */
    public function getBoolVisual($bool)
    {
        return $bool ? '✔' : '✘';
    }

    /**
     * Return max value in an array
     */
    public function getMaxValue($array)
    {
        return $array ? max($array) : 0;
    }

    /**
     * Return min value in an array
     */
    public function getMinValue($array)
    {
        return $array ? min($array) : 0;
    }

    /**
     * Get API version information
     */
    public function getApiVersion()
    {
        return SiteVersion::get();
    }

    /**
     * Get Fav icon based on if the site is in dev or prod mode
     */
    public function getFavIcon()
    {
        return getenv('APP_ENV') == 'dev' ? '/favicon_dev.png' : '/favicon.png';
    }

    /**
     * Get static cache
     */
    public function getCached($key)
    {
        $obj = Redis::Cache()->get($key);
        $obj = Language::handle($obj);
        return $obj;
    }
}
