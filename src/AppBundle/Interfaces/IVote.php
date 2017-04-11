<?php

namespace AppBundle\Interfaces;

use AppBundle\Interfaces\IVote;
use AppBundle\Interfaces\IVoter;
use AppBundle\Interfaces\IVotable;

interface IVote
{
    const THUMBS_UP = 1;
    const THUMBS_DOWN = 2;

    public function getVotable(): IVotable;
    public function getVote(): int;

    public function setVotable(IVotable $votable);
    public function setVoter(IVoter $voter);
    public function setVote(int $vote);
}
