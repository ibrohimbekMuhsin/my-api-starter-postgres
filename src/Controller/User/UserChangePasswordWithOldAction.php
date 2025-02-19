<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Component\User\Dto\UserChangePasswordWithOldDto;
use App\Component\User\UserManager;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserChangePasswordWithOldAction extends AbstractController
{
    /**
     * @throws Exception
     */
    public function __invoke(
        User $user,
        Request $request,
        UserManager $userManager,
        UserRepository $repository,
        UserPasswordHasherInterface $userPasswordHasher,
        string $id
    ): User {
        /** @var UserChangePasswordWithOldDto $userChangePasswordDto */
        $userChangePasswordDto = $this->getDtoFromRequest($request, UserChangePasswordWithOldDto::class);

        $this->validate($userChangePasswordDto);
        $oldPassword = $userChangePasswordDto->getOldPassword();

        if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new BadRequestException('Old password is incorrect!');
        }

        /**
         * @var User $user
         */
        $user = $this->findEntityOrError($repository, $id);
        $this->validate($user);

        $user->setPassword(
            $userPasswordHasher->hashPassword($user, $userChangePasswordDto->getNewPassword())
        );
        $userManager->save($user, true);

        return $user;
    }
}
