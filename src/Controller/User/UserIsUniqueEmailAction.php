<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckEmailController
 *
 * @package App\Controller
 */
class UserIsUniqueEmailAction extends AbstractController
{
    public function __invoke(User $data, UserRepository $userRepository): Response
    {
        return $this->responseNormalized([
            'isUnique' => empty($userRepository->findOneByEmail($data->getEmail()))
        ]);
    }
}
