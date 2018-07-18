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
	public function __construct(SpecView $view, string $field_path, string $reason)
	{
		parent::__construct(sprintf("%s [%s:%s]", $reason, $view->getFilename(), $field_path));
	}
}
