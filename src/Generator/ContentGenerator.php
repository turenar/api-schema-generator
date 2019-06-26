<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Generator;


use Turenar\ApiSchema\FilePath;
use Turenar\ApiSchema\Tree\Endpoint;

interface ContentGenerator
{
	public function canTargetSingleFile(): bool;

	public function canTargetMultiFile(): bool;

	public function targetExtension(): string;

	public function targetFileObject(string $target_path, bool $single): ?\SplFileObject;

	public function generateContent(Endpoint $endpoint, FilePath $source, ?\SplFileObject $file);

	public function getName(): string;
}
