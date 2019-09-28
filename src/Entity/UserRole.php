<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\UserRole as BaseUserRole;

/**
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="roles")
 * })
 *
 * @final
 */
class UserRole extends BaseUserRole
{
}
