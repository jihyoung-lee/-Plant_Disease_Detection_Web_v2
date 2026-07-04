<?php
namespace App\Enums;

enum CropName: string
{
    case Apple = 'apple';
    case Grape = 'grape';
    case Peach = 'peach';
    case Potato = 'potato';
    case Strawberry = 'strawberry';
    public function korean(): string
    {
        return match ($this) {
            self::Apple => '사과',
            self::Grape => '포도',
            self::Peach => '복숭아',
            self::Potato => '감자',
            self::Strawberry => '딸기',
        };
    }
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
