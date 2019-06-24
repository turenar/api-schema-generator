<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


use JsonSchema\Validator;
use PHPUnit\Framework\TestCase;

abstract class TryValidateTestCaseBase extends TestCase
{
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

	protected function check($schema, $test_case_file, $test_index)
	{
		$test_case_contents = file_get_contents($test_case_file);
		if ($test_case_contents === false) {
			self::fail("$test_case_file cannot be loaded");
		}
		$test_case = json_decode($test_case_contents, false);
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

	protected function validate($schema, $input, $expected)
	{
		$validator = new Validator();
		$validator->validate($input, $schema);
		$message = "# input\n" . json_encode($input, JSON_PRETTY_PRINT) . "\n";
		$message .= "# schema\n" . json_encode($schema, JSON_PRETTY_PRINT);

		$this->assertEquals($expected, $validator->isValid(), $message);
	}
}
