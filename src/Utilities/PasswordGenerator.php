<?php

namespace App\Utilities;

class PasswordGenerator
{
    private int $length;
    private string $available_sets;

    /**
     * @param int $length
     * @param string $available_sets
     */
    public function __construct(int $length, string $available_sets)
    {
        $this->length = $length;
        $this->available_sets = $available_sets;
    }

    /**
     * @return string
     */
    public function generateStrongPassword(): string
    {
        $sets = '';
        if (str_contains($this->available_sets, 'l'))
            $sets .= 'abcdefghjkmnpqrstuvwxyz';
        if (str_contains($this->available_sets, 'u'))
            $sets .= 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (str_contains($this->available_sets, 'd'))
            $sets .= '0123456789';
        if (str_contains($this->available_sets, 's'))
            $sets .= '!@#$£%&*?(){}[]&-+_.,;:';

        $pass = [];
        $alphaLength = strlen($sets) - 1;
        for ($i = 0; $i < $this->length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $sets[$n];
        }

        return implode($pass);
    }
}
