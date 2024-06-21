<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ProductGroupDto
{
    public function __construct(
        #[Assert\Length(min: 1, max: 100, minMessage: 'Nimi peab olema vähemalt 1 tähemärk', maxMessage: 'Nimi peab olema kuni 100 tähemärki')]
        public string $name,
        public ?int   $seq,
    )
    {
    }
}
