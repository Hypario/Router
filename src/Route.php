<?php

namespace Hypario;

/**
 * Class Route.
 */
class Route
{
    /**
     * The pattern wanted
     * Example :
     * /Route/{slug:[a-zAZ]+}.
     *
     * @var string
     */
    private $pattern;

    /**
     * The var returned if the route matched.
     *
     * @var mixed
     */
    private $handler;

    /**
     * Name of the route.
     *
     * @var string
     */
    private $name;

    /**
     * All the params filled whenever the route matched.
     *
     * @var array
     */
    private $params = [];

    /**
     * Route constructor.
     *
     * @param mixed $handler
     */
    public function __construct(string $pattern, $handler, ?string $name = null)
    {
        $this->pattern = trim($pattern, '/');
        $this->handler = $handler;
        $this->name = $name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

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
        $pattern = preg_replace_callback(
            '/{([a-zA-Z]+):([A-Za-z0-9_\-\[\]{}|\\\+*?]+)}+?/',
            [$this, 'paramMatch'],
            $this->getPattern()
        );
        $regex = "#^$pattern$#";
        if (!preg_match($regex, $url, $matches)) {
            return false;
        }
        array_shift($matches);
        $i = 0;
        foreach ($this->params as $key => $value) {
            $this->params[$key] = $matches[$i];
            ++$i;
        }

        return true;
    }

    private function paramMatch($match): string
    {
        $this->params[$match[1]] = null;
        $regex = $match[2];
        $regex = str_replace('(', '(?:', $regex);

        return '(' . $regex . ')';
    }
}
