<?php


namespace App\Controller\Is;


use App\Entity\Poll;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Invitation;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use App\Repository\PollGradeRepository;
use App\Repository\PollInvitationRepository;
use App\Repository\PollProposalBallotRepository;
use App\Repository\PollProposalRepository;
use App\Repository\PollRepository;


trait EntityAware {

    use EntityManagerAware;


    public function getPollRepository() : PollRepository
    {
        return $this->getEm()->getRepository(Poll::class);
    }

    public function getProposalRepository() : PollProposalRepository
    {
        return $this->getEm()->getRepository(Proposal::class);
    }

    public function getGradeRepository() : PollGradeRepository
    {
        return $this->getEm()->getRepository(Grade::class);
    }

    public function getBallotRepository() : PollProposalBallotRepository
    {
        return $this->getEm()->getRepository(Ballot::class);
    }

    public function getInvitationRepository() : PollInvitationRepository
    {
        return $this->getEm()->getRepository(Invitation::class);
    }
}