<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stub implementation for the NumberFormatter class of the intl extension
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see Symfony\Component\Locale\Stub\StubNumberFormatter
 */

use Symfony\Component\Locale\Stub\StubNumberFormatter;

class NumberFormatter extends StubNumberFormatter
{
  public function getSymbol($attr)
  {
    switch($attr) {
      case NumberFormatter::CURRENCY_SYMBOL:
        return '€';
      case NumberFormatter::DECIMAL_SEPARATOR_SYMBOL:
        return '.';
      case NumberFormatter::DIGIT_SYMBOL:
        return '#';
      case NumberFormatter::EXPONENTIAL_SYMBOL:
        return 'E';
      case NumberFormatter::GROUPING_SEPARATOR_SYMBOL:
        return ',';
        ;
    }
  }
}
