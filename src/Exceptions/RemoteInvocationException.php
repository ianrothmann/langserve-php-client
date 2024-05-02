<?php

namespace IanRothmann\LangSmithPhpClient\Exceptions;

use Exception;

class RemoteInvocationException extends Exception
{
    protected $message = 'Failed to invoke the remote runnable.';
}
