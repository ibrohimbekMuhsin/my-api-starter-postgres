<?php

declare(strict_types=1);

namespace App\Component\Core;

use App\Component\User\CurrentUser;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\CreatedBySettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Entity\Interfaces\UpdatedBySettableInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractManager
{
    public function __construct(
        private readonly CurrentUser $currentUser,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(object $entity, bool $needToFlush = false): void
    {
        $this->updateCreatedOrUpdatedDates($entity);
        $this->updateCreatedOrUpdatedUsers($entity);
        $this->getEntityManager()->persist($entity);

        if ($needToFlush) {
            $this->entityManager->flush();
        }
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    private function updateCreatedOrUpdatedDates(object $entity): void
    {
        if ($entity->getId() === null && $entity instanceof CreatedAtSettableInterface) {
            $entity->setCreatedAt(new DateTime());
        } elseif ($entity->getId() !== null && $entity instanceof UpdatedAtSettableInterface) {
            $entity->setUpdatedAt(new DateTime());
        }
    }

    private function updateCreatedOrUpdatedUsers(object $entity): void
    {
        if ($entity->getId() === null && $entity instanceof CreatedBySettableInterface) {
            $entity->setCreatedBy($this->currentUser->getUser());
        } elseif ($entity->getId() !== null && $entity instanceof UpdatedBySettableInterface) {
            $entity->setUpdatedBy($this->currentUser->getUser());
        }
    }
}
