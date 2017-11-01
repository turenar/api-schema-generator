<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Test\Schema;


use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;
use Turenar\ApiSchema\ApiSchemaGenerator;

class ParserTest extends TestCase
{
	const FILES_DIR = __DIR__ . '/files/';

	public function filesProvider()
	{
		$files = [];
		foreach (new \DirectoryIterator(self::FILES_DIR) as $test_case) {
			if (!$test_case->isDot() && $test_case->isDir()) {
				$files[] = [$test_case->getFilename()];
			}
		}
		return $files;
	}

	/**
	 * @dataProvider filesProvider
	 */
	public function testParse($dir)
	{
		$generator = new ApiSchemaGenerator();
		$base_dir = self::FILES_DIR . '/' . $dir;
		$test_spec = yaml_parse_file($base_dir . '/spec.yaml');
		$this->assertNotEmpty($test_spec, 'test specification is not valid');
		$schema = $generator->generateSchema($test_spec);
		$this->assertNotEmpty($schema['input'] ?? null, 'test specification is not valid');
		$this->assertNotEmpty($schema['output'] ?? null, 'test specification is not valid');

		$test_case = json_decode(file_get_contents($base_dir . '/tests.json'), false);
		foreach ($test_case->tests as $test) {
			$this->assertObjectHasAttribute('expected', $test);
			$expected = $this->parseExpectation($test->expected);
			if (isset($test->input)) {
				$this->checkParse($schema['input'], $test->input, $expected);
			}
			if (isset($test->output)) {
				$this->checkParse($schema['output'], $test->output, $expected);
			}
		}
	}

	private function parseExpectation($expected)
	{
		if ($expected === 'pass') {
			return true;
		} else if ($expected === 'fail') {
			return false;
		} else {
			throw new \InvalidArgumentException("expected value must be pass/fail but $expected");
		}
	}

	private function checkParse($schema, $input, $expected)
	{
		$validator = new Validator();
		$validator->validate($input, $schema);
		$message = "# input\n" . json_encode($input, JSON_PRETTY_PRINT) . "\n";
		$message .= "# schema\n" . json_encode($schema, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $validator->isValid(), $message);
	}
}
