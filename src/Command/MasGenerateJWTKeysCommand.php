<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Interfaces\GetOutputInterface;
use App\Command\Traits\RunCommandTrait;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mas:generate:jwtKeys',
    description: 'Generate jwt keys. If keys exist, they will be dropped.',
)]
class MasGenerateJWTKeysCommand extends Command implements GetOutputInterface
{
    use RunCommandTrait;

    private OutputInterface $output;
    private SymfonyStyle $symfonyIO;

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function getSymfonyStyleOutput(): SymfonyStyle
    {
        return $this->symfonyIO;
    }

    /**
     * @throws RandomException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->symfonyIO = new SymfonyStyle($input, $output);
        $this->output = $output;

        $this->setJwtPassphrase();
        $this->createJwtFolder();
        $this->createPassphrase();
        $this->allowAccessToPrivateKey();

        return Command::SUCCESS;
    }

    /**
     * @throws RandomException
     */
    private function setJwtPassphrase(): void
    {
        $envFile = $this->getDockerEnvFile();
        if (!file_exists($envFile)) {
            $this->symfonyIO->error("Environment file not found: $envFile");
            return;
        }

        $envContent = file_get_contents($envFile);

        if (preg_match('/^JWT_PASSPHRASE=(.*)$/m', $envContent, $matches)) {
            if (!empty(trim($matches[1]))) {
                $this->symfonyIO->success("JWT_PASSPHRASE уже установлен в $envFile");
                return;
            }

            $jwtPassphrase = bin2hex(random_bytes(16));
            $envContent = preg_replace(
                '/^JWT_PASSPHRASE=.*$/m',
                "JWT_PASSPHRASE=$jwtPassphrase",
                $envContent
            );

            file_put_contents($envFile, $envContent);
            $this->symfonyIO->success("JWT_PASSPHRASE обновлён в $envFile");
            return;
        }

        $jwtPassphrase = bin2hex(random_bytes(16));
        file_put_contents($envFile, "\nJWT_PASSPHRASE=$jwtPassphrase", FILE_APPEND | LOCK_EX);
        $this->symfonyIO->success("JWT_PASSPHRASE добавлен в $envFile");
    }


    private function getDockerEnvFile(): string
    {
        $envFiles = [
            '.env.local',
            '.env',
            ".env." . ($_ENV['APP_ENV'] ?? 'dev'),
            ".env." . ($_ENV['APP_ENV'] ?? 'dev') . ".local"
        ];

        foreach ($envFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return '.env';
    }

    private function createJwtFolder(): void
    {
        $this->runSystemCommandAndNotify(
            'mkdir -p config/jwt',
            'Created config/jwt folder',
            'Could not create folder config/jwt'
        );
    }

    private function createPassphrase(): void
    {
        $this->runSystemCommandAndNotify(
            '
            jwt_passphrase=$(grep "^JWT_PASSPHRASE=" ' . $this->getDockerEnvFile() . ' | cut -f 2 -d "=")
            echo "$jwt_passphrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
            echo "$jwt_passphrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
            ',
            'JWT keys are created',
            'Failed on creating JWT keys'
        );
    }

    private function allowAccessToPrivateKey(): void
    {
        $this->runSystemCommandAndNotify('chmod 0644 config/jwt/private.pem');
    }
}
