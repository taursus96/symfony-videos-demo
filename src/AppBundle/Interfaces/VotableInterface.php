<?php

namespace AppBundle\Interfaces;

interface VotableInterface
{
    public function getThumbsDown(): int;
    public function setThumbsDown(int $thumbsDown);

    public function getThumbsUp(): int;
    public function setThumbsUp(int $thumbsUp);

    public function getVoteRepositoryName(): string;
    public function getVoteVotablePropertyName(): string;
    public function getVoteVoterPropertyName(): string;

    public function createNewVoteEntity(): VoteInterface;
}
