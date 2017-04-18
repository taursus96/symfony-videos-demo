<?php

namespace AppBundle\Interfaces;

use AppBundle\Interfaces\IVote;
use AppBundle\Interfaces\IVoter;
use AppBundle\Interfaces\IVotable;

interface VoteInterface
{
    const THUMBS_UP = 1;
    const THUMBS_DOWN = 2;

    public function getVotable(): VotableInterface;
    public function getVote(): int;

    public function setVotable(VotableInterface $votable);
    public function setVoter(VoterInterface $voter);
    public function setVote(int $vote);
}
