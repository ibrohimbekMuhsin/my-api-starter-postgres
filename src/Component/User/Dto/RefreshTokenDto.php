<?php

declare(strict_types=1);

namespace App\Component\User\Dto;

readonly class RefreshTokenDto
{
    public function __construct(private string $id, private string $username, private int $iat)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getIat(): int
    {
        return $this->iat;
    }
}
