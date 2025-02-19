<?php

namespace App\Command;

use App\Command\Core\CheckUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mas:roles:show-user-roles',
    description: 'Shows list of the user roles',
)]
class MasRolesShowUserRolesCommand extends Command
{
    public function __construct(
        private readonly CheckUser $checkUser,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $questionHelper = $this->getHelper('question');
        $userIdQuestion = new Question('User id: ');

        $user = null;

        $user = $this->checkUser->check($io, $input, $output, $userIdQuestion, $questionHelper, $user);

        foreach ($user->getRoles() as $role) {
            if ($role === 'ROLE_USER') {
                continue;
            }

            $io->text($role);
        }

        return Command::SUCCESS;
    }
}
