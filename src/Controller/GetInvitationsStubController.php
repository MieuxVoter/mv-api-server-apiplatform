<?php

declare(strict_types=1);

namespace App\Controller;


/**
 * Used to disable a GET endpoint that ApiPlatform needs to generate IRIs.
 *
 * No-one should be able to get the index of invitations of all polls.
 *
 * See App\Entity\Poll\Invitation where this controller is declared.
 * See App\Security\Authorization\InvitationVoter for the access rules.
 *
 * Class GetInvitationsStubController
 * @package App\Controller
 */
final class GetInvitationsStubController
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [];
    }
}
