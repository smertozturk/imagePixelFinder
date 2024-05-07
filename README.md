# Image Pixel Finder

A tool that gives the color weight at the edges of the image, the most used color tone in the given image, or the color tone in any pixel.

## Installation

You can install the package via Composer:

```bash
composer require smertozturk/image_pixel_finder
```

## Usage
To use the package, first include the ImagePixelFinder class. Then, you can use the 3 method of the ImagePixelFinder;

```php
use smertozturk\imagepixelfinder\ImagePixelFinder;

$imagePath = 'path/to/your/image.png';
$ipf = new ImagePixelFinder();

// Returns specific pixel color on given coordinates (#hex)
$x = 1;
$y = 1;
$pixelColor = $ipf->findPixelColor($imagePath, $x, $y);

// Returns the weighted color tones used around the edges of the image (Array of #hex (top, bottom, left, right))
$edgesColor = $ipf->findWeightColor($imagePath);

// Return most used color tone on image (#hex)
$edgesColor = $ipf->findWeightColor($imagePath);
```

## Contributing
If you would like to contribute to the package, please fork it and submit a pull request. Your contributions are welcome!

## License
This package is licensed under the MIT License. For more information, see the LICENSE file.
