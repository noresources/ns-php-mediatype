<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\NotComparableException;
use NoreSources\TypeConversion;
use NoreSources\TypeDescription;

trait MediaTypeCompareTrait
{

	public function compare($b)
	{
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::fromString(TypeConversion::toString($b));
		}

		if ($a->getType() == MediaRange::ANY)
			return (($b->getType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getType() == MediaRange::ANY)
			return 1;

		if ($a->getSubType() == MediaRange::ANY)
			return (($b->getSubType() == MediaRange::ANY) ? 0 : -1);
		elseif ($b->getSubType() == MediaRange::ANY)
			return 1;

		return $a->getSubType()->compare($b->getSubType());
	}
}