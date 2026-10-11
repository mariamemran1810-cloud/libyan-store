<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * يُرمى عندما يقل المخزون عن المطلوب أثناء إتمام الطلب.
 */
final class OutOfStockException extends \RuntimeException
{
    public function __construct(
        private readonly string $productName,
        private readonly int $available
    ) {
        parent::__construct('الكمية المطلوبة من "' . $productName . '" غير متوفرة (المتاح: ' . $available . ').');
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function available(): int
    {
        return $this->available;
    }
}