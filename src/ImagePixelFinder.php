<?php

namespace smertozturk\imagepixelfinder;

class ImagePixelFinder {

    private function calculateColorWeight($colors): float|int
    {
        $colorWeights = [];
        foreach ($colors as $color) {
            // RGB renklerinin ağırlıklı toplamını hesapla
            $colorWeights[] = ($color['red'] * 0.299) + ($color['green'] * 0.587) + ($color['blue'] * 0.114);
        }
        // Ağırlıklı toplamı ortalamasını al
        return array_sum($colorWeights) / count($colorWeights);
    }

    private function rgbToHex($color): string
    {
        $hex = "#";
        $hex .= str_pad(dechex($color), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($color), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($color), 2, "0", STR_PAD_LEFT);
        return $hex;
    }

    private function rgbToHex3Code($r, $g, $b): string
    {
        $hex = "#";
        $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
        return $hex;
    }

    private function getImageType($imagePath): int
    {
        $imageInfo = getimagesize($imagePath);
        return $imageInfo[2];
    }

    private function loadImage($imagePath, $imageType)
    {
        return match ($imageType) {
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            default => null,
        };
    }

    public function findPixelColor($imagePath, $x, $y): string
    {
        // Determine the image type
        $imageType = $this->getImageType($imagePath);

        // Load the image based on the type
        $image = $this->loadImage($imagePath, $imageType);

        // Get the color of the pixel at the specified x and y coordinates
        $rgb = imagecolorat($image, $x, $y);

        // Get the RGB values
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        // Create the hexadecimal representation of the color
        $color = sprintf("#%02x%02x%02x", $r, $g, $b);

        // Clean up the memory
        imagedestroy($image);

        return $color;
    }

    public function findWeightColor($imagePath): array
    {
        // Determine the image type
        $imageType = $this->getImageType($imagePath);

        // Load the image based on the type
        $image = $this->loadImage($imagePath, $imageType);

        // Width and height of the image
        $width = imagesx($image);
        $height = imagesy($image);

        // Number of pixels on the edges (e.g. 5%)
        $marginPercentage = 5;
        $marginPixels = min($width, $height) * $marginPercentage / 100;

        // Top edge
        $topColors = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $marginPixels; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $topColors[] = $colorRGB;
            }
        }

        // Bottom edge
        $bottomColors = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = $height - $marginPixels; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $bottomColors[] = $colorRGB;
            }
        }

        // Left edge
        $leftColors = [];
        for ($x = 0; $x < $marginPixels; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $leftColors[] = $colorRGB;
            }
        }

        // Right edge
        $rightColors = [];
        for ($x = $width - $marginPixels; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $rightColors[] = $colorRGB;
            }
        }

        // Calculate hue weights
        $topWeight = $this->calculateColorWeight($topColors);
        $bottomWeight = $this->calculateColorWeight($bottomColors);
        $leftWeight = $this->calculateColorWeight($leftColors);
        $rightWeight = $this->calculateColorWeight($rightColors);

        // Convert color tones to hex codes
        $topColorHex = $this->rgbToHex($topWeight);
        $bottomColorHex = $this->rgbToHex($bottomWeight);
        $leftColorHex = $this->rgbToHex($leftWeight);
        $rightColorHex = $this->rgbToHex($rightWeight);

        //Return results
        return [
            'top' => $topColorHex,
            'bottom' => $bottomColorHex,
            'left' => $leftColorHex,
            'right' => $rightColorHex,
        ];
    }

    public function findMostUsedColor($imagePath): string
    {
        // Determine the image type
        $imageType = $this->getImageType($imagePath);

        // Load the image based on the type
        $image = $this->loadImage($imagePath, $imageType);

        // Width and height of the image
        $width = imagesx($image);
        $height = imagesy($image);

        // Create an array for counting colors
        $colorCounts = [];

        // Count the colors by moving around the image
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $colorKey = $colorRGB['red'] . ',' . $colorRGB['green'] . ',' . $colorRGB['blue'];
                if (!isset($colorCounts[$colorKey])) {
                    $colorCounts[$colorKey] = 0;
                }
                $colorCounts[$colorKey]++;
            }
        }

        // Find the most used color
        arsort($colorCounts);
        $mostUsedColor = key($colorCounts);

        // Convert hue to hexadecimal
        $mostUsedColorRGB = explode(',', $mostUsedColor);

        // Return the result
        return $this->rgbToHex3Code($mostUsedColorRGB[0], $mostUsedColorRGB[1], $mostUsedColorRGB[2]);
    }
}
