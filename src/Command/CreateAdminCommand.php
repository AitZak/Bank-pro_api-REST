<?php
namespace App\Command;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('Create a new admin user with apiKey')
            ->addArgument('email', InputArgument::REQUIRED, 'new email')
            ->addArgument('apiKey', null, InputOption::VALUE_NONE, 'apiKey')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $apiKey = $input->getArgument('apiKey');
        $io->note(sprintf('Create a User for email: %s | apiKey: %s', $email, $apiKey));
        $user = new User();
        $user->setEmail($email);
        $user->setApiKey($apiKey);
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success(sprintf('You have created a new User with the email: %s | ApiKey: %s', $email, $apiKey));
    }
}