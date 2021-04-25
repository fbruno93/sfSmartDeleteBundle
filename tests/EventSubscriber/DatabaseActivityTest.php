<?php

namespace Bfy\SmartDeleteBundle\Tests\EventSubscriber;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALException;
use Doctrine\DBAL\Exception;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class DatabaseActivityTest extends KernelTestCase
{

    protected function setUp(): void
    {
        $kernel = self::bootKernel([
            'environment' => 'test',
            'debug' => true,
        ]);

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $deleteCommand = $application->find('bfy:smart-delete');

        $commandTester = new CommandTester($deleteCommand);
        $commandTester->execute([]);

        self::assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testPreRemove(): void
    {
        $modelNamespace = self::$kernel->getContainer()->getParameter('smart_delete.entity.main.prefix');
        $modelClass = $modelNamespace.'\\Item';

        $defaultEntityManagerName = self::$kernel->getContainer()->getParameter('doctrine.default_entity_manager');
        $deleteEntityManagerName = self::$kernel->getContainer()->getParameter('smart_delete.entity.manager');

        $doctrine = self::$kernel->getContainer()->get('doctrine');

        $defaultEntityManager = $doctrine->getManager($defaultEntityManagerName);
        $defaultRepository = $defaultEntityManager->getRepository($modelClass);

        $deleteEntityManager = $doctrine->getManager($deleteEntityManagerName);

        self::assertCount(0, $defaultRepository->findAll());
        self::assertEquals(0, $this->countDeleted($deleteEntityManager->getConnection()));

        $item = new $modelClass();
        $defaultEntityManager->persist($item);
        $defaultEntityManager->flush();

        self::assertCount(1, $defaultRepository->findAll());
        self::assertEquals(0, $this->countDeleted($deleteEntityManager->getConnection()));

        $defaultEntityManager->remove($item);
        $defaultEntityManager->flush();

        self::assertCount(0, $defaultRepository->findAll());
        self::assertEquals(1, $this->countDeleted($deleteEntityManager->getConnection()));

        $deleteEntityManager->getConnection()->executeQuery('DELETE FROM Item')->execute();
    }

    /**
     * @param Connection $connection
     * @return int
     * @throws Exception|DBALException
     */
    private function countDeleted(Connection $connection)
    {
        return intval($connection->executeQuery('SELECT COUNT(*) FROM Item')->fetchOne());
    }

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