<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Exception;


use Turenar\ApiSchema\Generator\ContentGenerator;

class NotSupportedConversionException extends GenerationException
{
	public static function notSupportedDirToFile(ContentGenerator $generator, string $source, string $target)
	{
		$message = sprintf(
			"Generator `%s' cannot generate single file `%s' from a directory `%s'.\n"
			. "Specify directory name as target if you want to generate from a directory",
			$generator->getName(), $target, $source);
		return new NotSupportedConversionException($message);
	}

	public static function notSupportedToDir(ContentGenerator $generator, string $target)
	{
		$message = sprintf(
			"Generator `%s' cannot generate multiple file (`%s' is a directory).\n"
			. "Specify a single file name as target.",
			$generator->getName(), $target);
		return new NotSupportedConversionException($message);
	}
}
