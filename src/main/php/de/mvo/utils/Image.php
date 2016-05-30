<?php
namespace de\mvo\utils;

class Image
{
	/**
	 * Calculate the size for the specified original size to fit into the specified target size.
	 *
	 * @param int $originalWidth The original width
	 * @param int $originalHeight The original height
	 * @param int $maxWidth The maximum width
	 * @param int $maxHeight The maximum height
	 * @param int $newWidth The calculated width
	 * @param int $newHeight The calculated height
	 */
	public static function calculateResize($originalWidth, $originalHeight, $maxWidth, $maxHeight, &$newWidth, &$newHeight)
	{
		if ($originalWidth <= $maxWidth and $originalHeight <= $maxHeight)
		{
			$newWidth = $originalWidth;
			$newHeight = $originalHeight;
		}
		else
		{
			$ratio = $maxWidth / $originalWidth;

			$newWidth = $maxWidth;
			$newHeight = $originalHeight * $ratio;

			if ($newHeight > $maxHeight)
			{
				$ratio = $maxHeight / $originalHeight;

				$newHeight = $maxHeight;
				$newWidth = $originalWidth * $ratio;
			}
		}

		$newWidth = (int) $newWidth;
		$newHeight = (int) $newHeight;
	}
}