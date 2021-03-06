<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

trait MediaTypeStructuredTextTrait
{

	public function getStructuredSyntax($registeredOnly = false)
	{
		if (!($this->getSubType() instanceof MediaSubType))
			return null;

		$s = $this->getSubType()->getStructuredSyntax();
		if (!empty($s))
			return $s;

		if ($this->getSubType()->getFacetCount() == 1)
		{
			$facet = $this->getSubType()->getFacet(0);
			if (\strtolower($this->getType()) == 'text')
			{
				if ($registeredOnly &&
					!StructuredSyntaxSuffixRegistry::getInstance()->isRegistered(
						$facet))
					return null;

				if ($facet != MediaRange::ANY)
					return $facet;
			}

			/*
			 * Other types such as application/json
			 */
			if (StructuredSyntaxSuffixRegistry::getInstance()->isRegistered(
				$facet))
				return $facet;
		}

		return null;
	}
}