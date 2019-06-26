<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Generator\Schema;


use Turenar\ApiSchema\Generator\ContentGenerator;
use Turenar\ApiSchema\FilePath;
use Turenar\ApiSchema\Tree\Endpoint;

class SchemaContentGenerator implements ContentGenerator
{
	public function canTargetSingleFile(): bool
	{
		return false;
	}

	public function canTargetMultiFile(): bool
	{
		return true;
	}

	public function targetExtension(): string
	{
		return 'json';
	}

	public function targetFileObject(string $target_path, bool $single): ?\SplFileObject
	{
		return new \SplFileObject($target_path, 'w');
	}

	public function generateContent(Endpoint $endpoint, FilePath $source, ?\SplFileObject $file)
	{
		$visitor = new SchemaVisitor();
		$content = $visitor->visitEndpoint($endpoint, null);
		$file->fwrite(json_encode($content));
	}

	public function getName(): string
	{
		return 'schema';
	}
}
