<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


use Turenar\ApiSchema\Tree\Endpoint;

class ApiSchemaGenerator implements IncludeResolver
{
	protected $includes = [];
	public function __construct()
	{
	}

	public function generateSchema($spec): array
	{
		return (new Endpoint(new SpecView($this, $spec, '(null)', '')))->getSchema();
	}

	public function generateFile($infile, $outfile)
	{
		$yaml = yaml_parse_file($infile);
		if ($yaml === false) {
			throw new \Exception("$infile is not readable as yaml");
		}
		if (!(isset($yaml['input']) && isset($yaml['output']))) {
			throw new \Exception("$infile: required root object is not found");
		}
		$schema = (new Endpoint(new SpecView($this, $yaml, $infile, '')))->getSchema();
		file_put_contents($outfile, json_encode($schema));
	}

	public function addIncludeDirectory(string $dir)
	{
		$this->includes[] = $dir;
	}

	public function resolve(string $include_filename)
	{
		foreach ($this->includes as $include_dir) {
			$path = $include_dir . '/' . $include_filename;
			if (file_exists($path)) {
				return $path;
			}
		}
		return null;
	}
}
