<?php

namespace IanRothmann\LangSmithPhpClient\Exceptions;

use Exception;

class InternalServerErrorException extends Exception
{
    protected $message = 'The server encountered an internal error.';
}
