<?php
declare(strict_types=1);

namespace Turenar\ApiSchema\Tree;

use Turenar\ApiSchema\SpecView;

abstract class AbstractTreeElement implements TreeElement
{
	/** @var SpecView */
	protected $spec;

	public function __construct(SpecView $spec)
	{
		$this->spec = $spec;
	}

	public function getSpec(): SpecView
	{
		return $this->spec;
	}
}
