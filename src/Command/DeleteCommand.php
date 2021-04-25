<?php

namespace Bfy\SmartDeleteBundle\Command;

use Bfy\SmartDeleteBundle\Helper\Template;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use Exception;
use ReflectionClass;

/**
 * Class DeleteCommand
 */
class DeleteCommand extends Command
{
    /** @var string the name of command */
    protected static $defaultName = 'bfy:smart-delete';

    /** @var ClassMetadataFactory */
    private $deleteMetadataFactory;
    /** @var ClassMetadataFactory */
    private $defaultMetadataFactory;

    /** @var ContainerInterface */
    private $container;

    public function __construct(ManagerRegistry $managerRegistry, ContainerInterface $containerBag)
    {
        parent::__construct();
        $this->container = $containerBag;

        $this->defaultMetadataFactory = $managerRegistry->getManager($containerBag->getParameter('doctrine.default_entity_manager'))->getMetadataFactory();
        $this->deleteMetadataFactory = $managerRegistry->getManager($containerBag->getParameter('smart_delete.entity.manager'))->getMetadataFactory();

        $this->deleteMetadataFactory->getAllMetadata();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Generate deleted entities');
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deletedClassGenerated = [];
        $deletedExistClasses = [];

        $deletedNamespace = $this->container->getParameter('smart_delete.entity.deleted.prefix');
        $allMetadata = $this->defaultMetadataFactory->getAllMetadata();

        foreach ($allMetadata as $metadata) {
            $shortName =  $metadata->getReflectionClass()->getShortName();

            if ($this->deleteMetadataFactory->hasMetadataFor($deletedNamespace.'\\'.$shortName.'Deleted')) {
                $deletedExistClasses[] = $shortName;
                continue;
            }


            $deletedClassGenerated[] = $shortName;
            $this->processClass($metadata->getReflectionClass());
        }

        $output->write("Exists: ".implode(', ', $deletedExistClasses), true);
        $output->write("Generated: ".implode(', ', $deletedClassGenerated), true);

        return Command::SUCCESS;
    }

    private function processClass(ReflectionClass $class)
    {
        $shortName =  $class->getShortName();

        $rowClass = $this->toRowClass(
            $class->getFileName(),
            $shortName
        );

        $rowClass = join('', $rowClass);

        $deletedClass = Template::deletedClassFrom($shortName,
            $this->container->getParameter('smart_delete.entity.deleted.prefix'),
            $this->container->getParameter('smart_delete.entity.row.prefix'),
            $this->container->getParameter('smart_delete.entity.repository')
        );

        $childClass = Template::childClassFrom($shortName,
            $this->container->getParameter('smart_delete.entity.main.prefix'),
            $this->container->getParameter('smart_delete.entity.row.prefix'),
            $this->container->getParameter('smart_delete.entity.repository')
        );

        $fs = new Filesystem();

        $fs->mkdir($this->container->getParameter('smart_delete.entity.main.backup'));
        try {
            $fs->rename($class->getFileName(), $this->container->getParameter('smart_delete.entity.main.backup') . '/' . $shortName . '.php.entity');
        } catch (IOException $e) {
            // No action on existing file keep first original file
        }

        $fs->dumpFile($this->container->getParameter('smart_delete.entity.row.dir').'/'.$shortName.'Row.php', $rowClass);
        $fs->dumpFile($this->container->getParameter('smart_delete.entity.deleted.dir').'/'.$shortName.'Deleted.php', $deletedClass);
        $fs->dumpFile($this->container->getParameter('smart_delete.entity.main.dir').'/'.$shortName.'.php', $childClass);
    }

    private function toRowClass($filepath, $classBase)
    {
        return preg_replace([
            '/^namespace .*/',
            '/^use .*Repository;/',
            '/ORM\\\\Entity.*/',
            '/class .*/',
            '/private \$id;/'
        ], [
            "namespace {$this->container->getParameter( 'smart_delete.entity.row.prefix')};",
            '',
            'ORM\MappedSuperclass',
            "abstract class {$classBase}Row",
            'protected \$id;',

        ],
            file($filepath)
        );
    }

}