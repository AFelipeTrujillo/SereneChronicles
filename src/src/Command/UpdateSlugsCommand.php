<?php

namespace App\Command;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:update-slugs',
    description: 'Update slugs for existing posts',
)]
class UpdateSlugsCommand extends Command
{
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;
    private SluggerInterface $slugger;


    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        parent::__construct();

        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
        $this->slugger = $slugger;
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $posts = $this->postRepository->findAll();

        foreach ($posts as $post) {
            if (empty($post->getSlug())) {
                $slug = $this->slugger->slug($post->getTitle())->lower();
                $post->setSlug($slug);
            }
        }

        $this->entityManager->flush();

        $io->success('Slugs updated successfully.');

        return Command::SUCCESS;
    }
}
