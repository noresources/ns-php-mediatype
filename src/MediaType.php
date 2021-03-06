<?php
/**
 * Copyright © 2012 - 2021 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 *
 * @package MediaType
 */
namespace NoreSources\MediaType;

use NoreSources\NotComparableException;
use NoreSources\Container\Container;
use NoreSources\Type\TypeConversion;
use NoreSources\Type\TypeDescription;

/**
 *
 * @see https://www.iana.org/assignments/media-types/media-types.xhtml
 *
 */
class MediaType implements MediaTypeInterface
{

	use MediaTypeStructuredTextTrait;
	use MediaTypeParameterMapTrait;
	use MediaTypeSerializableTrait;
	use MediaTypeCompareTrait;

	/**
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->mainType;
	}

	/**
	 *
	 * @return \NoreSources\MediaType\MediaSubType
	 */
	public function getSubType()
	{
		return $this->subType;
	}

	public function __construct($type, MediaSubType $subType = null)
	{
		$this->mainType = $type;
		$this->subType = $subType;
	}

	public function __toString()
	{
		return strval($this->mainType) . '/' . strval($this->subType);
	}

	public function match($b)
	{
		/**
		 *
		 * @var MediaTypeInterface $a
		 * @var MediaTypeInterface $b
		 */
		$a = $this;

		if (!($b instanceof MediaTypeInterface))
		{
			if (!TypeDescription::hasStringRepresentation($b))
				throw new NotComparableException($a, $b);

			$b = MediaRange::fromString(TypeConversion::toString($b));
		}

		if ($b->getType() == MediaRange::ANY)
			return true;

		if (\strcasecmp($a->getType(), $b->getType()) != 0)
			return false;

		$ast = \strval(\implode('.', $a->getSubType()->getFacets()));
		$bst = \strval(\implode('.', $b->getSubType()->getFacets()));

		if ($bst == MediaRange::ANY)
			return true;

		$stc = \strcasecmp($ast, $bst);

		if ($a->getSubType()->compare($b->getSubType()) >= 0 &&
			($a->getSubType()->getFacetCount() ==
			$b->getSubType()->getFacetCount()) && ($stc != 0))
			return false;

		if ($a->getSubType()->getFacetCount() >
			$b->getSubType()->getFacetCount())
			$stc = 0;

		$as = $a->getSubType()->getStructuredSyntax();
		$bs = $b->getSubType()->getStructuredSyntax();

		if (empty($as))
			return (empty($bs) && ($stc == 0));
		elseif (empty($bs))
			return false;

		return ($stc == 0) && (\strcasecmp($as, $bs) == 0);
	}

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Mediga type string
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromString($mediaTypeString, $strict = true)
	{
		$pattern = RFC6838::MEDIA_TYPE_PATTERN;
		if ($strict)
			$pattern = '^' . $pattern . '$';
		else
			$pattern = '^[\x9\x20]*' . $pattern;

		$matches = [];
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i',
			$mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString,
				'Not a valid media type string');

		$subType = null;
		if (Container::keyExists($matches, 2))
		{
			$facets = $matches[2];
			$length = \strlen($facets);
			$syntax = null;

			$lastPlus = \strrpos($facets, '+');
			if ($lastPlus !== false && $lastPlus < ($length - 1))
			{
				$syntax = \substr($facets, $lastPlus + 1);
				$facets = \substr($facets, 0, $lastPlus);
			}

			$subType = new MediaSubType(\explode('.', $facets), $syntax);
		}

		return new MediaType($matches[1], $subType);
	}

	/**
	 * Media main type
	 *
	 * @var string
	 */
	private $mainType;

	/**
	 *
	 * @var MediaSubType
	 */
	private $subType;
}