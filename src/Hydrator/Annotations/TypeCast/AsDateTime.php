<?php
/**
 * Hydration system annotation.
 * Used to annotate items should be type cast to date time objects.
 * Project homepage: https://github.com/bairwell/hydrator
 * (c) Richard Bairwell <richard@bairwell.com> of Bairwell Ltd http://bairwell.com
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types = 1);

namespace Bairwell\Hydrator\Annotations\TypeCast;

/**
 * Used to annotate items should be type cast to date time objects.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AsDateTime extends CastBase
{

    /**
     * Allow null
     *
     * @var boolean
     */
    public $allowNull = true;
    /**
     * Minimum allowed date time.
     *
     * @var \DateTime
     */
    public $min = null;
    /**
     * Maximum allowed date time.
     *
     * @var \DateTime
     */
    public $max = null;

    /**
     * AsDateTime constructor.
     */
    public function __construct()
    {
        $this->min = new \DateTime('1970-01-01 00:00:00');
        $this->max = new \DateTime('2999-12-31 23:59:59');
    }//end __construct()


    /**
     * Cast an value to a specific type.
     *
     * Public calls should be made to "cast" which then delegates to this method.
     *
     * @param mixed $value        Value to be casted and returned by reference.
     * @param mixed $defaultValue Value to be returned if not set.
     *
     * @return mixed New value (or default value if unmatched).
     * @throws \TypeError If defaultValue is of invalid type.
     * @throws \RuntimeException If min or max are invalid.
     */
    public function doCast($value, $defaultValue = null)
    {
        $format = 'Y-m-d H:i:s';
        if (false === ($defaultValue instanceof \DateTime) && null !== $defaultValue) {
            throw new \TypeError('DefaultValue must be a \DateTime for DateTime casts');
        }

        // check our constraints
        $this->min = $this->attemptCast($this->min);
        if (false === $this->min) {
            throw new \RuntimeException('AsDateTime min is invalid');
        }

        $this->max = $this->attemptCast($this->max);
        if (false === $this->max) {
            throw new \RuntimeException('AsDateTime max is invalid');
        }

        if (null !== $defaultValue) {
            if ($defaultValue > $this->max) {
                throw new \RuntimeException(
                    'DefaultValue exceed max requirements for DateTime which is '.
                    $this->max->format($format).' was passed: '.
                    $defaultValue->format($format)
                );
            }

            if ($defaultValue < $this->min) {
                throw new \RuntimeException(
                    'DefaultValue fails to reach min requirements for DateTime which is '.
                    $this->min->format($format).' was passed: '.
                    $defaultValue->format($format)
                );
            }
        }

        if (false === is_string($value) && false === is_numeric($value)) {
            $this->setError(self::ONLY_STRINGS_NUMERICS);

            return $defaultValue;
        }

        // attempt cast
        $date = $this->attemptCast($value);
        // we have no matching date, let's return now.
        if (false === $date) {
            $this->setError(self::DATETIME_MUST_BE_ACCEPTED_FORMAT);

            return $defaultValue;
        }

        if (false === ($date >= $this->min && $date <= $this->max)) {
            $this->setError(
                self::DATETIME_OUTSIDE_ACCEPTABLE_RANGE,
                ['%min%' => $this->min->format($format), '%max%' => $this->max->format($format)]
            );

            return $defaultValue;
        }

        return $date;
    }//end doCast()

    /**
     * Attempt to cast a string to a \DateTime.
     *
     * @param mixed $value Either an int, float, string or \DateTime to cast.
     *
     * @return \DateTime|false False if failed.
     * @throws \TypeError If passed a non-string, numeric or \DateTime.
     */
    protected function attemptCast($value)
    {
        // already a date time
        if (true === ($value instanceof \DateTime)) {
            return $value;
        } elseif (false === is_string($value) && false === is_numeric($value)) {
            throw new \TypeError('AttemptCast must be a \DateTime, string or a numeric');
        }

        $value = strval($value);
        // ISO8601/RFC3339/Atom/W3C format = 2005-08-15T15:52:01+00:00
        // RFC2822 format                  = Mon, 15 Aug 05 15:52:01 +0000
        // unixtime
        $formats         = [\DateTime::ATOM,\DateTime::RFC2822,'U'];
        $defaultTimeZone = new \DateTimeZone('UTC');
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value, $defaultTimeZone);
            if (false !== $date) {
                // check for overflow/invalid "fixes" and only return if no warnings or errors.
                $errors = \DateTime::getLastErrors();
                if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) {
                    return $date;
                }
            }
        }

        return false;
    }//end attemptCast()
}//end class
