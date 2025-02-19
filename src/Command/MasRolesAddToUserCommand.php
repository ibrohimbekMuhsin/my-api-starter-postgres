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

#[AsCommand(
    name: 'mas:roles:add-to-user',
    description: 'Adds a role to the user',
)]
class MasRolesAddToUserCommand extends Command
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
        }

        $user->addRole($role);
        $this->userManager->save($user, true);

        return Command::SUCCESS;
    }
}
