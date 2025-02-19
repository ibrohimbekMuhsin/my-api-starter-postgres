<?php

declare(strict_types = 1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use App\Component\User\Dto\RefreshTokenRequestDto;
use App\Component\User\Dto\TokensDto;
use App\Component\User\Dto\UserChangePasswordWithOldDto;
use App\Controller\DeleteAction;
use App\Controller\User\UserAboutMeAction;
use App\Controller\User\UserAuthAction;
use App\Controller\User\UserAuthByRefreshTokenAction;
use App\Controller\User\UserChangePasswordWithOldAction;
use App\Controller\User\UserIsUniqueEmailAction;
use App\Entity\Interfaces\CreatedAtSettableInterface;
use App\Entity\Interfaces\DeletedAtSettableInterface;
use App\Entity\Interfaces\UpdatedAtSettableInterface;
use App\Repository\UserRepository;
use App\State\UserPasswordHasher;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['users:read']],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Post(processor: UserPasswordHasher::class),
        new Post(
            uriTemplate: 'users/auth',
            controller: UserAuthAction::class,
            openapi: new Operation(
                summary: 'Authorization'
            ),
            denormalizationContext: ['groups' => ['user:auth']],
            output: TokensDto::class,
            name: 'auth',
        ),
        new Post(
            uriTemplate: 'users/auth/refreshToken',
            controller: UserAuthByRefreshTokenAction::class,
            openapi: new Operation(
                summary: 'Authorization by refreshToken'
            ),
            input: RefreshTokenRequestDto::class,
            output: TokensDto::class,
            name: 'authByRefreshToken',
        ),
        new Get(
            security: "object == user || is_granted('ROLE_ADMIN')",
        ),
        new Patch(
            uriTemplate: 'users/{id}/password_admin',
            denormalizationContext: ['groups' => ['user:changePasswordAdmin:write']],
            security: "is_granted('ROLE_ADMIN')",
            processor: UserPasswordHasher::class,
        ),
        new Delete(
            controller: DeleteAction::class,
            security: "is_granted('ROLE_ADMIN') && object != user",
        ),
        new Post(
            uriTemplate: 'users/about_me',
            controller: UserAboutMeAction::class,
            denormalizationContext: ['groups' => ['user:empty:body']],
            name: 'aboutMe',
        ),
        new Post(
            uriTemplate: 'users/is_unique_email',
            controller: UserIsUniqueEmailAction::class,
            openapi: new Operation(
                summary: 'Checks email for uniqueness'
            ),
            denormalizationContext: ['groups' => ['user:isUniqueEmail:write']],
            name: 'isUniqueEmail',
        ),
        new Patch(
            uriTemplate: 'users/{id}/password_with_old',
            controller: UserChangePasswordWithOldAction::class,
            openapi: new Operation(
                summary: 'Changes password with old'
            ),
            denormalizationContext: ['groups' => ['user:changePasswordWithOld:write']],
            security: "object == user",
            input: UserChangePasswordWithOldDto::class,
            validate: false,
            name: 'changePasswordWithOld'
        ),
    ],
    normalizationContext: ['groups' => ['user:read', 'users:read']],
    denormalizationContext: ['groups' => ['user:write', 'user:update']],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'createdAt', 'updatedAt', 'email'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'email' => 'partial'])]
#[UniqueEntity('email', message: 'This {{ value }} email is already used')]
class User implements
    UserInterface,
    CreatedAtSettableInterface,
    UpdatedAtSettableInterface,
    DeletedAtSettableInterface,
    PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['user:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write', 'user:auth', 'user:isUniqueEmail:write'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:auth'])]
    private ?string $password = null;

    #[Assert\NotNull(groups: ['user:write', 'user:update', 'user:changePasswordAdmin:write'])]
    #[Assert\Length(min: 6, minMessage: 'Password must be at least {{ limit }} characters long')]
    #[Groups(['user:write', 'user:update', 'user:changePasswordAdmin:write'])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(type: 'datetime')]
    #[Groups(['user:read'])]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user:read'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['user:read'])]
    private ?DateTimeInterface $deletedAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function deleteRole(string $role): self
    {
        $roles = $this->roles;

        foreach ($roles as $roleKey => $roleName) {
            if ($roleName === $role) {
                unset($roles[$roleKey]);
                $this->setRoles($roles);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
