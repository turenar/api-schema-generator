<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;

use JsonSchema\Validator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class SpecProcessorTest extends TestCase
{
	const SCHEMA_GENERATOR_FILES_DIR = __DIR__ . '/Generator/Schema/files/';

	/** @var vfsStreamDirectory */
	protected $root;
	/** @var vfsStreamDirectory */
	protected $input;
	/** @var vfsStreamDirectory */
	protected $output;

	protected function setUp()
	{
		parent::setUp();

		$this->root = vfsStream::setup('root', null, ['input' => [], 'output' => []]);
		$this->input = $this->root->getChild('input');
		$this->output = $this->root->getChild('output');
	}

	public function schemaGeneratorFiles()
	{
		$files = [];
		foreach (new \DirectoryIterator(self::SCHEMA_GENERATOR_FILES_DIR) as $test_spec_dir) {
			if (!$test_spec_dir->isDot() && $test_spec_dir->isDir()) {
				$yaml_filename = $test_spec_dir->getPathname() . '/spec.yaml';
				$test_spec = yaml_parse_file($yaml_filename);
				if (empty($test_spec)) {
					throw new \Exception("$yaml_filename is not yaml?");
				}

				$test_cases = json_decode(file_get_contents($test_spec_dir->getPathname() . '/tests.json'), true);
				foreach ($test_cases['tests'] as $index => $_) {
					$files[] = [$test_spec_dir->getFilename(), $index];
				}
			}
		}
		return $files;
	}

	/**
	 * @dataProvider schemaGeneratorFiles
	 */
	public function testProcessSchemaGeneratorFiles($dir, $test_index)
	{
		$processor = new ExposedSpecProcessor();
		$base_dir = self::SCHEMA_GENERATOR_FILES_DIR . '/' . $dir;

		vfsStream::copyFromFileSystem($base_dir, $this->input);

		$processor->process($this->input->url(), $this->output->url(), 'schema');

		$schema = json_decode(file_get_contents($this->output->url() . '/spec.json'), true);

		$test_case = json_decode(file_get_contents($base_dir . '/tests.json'), false);
		$test = $test_case->tests[$test_index];
		$this->assertObjectHasAttribute('expected', $test);
		$expected = $this->parseExpectation($test->expected);
		if (isset($test->input)) {
			$this->validate($schema['input'], $test->input, $expected);
		}
		if (isset($test->output)) {
			$this->validate($schema['output'], $test->output, $expected);
		}
	}

	protected function parseExpectation($expected)
	{
		if ($expected === 'pass') {
			return true;
		} elseif ($expected === 'fail') {
			return false;
		} else {
			throw new \InvalidArgumentException("expected value must be pass/fail but $expected");
		}
	}

	protected function validate($schema, $input, $expected)
	{
		$validator = new Validator();
		$validator->validate($input, $schema);
		$message = "# input\n" . json_encode($input, JSON_PRETTY_PRINT) . "\n";
		$message .= "# schema\n" . json_encode($schema, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $validator->isValid(), $message);
	}
}
