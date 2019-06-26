<?php
declare(strict_types=1);


namespace Turenar\ApiSchema;


class FilePath
{
	/** @var string */
	private $base_path;
	/** @var string|null */
	private $dir;
	/** @var string|null */
	private $filename;

	public function __construct(string $base_path, ?string $dir = null, ?string $filename = null)
	{
		$this->base_path = $base_path;
		$this->dir = $dir;
		$this->filename = $filename;
	}

	public function getBasePath(): ?string
	{
		return $this->base_path;
	}

	public function getDir(): ?string
	{
		return $this->dir;
	}

	public function getFilename(): string
	{
		return $this->filename;
	}

	public function getPath(): string
	{
		if ($this->dir !== null) {
			return $this->base_path . '/' . $this->dir . '/' . $this->filename;
		} else {
			return $this->base_path;
		}
	}
}
