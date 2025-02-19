<?php

declare(strict_types=1);

namespace App\Component\User\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


readonly class UserChangePasswordWithOldDto
{
    public function __construct(
        #[Groups(['user:changePasswordWithOld:write'])]
        private string $oldPassword,

        #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters long')]
        #[Groups(['user:changePasswordWithOld:write'])]
        private string $newPassword
    ) {
    }

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}

