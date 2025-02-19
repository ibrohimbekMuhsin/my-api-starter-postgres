<?php

declare(strict_types=1);

namespace App\Component\User\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

readonly class TokensDto
{
    public function __construct(
        #[Groups(['users:read'])]
        private string $accessToken,

        #[Groups(['users:read'])]
        private string $refreshToken
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
