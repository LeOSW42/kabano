<?

function generate_image_thumbnail($source_image_path, $thumbnail_image_path, $width, $height)
{
	list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
	switch ($source_image_type) {
		case IMAGETYPE_GIF:
			$source_gd_image = imagecreatefromgif($source_image_path);
			break;
		case IMAGETYPE_JPEG:
			$source_gd_image = imagecreatefromjpeg($source_image_path);
			break;
		case IMAGETYPE_PNG:
			$source_gd_image = imagecreatefrompng($source_image_path);
			break;
	}
	if ($source_gd_image === false) {
		return false;
	}

	$src_x = 0;
	$src_y = 0;
	$thumbnail_image_height = $height;
	$thumbnail_image_width = $width;
	// If the limitation is on the height (cuts on the width)
	if($height*$source_image_width/$source_image_height > $width) {
		$src_x = (int)(($source_image_width - $source_image_height * $width / $height) / 2);
		$source_image_width = (int)($source_image_height * $width / $height);
	} else {
		$src_y = (int)(($source_image_height - $source_image_width * $height / $width) / 2);
		$source_image_height = (int)($source_image_width * $height / $width);
	}

	$thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
	imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, $src_x, $src_y, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
	imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
	imagedestroy($source_gd_image);
	imagedestroy($thumbnail_gd_image);
	return true;
}

?>