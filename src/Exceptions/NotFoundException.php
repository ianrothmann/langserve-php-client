<?php

namespace IanRothmann\LangServePhpClient\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message = 'The requested resource was not found.';
}

