<?php
namespace App\Enums;

enum CropName: string
{
    case Apple = 'apple';
    case Grape = 'grape';
    case Peach = 'peach';
    case Potato = 'potato';
    case Strawberry = 'strawberry';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
