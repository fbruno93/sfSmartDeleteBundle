<?php

namespace Bfy\SmartDeleteBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class DeleteCommandTest extends KernelTestCase
{
    private $commandTester;

    /**
     * Launch before every test
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel([
            'environment' => 'test',
            'debug' => true,
        ]);

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $deleteCommand = $application->find('bfy:smart-delete');

        $this->commandTester = new CommandTester($deleteCommand);
        $this->commandTester->execute([]);
    }

    public function testDeletedCommandFirstLaunch(): void
    {
        self::assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();

        $exists = [];
        $generated = [];

        preg_match('/Exists: (\w+)/m', $output, $exists);
        preg_match('/Generated: (\w+)/m', $output, $generated);

        self::assertTrue(empty($exists));
        self::assertFalse(empty($generated[1]));
        self::assertCount(1, explode(',', $generated[1]));
    }

    /**
     * @depends testDeletedCommandFirstLaunch
     */
    public function testDeleteCommandSecondLaunch(): void
    {
        self::assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        $exists = [];
        $generated = [];

        preg_match('/Exists: (\w+)/m', $output, $exists);
        preg_match('/Generated: (\w+)/m', $output, $generated);

        self::assertTrue(empty($generated));
        self::assertFalse(empty($exists[1]));
        self::assertCount(1, explode(',', $exists[1]));
    }

    /**
     * Launch after all tests
     */
    public static function tearDownAfterClass(): void
    {
        // Need to reboot kernel because of inherit of tearDown stops the kernel
        self::bootKernel([
            'environment' => 'test',
            'debug' => true,
        ]);

        $backupPath = self::$kernel->getContainer()->getParameter('smart_delete.entity.main.backup');
        $modelPath = self::$kernel->getContainer()->getParameter('smart_delete.entity.main.dir');

        // Delete generated files
        array_map('unlink', glob(__DIR__ . "/../Entity/{Deleted,Model,Row}/*.php", GLOB_BRACE));

        // Restore original files
        $fs = new Filesystem();
        foreach (glob($backupPath . '/*.php.entity') as $file) {
            $fs->rename($file, $modelPath . '/' . basename($file, '.entity'), true);
        }

        // Remove backup directory
        rmdir($backupPath);
    }
}
