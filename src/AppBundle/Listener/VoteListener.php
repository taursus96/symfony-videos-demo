<?php

namespace AppBundle\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

use Doctrine\ORM\UnitOfWork;

use AppBundle\Interfaces\VoteInterface;
use AppBundle\Interfaces\VotableInterface;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class VoteListener
{
    protected $em;
    protected $uow;

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof VoteInterface) {
                $this->onInsertVote($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof VoteInterface) {
                $this->onUpdateVote($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof VoteInterface) {
                $this->onDeleteVote($entity);
            }
        }
    }

    protected function onInsertVote(VoteInterface $vote)
    {
        $this->applyVote($vote->getVotable(), $vote->getVote());
    }

    protected function onUpdateVote(VoteInterface $vote)
    {
        $changes = $this->uow->getEntityChangeSet($vote);

        if (isset($changes['vote']) && $changes['vote'][0] !== $changes['vote'][1]) {
            $oldVote = $changes['vote'][0];
            $this->applyVote($vote->getVotable(), $oldVote, -1); //revert old vote
            $this->applyVote($vote->getVotable(), $vote->getVote());
        }
    }

    protected function onDeleteVote(VoteInterface $vote)
    {
        $this->applyVote($vote->getVotable(), $vote->getVote(), -1);
    }

    protected function applyVote(VotableInterface $votable, int $vote, int $change = 1)
    {
        switch ($vote) {
            case VoteInterface::THUMBS_DOWN:
                $votable->setThumbsDown($votable->getThumbsDown() + $change);
                break;
            case VoteInterface::THUMBS_UP:
                $votable->setThumbsUp($votable->getThumbsUp() + $change);
                break;
        }
        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(get_class($votable)), $votable);
    }
}
