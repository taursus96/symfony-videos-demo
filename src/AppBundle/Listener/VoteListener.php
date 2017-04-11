<?php

namespace AppBundle\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

use Doctrine\ORM\UnitOfWork;

use AppBundle\Interfaces\IVote;
use AppBundle\Interfaces\IVotable;

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
            if ($entity instanceof IVote) {
                $this->onInsertVote($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof IVote) {
                $this->onUpdateVote($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof IVote) {
                $this->onDeleteVote($entity);
            }
        }
    }

    protected function onInsertVote(IVote $vote)
    {
        $this->applyVote($vote->getVotable(), $vote->getVote());
    }

    protected function onUpdateVote(IVote $vote)
    {
        $changes = $this->uow->getEntityChangeSet($vote);

        if (isset($changes['vote']) && $changes['vote'][0] !== $changes['vote'][1]) {
            $oldVote = $changes['vote'][0];
            $this->applyVote($vote->getVotable(), $oldVote, -1); //revert old vote
            $this->applyVote($vote->getVotable(), $vote->getVote());
        }
    }

    protected function onDeleteVote(IVote $vote)
    {
        $this->applyVote($vote->getVotable(), $vote->getVote(), -1);
    }

    protected function applyVote(IVotable $votable, int $vote, int $change = 1)
    {
        switch ($vote) {
            case IVote::THUMBS_DOWN:
                $votable->setThumbsDown($votable->getThumbsDown() + $change);
                break;
            case IVote::THUMBS_UP:
                $votable->setThumbsUp($votable->getThumbsUp() + $change);
                break;
        }
        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(get_class($votable)), $votable);
    }
}
