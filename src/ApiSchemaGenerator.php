<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


use Turenar\ApiSchema\Tree\Endpoint;

class ApiSchemaGenerator implements IncludeResolver
{
	protected $includes = [];
	protected $base_spec_file;
	protected $base_spec;

	public function __construct()
	{
	}

	protected function loadYaml($infile)
	{
		if (!file_exists($infile)) {
			throw new \Exception("$infile is not found");
		}
		$yaml = yaml_parse_file($infile);
		if ($yaml === false) {
			throw new \Exception("$infile is not readable as yaml");
		}
		if (!(isset($yaml['input']) && isset($yaml['output']))) {
			throw new \Exception("$infile: required root object is not found");
		}
		return $yaml;
	}

	public function setBaseSpecFile(string $filename)
	{
		$this->base_spec_file = $filename;
		$this->base_spec = new SpecView($this, null, $this->loadYaml($filename), $filename, '');
	}

	public function generateSchema($spec): array
	{
		return (new Endpoint(new SpecView($this, null, $spec, '(null)', '')))->getSchema();
	}

	public function generateFile($infile, $outfile)
	{
		$yaml = $this->loadYaml($infile);
		$spec = new SpecView($this, null, $yaml, $infile, '');
		if ($this->base_spec) {
			$spec->merge($this->base_spec);
		}
		$schema = (new Endpoint($spec))->getSchema();
		file_put_contents($outfile, json_encode($schema));
	}

	public function addIncludeDirectory(string $dir)
	{
		$this->includes[] = $dir;
	}

	public function resolve(SpecView $parent, string $name, string $include_filename): ?SpecView
	{
		foreach ($this->includes as $include_dir) {
			$path = $include_dir . '/' . $include_filename;
			if (file_exists($path)) {
				$yaml = yaml_parse_file($path);
				if ($yaml === false) {
					throw new SpecException($parent, $parent->newChildPath($name), 'yaml parse failed');
				} else if (!is_array($yaml)) {
					throw new SpecException($parent, $parent->newChildPath($name), 'loaded yaml is not array');
				}
				return new SpecView($parent->getResolver(), $name, $yaml, $path, '');
			}
		}
		return null;
	}
}
