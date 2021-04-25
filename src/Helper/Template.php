<?php

namespace Bfy\SmartDeleteBundle\Helper;

class Template
{
    public static function deletedClassFrom($className, $deletedNamespace, $rowNamespace, $repoNamespace)
    {
        return "<?php

namespace $deletedNamespace;

use $rowNamespace\\{$className}Row;
use $repoNamespace\\{$className}Repository;
use Bfy\SmartDeleteBundle\Entity\SmartDeleteInterface;
use Bfy\SmartDeleteBundle\Helper\DeletedTrait;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass={$className}Repository::class)
 * @ORM\Table(name=\"$className\")
 */
class {$className}Deleted extends {$className}Row implements SmartDeleteInterface
{
    use DeletedTrait;

    public function setId(\$id)
    {
        \$this->id = \$id;
    }

    /**
     * @var DateTimeInterface
     * @ORM\Column(type=\"datetime\")
     */
    private \$deleted_at;

    public function getDeletedAt(): DateTimeInterface
    {
        return \$this->deleted_at;
    }

    public function setDeletedAt(DateTimeInterface \$deleted_at): self
    {
        \$this->deleted_at = \$deleted_at;

        return \$this;
    }

}
";
    }

    public static function childClassFrom($className, $modelNamespace, $rowNamespace, $repoNamespace)
    {
        return "<?php

namespace $modelNamespace;

use $rowNamespace\\{$className}Row;
use $repoNamespace\\{$className}Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass={$className}Repository::class)
 */
class {$className} extends {$className}Row
{
}
";
    }
}