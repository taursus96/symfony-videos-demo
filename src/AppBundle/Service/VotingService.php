<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Interfaces\VoteInterface;
use AppBundle\Interfaces\VotableInterface;
use AppBundle\Interfaces\VoterInterface;

class VotingService
{
    /** @var EntityManager $em */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function vote(VoterInterface $voter = null, VotableInterface $votable, int $voteType): bool
    {
        if (!$voter || !$votable || !$this->isVoteTypeCorrect($voteType)) {
            return false;
        }

        /** @var VoteInterface $vote */
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
        return in_array($voteType, [VoteInterface::THUMBS_DOWN, VoteInterface::THUMBS_UP]);
    }

    protected function createVote(VotableInterface $votable, VoterInterface $voter): VoteInterface
    {
        /** @var VoteInterface $vote */
        $vote = $votable->createNewVoteEntity();
        $vote->setVotable($votable);
        $vote->setVoter($voter);

        $this->em->persist($vote);
        return $vote;
    }
}
