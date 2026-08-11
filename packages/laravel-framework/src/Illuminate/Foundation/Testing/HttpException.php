<?php

namespace Illuminate\Foundation\Testing;

use PHPUnit\Framework\AssertionFailedError;

/**
 * PHPUnit 10: ExpectationFailedException is final, so extend AssertionFailedError.
 */
class HttpException extends AssertionFailedError
{
    //
}
