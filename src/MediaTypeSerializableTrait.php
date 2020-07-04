<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */

/**
 *
 * @package Core
 */
namespace NoreSources\MediaType;

use NoreSources\Http\ParameterMapSerializer;

trait MediaTypeSerializableTrait
{

	public function serialize()
	{
		$s = \strval($this);
		if ($this->getParameters()->count())
			$s .= '; ' . ParameterMapSerializer::serializeParameters($this->getParameters());
		return $s;
	}

	public function unserialize($serialized)
	{
		$p = \strpos($serialized, ';');
		$mts = \trim(($p === false) ? $serialized : \substr($serialized, 0, $p));
		$mt = static::fromString($mts);

		$this->mainType = $mt->getType();
		$this->subType = $mt->getSubType();

		if ($p !== false)
		{
			$parameters = [];
			ParameterMapSerializer::unserializeParameters($parameters,
				\trim(\substr($serialized, $p + 1)));
			$this->setParameters($parameters);
		}
	}
}