<?php

namespace Bfy\SmartDeleteBundle\Tests\Helper;

use Bfy\SmartDeleteBundle\Helper\Template;

use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testDeleteTemplate(): void
    {
        $this->assertEquals('<?php

namespace App\Entity\Deleted;

use App\Entity\Row\ItemRow;
use App\Repository\ItemRepository;
use Bfy\SmartDeleteBundle\Entity\SmartDeleteInterface;
use Bfy\SmartDeleteBundle\Helper\DeletedTrait;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 * @ORM\Table(name="Item")
 */
class ItemDeleted extends ItemRow implements SmartDeleteInterface
{
    use DeletedTrait;

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $deleted_at;

    public function getDeletedAt(): DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(DateTimeInterface $deleted_at): self
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

}
',
            Template::deletedClassFrom('Item', 'App\Entity\Deleted', 'App\Entity\Row', 'App\Repository'));
    }

    public function testChildTemplate(): void
    {
        $this->assertEquals('<?php

namespace App\Entity\Model;

use App\Entity\Row\\ItemRow;
use App\Repository\\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item extends ItemRow
{
}
',
            Template::childClassFrom('Item', 'App\Entity\Model', 'App\Entity\Row', 'App\Repository'));
    }
}
