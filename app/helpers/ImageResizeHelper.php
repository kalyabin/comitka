<?php
namespace app\helpers;

use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use yii\imagine\Image;
use Imagine\Image\Point;

/**
 * Хелпер для сжатия или кропа изображений
 */
class ImageResizeHelper
{
    /**
     * Пропорционально сжать изображение и вернуть объект для работы с ним.
     *
     * Если исходное изображение меньше по размерам - ничего с ним не делает.
     *
     * @param string $tmpFileName Путь к исходному файлу
     * @param integer $paramWidth Ширина для ресайза
     * @param integer $paramHeight Высота для ресайза
     *
     * @return ImageInterface
     */
    public static function propResizeImage($tmpFileName, $paramWidth, $paramHeight)
    {
        $newImage = Image::getImagine()->open($tmpFileName);
        $imageSizes = $newImage->getSize();

        $width = $imageSizes->getWidth();
        $height = $imageSizes->getHeight();

        if ($width > $paramWidth || $height > $paramHeight) {
            // ресайзить только в случае, если исходные размеры больше существующих
            $newWidth = $width;
            $newHeight = $height;
            if ($width / $height > $paramWidth / $paramHeight) {
                $newWidth = $paramWidth;
                $newHeight = round($newWidth * $height / $width);
            }
            elseif ($width / $height <= $paramWidth / $paramHeight) {
                $newHeight = $paramHeight;
                $newWidth = round($newHeight * $width / $height);
            }

            if ($newWidth != $width || $newHeight != $height) {
                $newImage->resize(new Box($newWidth, $newHeight));
            }
        }

        return $newImage;
    }

    /**
     * Скропать изображения и вернуть объет для работы с ним.
     *
     * Если исходное изображение меньше по размерам - сначала его ресайзит до нужных размеров.
     *
     * @param string $tmpFileName Путь к исходному файлу
     * @param integer $paramWidth Ширина для кропа
     * @param integer $paramHeight Высота для кропа
     *
     * @return ImageInterface
     */
    public static function cropImage($tmpFileName, $paramWidth, $paramHeight)
    {
        $newImage = Image::getImagine()->open($tmpFileName);

        $imageSizes = $newImage->getSize();

        $width = $imageSizes->getWidth();
        $height = $imageSizes->getHeight();

        //Если меньше нужных размеров, то сначала пропорционально ресайзим
        if ($width < $paramWidth || $height < $paramHeight) {
            $newHeight = $height;
            $newWidth = $width;

            if ($width/$height > $paramWidth/$paramHeight) {
                $newHeight = $paramHeight;
                $newWidth = round($newHeight * $width / $height);
            } elseif ($width/$height <= $paramWidth/$paramHeight) {
                $newWidth = $paramWidth;
                $newHeight = round($newWidth * $height / $width);
            }

            $newImage->resize(new Box($newWidth, $newHeight))->save($tmpFileName, ['quality' => 80]);
        }

        $box = new Box($paramWidth, $paramHeight);

        if (($newImage->getSize()->getWidth() <= $box->getWidth() && $newImage->getSize()->getHeight() <= $box->getHeight()) || (!$box->getWidth() && !$box->getHeight())) {
            return $newImage->copy();
        }

        $newImage = $newImage->thumbnail($box, ManipulatorInterface::THUMBNAIL_OUTBOUND);

        // create empty image to preserve aspect ratio of thumbnail
        $thumb = Image::getImagine()->create(
            new Box($newImage->getSize()->getWidth() , $newImage->getSize()->getHeight()),
            new Color('FFF', 100)
        );
        $thumb->paste($newImage, new Point(0, 0));

        return $thumb;
    }
}
