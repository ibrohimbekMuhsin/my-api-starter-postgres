<?php

declare(strict_types=1);

namespace App\Command\Core;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

readonly class CheckUser
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function check(
        SymfonyStyle $io,
        InputInterface $input,
        OutputInterface $output,
        Question $userIdQuestion,
        HelperInterface $questionHelper,
        ?User $user
    ): ?User {
        while ($user === null) {
            $userId = $questionHelper->ask($input, $output, $userIdQuestion);

            if (!Uuid::isValid($userId) || strlen($userId) !== 36) {
                $io->error("Invalid UUID format: $userId");
                continue;
            }

            $user = $this->userRepository->find($userId);

            if ($user === null) {
                $io->warning('User is not found by id: #' . $userId);
            }

            if (!!$user->getDeletedAt()) {
                $io->error('User is deleted by id: #' . $userId);
                exit(1);
            }
        }

        return $user;
    }
}
