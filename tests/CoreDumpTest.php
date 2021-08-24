<?php

declare(strict_types=1);

namespace MichaelHall\CoreDump\Tests;

use MichaelHall\CoreDump\CoreDump;
use PHPUnit\Framework\TestCase;

/**
 * Test CoreDump class.
 */
class CoreDumpTest extends TestCase
{
    /**
     * Test an empty core dump.
     */
    public function testEmpty()
    {
        $coreDump = new CoreDump();

        self::assertSame('', $coreDump->__toString());
    }

    /**
     * Test a core dump with globals set.
     */
    public function testWithGlobals()
    {
        $_SERVER['SERVER_TEST_VAR'] = 'Foo';
        $_GET['GET_TEST_VAR'] = 'Bar';
        $_POST['POST_TEST_VAR'] = 'Baz';
        $_FILES['FILES_TEST_VAR'] = 'FooBar';
        $_COOKIE['COOKIE_TEST_VAR'] = 'FooBaz';
        $_SESSION['SESSION_TEST_VAR'] = 'BarFoo';
        $_REQUEST['REQUEST_TEST_VAR'] = 'BarBaz';
        $_ENV['ENV_TEST_VAR'] = 'FooBarBaz';

        $coreDump = new CoreDump();

        self::assertStringContainsString('==============================' . PHP_EOL . ' $_SERVER' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[SERVER_TEST_VAR] => Foo', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_GET' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[GET_TEST_VAR] => Bar', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_POST' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[POST_TEST_VAR] => Baz', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_FILES' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[FILES_TEST_VAR] => FooBar', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_COOKIE' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[COOKIE_TEST_VAR] => FooBaz', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_SESSION' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[SESSION_TEST_VAR] => BarFoo', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_REQUEST' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[REQUEST_TEST_VAR] => BarBaz', $coreDump->__toString());
        self::assertStringContainsString('==============================' . PHP_EOL . ' $_ENV' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[ENV_TEST_VAR] => FooBarBaz', $coreDump->__toString());

        self::assertStringNotContainsString('Exception', $coreDump->__toString());
    }

    /**
     * Tests a core dump with an exception.
     */
    public function testWithException()
    {
        $_GET['GET_TEST_VAR'] = 'Foo';

        $exception = new \InvalidArgumentException('This is an exception', 42);
        $coreDump = new CoreDump($exception);

        self::assertStringContainsString('==============================' . PHP_EOL . ' Exception' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('Class    : InvalidArgumentException', $coreDump->__toString());
        self::assertStringContainsString('Message  : This is an exception', $coreDump->__toString());

        self::assertStringContainsString('==============================' . PHP_EOL . ' $_GET' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[GET_TEST_VAR] => Foo', $coreDump->__toString());

        self::assertStringNotContainsString('$_POST', $coreDump->__toString());
    }

    /**
     * Tests a core dump with an error.
     */
    public function testWithError()
    {
        $error = new \TypeError('This is an error', 0);
        $coreDump = new CoreDump($error);

        self::assertStringContainsString('==============================' . PHP_EOL . ' Error' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('Class    : TypeError', $coreDump->__toString());
        self::assertStringContainsString('Message  : This is an error', $coreDump->__toString());

        self::assertStringNotContainsString('$_GET', $coreDump->__toString());
    }

    /**
     * Test a core dump with an object.
     */
    public function testWithObject()
    {
        $_GET['GET_TEST_VAR'] = 'Foo';

        $exception = new \InvalidArgumentException('This is an exception', 42);
        $coreDump = new CoreDump($exception);
        $coreDump->add('My Array', ['Bar' => 'Baz']);

        self::assertStringContainsString('==============================' . PHP_EOL . ' Exception' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('Class    : InvalidArgumentException', $coreDump->__toString());
        self::assertStringContainsString('Message  : This is an exception', $coreDump->__toString());

        self::assertStringContainsString('==============================' . PHP_EOL . ' My Array' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[Bar] => Baz', $coreDump->__toString());

        self::assertStringContainsString('==============================' . PHP_EOL . ' $_GET' . PHP_EOL . '==============================', $coreDump->__toString());
        self::assertStringContainsString('[GET_TEST_VAR] => Foo', $coreDump->__toString());

        self::assertStringNotContainsString('$_POST', $coreDump->__toString());
    }

    /**
     * Test save method with default path.
     */
    public function testSaveWithDefaultPath()
    {
        $coreDump = new CoreDump();
        $coreDump->add('Test', ['Method' => 'testSaveWithDefaultValue']);
        $filePath = $coreDump->save();
        $fileContent = file_get_contents($filePath);
        unlink($filePath);

        self::assertMatchesRegularExpression('!^' . str_replace(DIRECTORY_SEPARATOR, '\\' . DIRECTORY_SEPARATOR, getcwd()) . '\\' . DIRECTORY_SEPARATOR . '[a-z0-9]{40}\.coredump$!', $filePath);
        self::assertStringContainsString('[Method] => testSaveWithDefaultValue', $fileContent);
    }

    /**
     * Test save method with directory path.
     */
    public function testSaveWithDirectoryPath()
    {
        $coreDump = new CoreDump();
        $coreDump->add('Test', ['Method' => 'testSaveWithDirectoryPath']);
        $filePath = $coreDump->save(sys_get_temp_dir());
        $fileContent = file_get_contents($filePath);
        unlink($filePath);

        self::assertMatchesRegularExpression('!^' . str_replace(DIRECTORY_SEPARATOR, '\\' . DIRECTORY_SEPARATOR, sys_get_temp_dir()) . '\\' . DIRECTORY_SEPARATOR . '[a-z0-9]{40}\.coredump$!', $filePath);
        self::assertStringContainsString('[Method] => testSaveWithDirectoryPath', $fileContent);
    }

    /**
     * Test save method with file path.
     */
    public function testSaveWithFilePath()
    {
        $coreDump = new CoreDump();
        $coreDump->add('Test', ['Method' => 'testSaveWithFilePath']);
        $filePath = $coreDump->save(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'core.dump');
        $fileContent = file_get_contents($filePath);
        unlink($filePath);

        self::assertSame(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'core.dump', $filePath);
        self::assertStringContainsString('[Method] => testSaveWithFilePath', $fileContent);
    }

    /**
     * Test save method with file path with replacement character.
     */
    public function testSaveWithFilePathWithReplacementCharacter()
    {
        $coreDump = new CoreDump();
        $coreDump->add('Test', ['Method' => 'testSaveWithFilePathWithReplacementCharacter']);
        $filePath = $coreDump->save(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'core.#.dump');
        $fileContent = file_get_contents($filePath);
        unlink($filePath);

        self::assertMatchesRegularExpression('!^' . str_replace(DIRECTORY_SEPARATOR, '\\' . DIRECTORY_SEPARATOR, sys_get_temp_dir()) . '\\' . DIRECTORY_SEPARATOR . 'core\.[a-z0-9]{40}\.dump$!', $filePath);
        self::assertStringContainsString('[Method] => testSaveWithFilePathWithReplacementCharacter', $fileContent);
    }

    /**
     * Set up.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->originalServerArray = $_SERVER;

        unset($_SERVER);
        unset($_GET);
        unset($_POST);
        unset($_FILES);
        unset($_COOKIE);
        unset($_SESSION);
        unset($_REQUEST);
        unset($_ENV);
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = $this->originalServerArray;
    }

    /**
     * @var array My original server array.
     */
    private $originalServerArray;
}
