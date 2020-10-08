<?php
/**
 * Copyright © 2012 - 2020 by Renaud Guillard (dev@nore.fr)
 * Distributed under the terms of the MIT License, see LICENSE
 */
namespace NoreSources\MediaType;

use NoreSources\Bitset;

/**
 * Media Type and Media Range factory
 *
 * Constructs Media Type and Media Range from various ways.
 */
class MediaTypeFactory
{

	/**
	 * Parse a media type string
	 *
	 * @param string $mediaTypeString
	 *        	Medig type string
	 * @param boolean $acceptRange
	 *        	Accept Media ranges
	 * @throws MediaTypeException
	 * @return \NoreSources\MediaType\MediaType \NoreSources\MediaType\MediaRange
	 */
	public static function fromString($mediaTypeString,
		$acceptRange = true)
	{
		try
		{
			return MediaType::fromString($mediaTypeString);
		}
		catch (MediaTypeException $e)
		{
			if (!$acceptRange)
				throw $e;
			return MediaRange::fromString($mediaTypeString);
		}
	}

	/**
	 * Attempt to guess media type from media content
	 *
	 * @var integer
	 */
	const FROM_CONTENT = Bitset::BIT_01;

	/**
	 * ttempt to guess media type from file extension
	 *
	 * @var integer
	 */
	const FROM_EXTENSION = Bitset::BIT_02;

	/**
	 * ttempt to guess media type from file extension first
	 *
	 * @var integer
	 */
	const FROM_ALL = self::FROM_CONTENT | self::FROM_EXTENSION;

	const FROM_EXTENSION_FIRST = self::FROM_EXTENSION | Bitset::BIT_03;

	/**
	 * Get media type of a file or stream
	 *
	 * @param string|resource $media
	 *        	File path or stream
	 * @param integer $mode
	 *        	Media type guessing options
	 * @return \NoreSources\MediaType\MediaType
	 */
	public static function fromMedia($media, $mode = self::FROM_ALL)
	{
		$contentType = null;
		$extensionType = null;
		$type = null;

		if (($mode & self::FROM_EXTENSION) == self::FROM_EXTENSION &&
			\is_file($media))
		{
			$extensionType = MediaTypeFileExtensionRegistry::mediaTypeFromExtension(
				pathinfo($media, PATHINFO_EXTENSION));

			if ($extensionType instanceof MediaTypeInterface)
				if (($mode & self::FROM_EXTENSION_FIRST) ==
					self::FROM_EXTENSION_FIRST)
					return $extensionType;
		}

		if (($mode & self::FROM_CONTENT) == self::FROM_CONTENT)
		{
			if (\is_file($media) && \is_readable($media))
			{
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$contentType = $finfo->file($media);
			}
			else
				$contentType = @mime_content_type($media);
		}

		if ($extensionType &&
			(!\is_string($contentType) || ($contentType == 'text/plain')))
			return $extensionType;

		if (\is_string($contentType))
			return MediaType::fromString($contentType);

		throw new \Exception('Unable to recognize media type');
	}
}