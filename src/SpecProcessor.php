<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


use Turenar\ApiSchema\Exception\SpecException;
use Turenar\ApiSchema\Generator\ContentGenerator;
use Turenar\ApiSchema\Generator\Schema\SchemaContentGenerator;
use Turenar\ApiSchema\Resolver\IncludeResolver;
use Turenar\ApiSchema\Tree\Endpoint;

class SpecProcessor implements IncludeResolver
{
	protected static $generators = [
		'schema' => SchemaContentGenerator::class,
	];

	protected $includes = [];
	protected $base_spec_file;
	protected $base_spec;

	public function __construct()
	{
	}

	protected function loadYaml($infile)
	{
		if (!file_exists($infile)) {
			throw new \RuntimeException("$infile is not found");
		}
		$yaml = yaml_parse_file($infile);
		if ($yaml === false) {
			throw new \RuntimeException("$infile is not readable as yaml");
		}
		return $yaml;
	}

	public function setBaseSpecFile(string $filename)
	{
		$this->base_spec_file = $filename;
		$this->base_spec = new SpecView($this, null, $this->loadYaml($filename), $filename, '');
	}

	protected function parseFile(string $infile): Endpoint
	{
		$yaml = $this->loadYaml($infile);
		$spec = new SpecView($this, null, $yaml, $infile, '');

		if ($this->base_spec) {
			$spec->merge($this->base_spec);
		}
		if (!isset($yaml['input'])) {
			$spec->newChild('input', '<default>');
		}
		$spec->requireField('output');

		return new Endpoint($spec);
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
				} elseif (!is_array($yaml)) {
					throw new SpecException($parent, $parent->newChildPath($name), 'loaded yaml is not array');
				}
				return new SpecView($parent->getResolver(), $name, $yaml, $path, '');
			}
		}
		return null;
	}

	public function process($src, $dst, $generator_name)
	{
		$generator = $this->newGenerator($generator_name);
		$iterator = new SourceTargetIterable($generator, $src, $dst);

		$targetFileObject = null;
		foreach ($iterator as $source => $target) {
			if (!$iterator->isTargetSingle() || $targetFileObject === null) {
				$targetFileObject = $generator->targetFileObject($target, true);
			}
			$endpoint = $this->parseFile($source);
			$generator->generateContent($endpoint, $targetFileObject);
		}
	}

	protected function newGenerator($generator_name): ContentGenerator
	{
		$generator = static::$generators[$generator_name] ?? null;
		if ($generator === null) {
			throw new \RuntimeException("unknown generator: $generator_name");
		}
		return new $generator;
	}

	protected function replaceExtension(string $source, string $target_extension)
	{
		$source_filename = basename($source);
		$source_extension_index = strrchr($source, '.');
		$target_filename = $source_extension_index < 0
			? $source_filename
			: (substr($source_filename, 0, $source_extension_index) . '.' . $target_extension);
		return dirname($source, $target_filename);
	}
}