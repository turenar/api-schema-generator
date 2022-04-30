<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Turenar\ApiSchema\SpecProcessor;

class GenerateCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('gen')
			->setDescription('generate api schema files')// TODO
			->setHelp('')// TODO
			->addArgument('src', InputArgument::OPTIONAL, 'source directory', getcwd() . '/api-spec')
			->addArgument('dst', InputArgument::OPTIONAL, 'destination directory', getcwd() . '/generated-api-schema')
			->addOption('base', 'b', InputOption::VALUE_REQUIRED)
			->addOption('incdir', 'I', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'additional include directory');
	}
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$src = $input->getArgument('src');
		$dst = $input->getArgument('dst');

		if (!is_dir($src)) {
			throw new \Exception("$src is not found");
		}

		$processor = new SpecProcessor();
		$processor->addIncludeDirectory('./_includes');

		$base = $input->getOption('base');
		if ($base) {
			$processor->setBaseSpecFile($base);
		}
		$inc = $input->getOption('incdir');
		if ($inc) {
			foreach ($inc as $dir) {
				$processor->addIncludeDirectory($dir);
			}
		}

		$processor->process($src, $dst, 'schema');
		return 0;
	}
}
