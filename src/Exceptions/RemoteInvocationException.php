<?php

namespace IanRothmann\LangServePhpClient\Exceptions;

use Exception;

class RemoteInvocationException extends Exception
{
    protected $message = 'Failed to invoke the remote runnable.';
}
