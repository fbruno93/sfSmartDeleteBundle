<?php


namespace Bfy\SmartDeleteBundle\Entity;

use DateTimeInterface;

interface SmartDeleteInterface
{
    /**
     * Set primary key of doctrine entity
     *
     * @param mixed $id
     * @return mixed
     */
    public function setId($id);
    /**
     * Return the date of deletion
     *
     * @return DateTimeInterface
     */
    public function getDeletedAt(): DateTimeInterface;
    /**
     * Set the date of deletion
     *
     * @param DateTimeInterface $deleted_at
     * @return mixed
     */
    public function setDeletedAt(DateTimeInterface $deleted_at);
}
