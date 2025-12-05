<?php

declare (strict_types=1);

namespace Validator;

class ValidateException extends \RuntimeException
{
    /**
     * @var string|array
     */
    protected string|array $error;
    protected mixed $data;

    public function __construct($error, $data = null, $code = 400)
    {
        $this->error = $error;
        $this->message = is_array($error) ? implode(PHP_EOL, $error) : $error;
        $this->data = $data;

        parent::__construct($this->message,$code);
    }

    public function getError(): array|string
    {
        return $this->error;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
