<?php
/**
 * This file is a part of the coredump package.
 *
 * Read more at https://github.com/themichaelhall/coredump
 */
declare(strict_types=1);

namespace MichaelHall\CoreDump;

/**
 * Class CoreDump.
 *
 * @since 1.0.0
 */
class CoreDump
{
    /**
     * Constructs a CoreDump object.
     *
     * @since 1.0.0
     *
     * @param \Throwable|null $throwable The throwable or null if no throwable.
     */
    public function __construct(?\Throwable $throwable = null)
    {
        $this->throwable = $throwable;
        $this->content = [];
        $this->superGlobals = [];

        $this->addSuperGlobal('$_SERVER', $_SERVER ?? null);
        $this->addSuperGlobal('$_GET', $_GET ?? null);
        $this->addSuperGlobal('$_POST', $_POST ?? null);
        $this->addSuperGlobal('$_FILES', $_FILES ?? null);
        $this->addSuperGlobal('$_COOKIE', $_COOKIE ?? null);
        $this->addSuperGlobal('$_SESSION', $_SESSION ?? null);
        $this->addSuperGlobal('$_REQUEST', $_REQUEST ?? null);
        $this->addSuperGlobal('$_ENV', $_ENV ?? null);
    }

    /**
     * Adds content to the core dump.
     *
     * @since 1.0.0
     *
     * @param string $name    The name.
     * @param mixed  $content The content.
     */
    public function add(string $name, $content): void
    {
        $this->content[$name] = $content;
    }

    /**
     * Saves content to a file.
     *
     * @since 1.0.0
     *
     * @param string $path The path to save file to.
     *
     * @return string The full path to the file.
     */
    public function save(string $path = ''): string
    {
        if ($path === '') {
            $path = getcwd() . DIRECTORY_SEPARATOR . '#.coredump';
        } elseif (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '#.coredump';
        }

        $path = str_replace('#', sha1(mt_rand() . microtime()), $path);

        file_put_contents($path, $this->__toString());

        return $path;
    }

    /**
     * Returns the core dump content as a string.
     *
     * @since 1.0.0
     *
     * @return string The core dump content as a string.
     */
    public function __toString(): string
    {
        $result = [];

        if ($this->throwable !== null) {
            $result[] = self::contentToString($this->throwable instanceof \Exception ? 'Exception' : 'Error', $this->throwable);
        }

        foreach ($this->content as $name => $content) {
            $result[] = self::contentToString(strval($name), $content);
        }

        foreach ($this->superGlobals as $name => $content) {
            $result[] = self::contentToString(strval($name), $content);
        }

        return implode(PHP_EOL, $result);
    }

    /**
     * Adds a super global array.
     *
     * @param string     $name        The name of the super global array.
     * @param array|null $globalArray The global array or null if not set.
     */
    private function addSuperGlobal(string $name, ?array $globalArray = null): void
    {
        if ($globalArray === null) {
            return;
        }

        $this->superGlobals[$name] = $globalArray;
    }

    /**
     * Converts content to dump to a readable string.
     *
     * @param string $name    The name of the content.
     * @param mixed  $content The content.
     *
     * @return string The content as a readable string.
     */
    private static function contentToString(string $name, $content): string
    {
        $result = str_repeat('=', 30) . PHP_EOL . ' ' . $name . PHP_EOL . str_repeat('=', 30) . PHP_EOL . PHP_EOL;

        if ($content instanceof \Throwable) {
            $result .= self::formatThrowable($content);
        } else {
            $result .= print_r($content, true);
        }

        return $result;
    }

    /**
     * Formats a throwable.
     *
     * @param \Throwable $throwable The throwable.
     *
     * @return string The result.
     */
    private static function formatThrowable(\Throwable $throwable): string
    {
        return
            'Class    : ' . get_class($throwable) . PHP_EOL .
            'Message  : ' . $throwable->getMessage() . PHP_EOL .
            'Code     : ' . $throwable->getCode() . PHP_EOL .
            'Location : ' . $throwable->getFile() . '(' . $throwable->getLine() . ')' . PHP_EOL .
            PHP_EOL .
            $throwable->getTraceAsString() . PHP_EOL;
    }

    /**
     * @var \Throwable|null My throwable.
     */
    private $throwable;

    /**
     * @var array My content.
     */
    private $content;

    /**
     * @var array My super globals.
     */
    private $superGlobals;
}
