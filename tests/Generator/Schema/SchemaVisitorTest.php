<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Generator\Schema;


use Turenar\ApiSchema\ExposedSpecProcessor;
use Turenar\ApiSchema\TryValidateTestCaseBase;

class SchemaVisitorTest extends TryValidateTestCaseBase
{
	const FILES_DIR = __DIR__ . '/files/';

	public function filesProvider()
	{
		$files = [];
		foreach (new \DirectoryIterator(self::FILES_DIR) as $test_spec_dir) {
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
	 * @dataProvider filesProvider
	 */
	public function testParse($dir, $test_index)
	{
		$generator = new ExposedSpecProcessor();
		$base_dir = self::FILES_DIR . '/' . $dir;

		$visitor = new SchemaVisitor();
		$schema = $visitor->visit($generator->parseFile($base_dir.'/spec.yaml'));
		$this->assertNotEmpty($schema['input'] ?? null, 'test specification is not valid');
		$this->assertNotEmpty($schema['output'] ?? null, 'test specification is not valid');

		$this->check($schema, $base_dir . '/tests.json', $test_index);
	}
}
