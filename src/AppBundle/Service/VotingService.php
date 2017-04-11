<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Interfaces\IVote;
use AppBundle\Interfaces\IVotable;
use AppBundle\Interfaces\IVoter;

class VotingService
{
    /** @var EntityManager $em */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function vote(IVoter $voter = null, IVotable $votable, int $voteType): bool
    {
        if (!$voter || !$votable || !$this->isVoteTypeCorrect($voteType)) {
            return false;
        }

        /** @var IVote $vote */
        $vote = $this->em->getRepository($votable->getVoteRepositoryName())->findOneBy([
            $votable->getVoteVotablePropertyName() => $votable,
            $votable->getVoteVoterPropertyName() => $voter
        ]);

        if (!$vote) {
            $vote = $this->createVote($votable, $voter);
        }

        $vote->setVote($voteType);
        return true;
    }

    protected function isVoteTypeCorrect(int $voteType)
    {
        return in_array($voteType, [IVote::THUMBS_DOWN, IVote::THUMBS_UP]);
    }

    protected function createVote(IVotable $votable, IVoter $voter): IVote
    {
        /** @var IVote $vote */
        $vote = $votable->createNewVoteEntity();
        $vote->setVotable($votable);
        $vote->setVoter($voter);

        $this->em->persist($vote);
        return $vote;
    }
}
