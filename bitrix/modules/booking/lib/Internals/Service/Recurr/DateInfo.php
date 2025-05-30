<?php

namespace Bitrix\Booking\Internals\Service\Recurr;

/**
 * Class DateInfo is responsible for holding information based on a particular
 * date that is applicable to a Rule.
 */
class DateInfo
{
    /** @var \DateTime */
    public $dt;

    /**
     * @var int Number of days in the month.
     */
    public $monthLength;

    /**
     * @var int Number of days in the year (365 normally, 366 on leap years)
     */
    public $yearLength;

    /**
     * @var int Number of days in the next year (365 normally, 366 on leap years)
     */
    public $nextYearLength;

    /**
     * @var array Day of year of last day of each month.
     */
    public $mRanges;

    /** @var int Day of week */
    public $dayOfWeek;

    /** @var int Day of week of the year's first day */
    public $dayOfWeekYearDay1;

    /**
     * @var array Month number for each day of the year.
     */
    public $mMask;

    /**
     * @var array Month-daynumber for each day of the year.
     */
    public $mDayMask;

    /**
     * @var array Month-daynumber for each day of the year (in reverse).
     */
    public $mDayMaskNeg;

    /**
     * @var array Day of week (0-6) for each day of the year, 0 being Monday
     */
    public $wDayMask;
}
