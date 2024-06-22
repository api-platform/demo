<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ReviewRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:review-analysis',
    description: 'Displays the day or month with the highest number of reviews published',
)]
final class ReviewAnalysisCommand extends Command
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                name: 'group-by-month',
                mode: InputOption::VALUE_NONE,
                description: 'Group reviews by month instead of day'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $groupByMonth = $input->getOption('group-by-month');

        $result = $groupByMonth
            ? $this->reviewRepository->findHighestReviewMonth()
            : $this->reviewRepository->findHighestReviewDay();

        if ($groupByMonth) {
            $io->success(sprintf('The month with the highest number of reviews is: %s', $result));
        } else {
            $io->success(sprintf('The day with the highest number of reviews is: %s', $result));
        }

        return Command::SUCCESS;
    }
}
