<?php
namespace smertozturk\imagepixelfinder\Controllers;

use smertozturk\imagepixelfinder\ImagePixelFinder;

class ImagePixelFinderController
{
    public function findPixelColor(ImagePixelFinder $ipf, $imagePath, $x, $y): string
    {
        return $ipf->findPixelColor($imagePath, $x, $y);
    }

    public function findWeightColor(ImagePixelFinder $ipf, $imagePath): array
    {
        return $ipf->findWeightColor($imagePath);
    }

    public function findMostUsedColor(ImagePixelFinder $ipf, $imagePath): string
    {
        return $ipf->findMostUsedColor($imagePath);
    }
}
