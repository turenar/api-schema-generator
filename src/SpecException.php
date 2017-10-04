<?php
declare(strict_types=1);

namespace Turenar\ApiSchema;


class SpecException extends \Exception
{
	/**
	 * SpecException constructor.
	 * @param SpecView $view
	 * @param string $field_path
	 * @param string $reason
	 */
	public function __construct($view, $field_path, $reason)
	{
		parent::__construct(sprintf("%s: %s in %s", $reason, $field_path, $view->getFilename()));
	}
}