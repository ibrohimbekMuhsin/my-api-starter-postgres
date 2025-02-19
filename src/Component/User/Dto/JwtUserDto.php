<?php

declare(strict_types=1);

namespace App\Component\User\Dto;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

readonly class JwtUserDto implements JWTUserInterface
{
    public function __construct(private int $id, private string $email, private array $roles)
    {
    }

    public static function createFromPayload($username, array $payload): JwtUserDto
    {
        return new self(
            $payload['id'],
            $username,
            $payload['roles']
        );
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->getId();
    }
}
