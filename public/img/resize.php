<?php
$sourcePath = __DIR__ . '/logo-universidad.png';
$destPath = __DIR__ . '/logo-universidad-watermark.png';

$info = getimagesize($sourcePath);
$width = $info[0];
$height = $info[1];

// Target width for watermark (e.g., 500px)
$targetWidth = 500;
$targetHeight = floor($height * ($targetWidth / $width));

$image = imagecreatefrompng($sourcePath);

// Create a new true color image
$newImage = imagecreatetruecolor($targetWidth, $targetHeight);

// Preserve transparency
imagealphablending($newImage, false);
imagesavealpha($newImage, true);
$transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
imagefilledrectangle($newImage, 0, 0, $targetWidth, $targetHeight, $transparent);

// Resize
imagecopyresampled($newImage, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

// Save with maximum compression
imagepng($newImage, $destPath, 9);

imagedestroy($image);
imagedestroy($newImage);

echo "Resized logo-universidad.png from {$width}x{$height} to {$targetWidth}x{$targetHeight} and saved as logo-universidad-watermark.png\n";
