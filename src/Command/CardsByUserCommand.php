<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CardsByUserCommand extends Command
{
    protected static $defaultName = 'app:cards-by-user';
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Give number of cards of a user specified by his email.')
            ->addArgument('email', InputArgument::OPTIONAL, 'user email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user) {
            $count = count($user->getCards());
            $io->success($user->getEmail().' has '.$count.' card(s) !');
        } else {
            $io->error('This user does not exist');
        }
    }
}
