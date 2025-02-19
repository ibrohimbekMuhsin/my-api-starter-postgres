<?php

namespace App\Command;

use App\Command\Core\CheckUser;
use App\Component\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsCommand(
    name: 'mas:roles:delete-from-user',
    description: 'Deletes a role from the user',
)]
class MasRolesDeleteFromUserCommand extends Command
{
    public function __construct(
        private readonly CheckUser $checkUser,
        private readonly UserManager $userManager,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $questionHelper = $this->getHelper('question');
        $userIdQuestion = new Question('User id: ');
        $roleQuestion = new Question('Role: ');

        $user = null;
        $role = '';

        $user = $this->checkUser->check($io, $input, $output, $userIdQuestion, $questionHelper, $user);

        while (empty($role)) {
            $role = $questionHelper->ask($input, $output, $roleQuestion);

            if (!$this->hasRole($user, $role)) {
                $io->warning('The user have not a role: ' . $role);
                return 0;
            }
        }

        $user->deleteRole($role);
        $this->userManager->save($user, true);

        return Command::SUCCESS;
    }

    private function hasRole(UserInterface $user, string $roleName): bool
    {
        return in_array($roleName, $user->getRoles(), true);
    }
}
