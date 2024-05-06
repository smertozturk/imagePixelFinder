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

    public function findPixelColor($imagePath, $x, $y): string
    {
        // Load the image
        $image = imagecreatefrompng($imagePath);

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
        // Resmi yükle
        $image = imagecreatefromjpeg($imagePath);

        // Resmin genişliği ve yüksekliği
        $width = imagesx($image);
        $height = imagesy($image);

        // Kenarlardaki piksel sayısı (örneğin %5)
        $marginPercentage = 5;
        $marginPixels = min($width, $height) * $marginPercentage / 100;

        // Üst kenar
        $topColors = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $marginPixels; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $topColors[] = $colorRGB;
            }
        }

        // Alt kenar
        $bottomColors = [];
        for ($x = 0; $x < $width; $x++) {
            for ($y = $height - $marginPixels; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $bottomColors[] = $colorRGB;
            }
        }

        // Sol kenar
        $leftColors = [];
        for ($x = 0; $x < $marginPixels; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $leftColors[] = $colorRGB;
            }
        }

        // Sağ kenar
        $rightColors = [];
        for ($x = $width - $marginPixels; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $colorIndex = imagecolorat($image, $x, $y);
                $colorRGB = imagecolorsforindex($image, $colorIndex);
                $rightColors[] = $colorRGB;
            }
        }

        // Renk tonu ağırlıklarını hesapla
        $topWeight = $this->calculateColorWeight($topColors);
        $bottomWeight = $this->calculateColorWeight($bottomColors);
        $leftWeight = $this->calculateColorWeight($leftColors);
        $rightWeight = $this->calculateColorWeight($rightColors);

        // Renk tonlarını hex kodlarına dönüştür
        $topColorHex = $this->rgbToHex($topWeight);
        $bottomColorHex = $this->rgbToHex($bottomWeight);
        $leftColorHex = $this->rgbToHex($leftWeight);
        $rightColorHex = $this->rgbToHex($rightWeight);

        // Sonuçları döndür
        return [
            'top' => $topColorHex,
            'bottom' => $bottomColorHex,
            'left' => $leftColorHex,
            'right' => $rightColorHex,
        ];
    }

    public function findMostUsedColor($imagePath): string
    {
        // Resmi yükle
        $image = imagecreatefromjpeg($imagePath);

        // Resmin genişliği ve yüksekliği
        $width = imagesx($image);
        $height = imagesy($image);

        // Renklerin sayımı için bir dizi oluştur
        $colorCounts = [];

        // Resim üzerinde dolaşarak renkleri say
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

        // En çok kullanılan rengi bul
        arsort($colorCounts);
        $mostUsedColor = key($colorCounts);

        // Renk tonunu hexadecimal olarak dönüştür
        $mostUsedColorRGB = explode(',', $mostUsedColor);
        // Sonucu döndür
        return $this->rgbToHex3Code($mostUsedColorRGB[0], $mostUsedColorRGB[1], $mostUsedColorRGB[2]);
    }
}
