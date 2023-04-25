<?php
declare (strict_types = 1);

namespace Validator\lib;

class ValidateException extends \RuntimeException
{
    /**
     * @var string|array
     */
    protected string|array $error;

    public function __construct($error)
    {
        $this->error   = $error;
        $this->message = is_array($error) ? implode(PHP_EOL, $error) : $error;
        parent::__construct($this->message);
    }

    public function getError(): array|string
    {
        return $this->error;
    }
}
