<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\Container;

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
	 * Parameter name pattern.
	 *
	 * @see https://raw.githubusercontent.com/httpwg/http-core/master/httpbis.abnf
	 *
	 * @var string
	 */
	const PARAMETER_NAME_PATTERN = '[A-Za-z0-9!#$%\'*+.^_`|~-]+';

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
		$pattern = self::STRING_PATTERN;
		if ($strict)
			$pattern = '^' . $pattern . '$';
		else
			$pattern = '^[\x9\x20]*' . $pattern;

		$matches = [];
		if (!\preg_match(chr(1) . $pattern . chr(1) . 'i', $mediaTypeString, $matches))
			throw new MediaTypeException($mediaTypeString, 'Not a valid media type string');

		$subType = null;
		if (Container::keyExists($matches, 2))
		{
			$facets = explode('.', $matches[2]);
			$syntax = Container::keyValue($matches, 3, null);
			$subType = new MediaSubType($facets, $syntax);
		}

		return new MediaType($matches[1], $subType);
	}

	const STRING_PATTERN = '([a-z0-9](?:[a-z0-9!#$&^_-]{0,126}))/((?:[a-z0-9](?:[a-z0-9!#$&^_-]{0,126}))(?:\.(?:[a-z0-9](?:[a-z0-9!#$&^_-]{0,126})))*)(?:\+([a-z0-9](?:[a-z0-9!#$&^_-]{0,126})))*';

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