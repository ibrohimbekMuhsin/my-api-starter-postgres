<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Base\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserAboutMeAction extends AbstractController
{
    public function __invoke(#[CurrentUser] $user): UserInterface
    {
        return $user;
    }
}
