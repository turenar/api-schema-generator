<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Turenar\ApiSchema\ApiSchemaGenerator;

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
			->addOption('incdir', 'I', InputArgument::REQUIRED | InputArgument::IS_ARRAY,
				'additional include directory');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$src = $input->getArgument('src');
		$dst = $input->getArgument('dst');

		if(!is_dir($src)){
			throw new \Exception('$src is not found');
		}
		
		chdir($src);
		//  init_propel_map(); // FIXME
		$file_pattern = '@\./[^_].+\.yaml$@';
		$dir = new \RecursiveDirectoryIterator('.');
		$ite = new \RecursiveIteratorIterator($dir);
		$yaml_files_iterator = new \RegexIterator($ite, $file_pattern, \RegexIterator::GET_MATCH);
		$generator = new ApiSchemaGenerator();
		$generator->addIncludeDirectory('./_includes');
		foreach ($yaml_files_iterator as $yaml_files) {
			foreach ($yaml_files as $yaml_file) {
				if ($yaml_file[0] == '_') { // _includes
					continue;
				}
				$outfile = $dst . '/' . str_replace('.yaml', '.json', $yaml_file);
				$outdir = dirname($outfile);
				if (!is_dir($outdir)) {
					mkdir($outdir, 0755, true);
				}

				$generator->generateFile($yaml_file, $outfile);
			}
		}
	}
}