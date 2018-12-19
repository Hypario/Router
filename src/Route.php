<?php

namespace Hypario;

class Route
{

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Route constructor.
     * @param string $pattern
     * @param callable $callable
     * @param string|null $name
     */
    public function __construct(string $pattern, callable $callable, ?string $name = null)
    {
        $this->pattern = trim($pattern, '/');
        $this->callable = $callable;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return callable
     */
    public function getCallable(): callable
    {
        return $this->callable;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function match(string $url): bool
    {
        $url = trim($url, '/');
        $pattern = preg_replace_callback('#{.*?}#', [$this, 'paramMatch'], $this->pattern);
        $regex = "#^$pattern$#i";
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $i = 0;
        foreach($this->params as $key => $value) {
            $this->params[$key] = $matches[$i];
            $i++;
        }
        return true;
    }

    private function paramMatch($match): string
    {
        $param = str_replace('}', '', str_replace('{', '', $match[0]));
        $params = explode(':', $param);
        $name = $params[0];
        $this->params[$name] = null;
        $regex = $params[1];
        $regex = str_replace('(', '(?:', $regex);
        return '(' . $regex . ')';
    }

}
