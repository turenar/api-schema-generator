<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Exception;


class FileNotFoundException extends GenerationException
{
	public static function create(string $filename)
	{
		return new FileNotFoundException("file not exists: $filename");
	}
}
