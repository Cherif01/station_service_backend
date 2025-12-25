<?php

namespace App\Traits;

use App\Modules\Backoffice\Models\Client;

trait GenerateClientCode
{

    // Retourne le code generer de company
    public function getClientCode(string $name, $count = null)
    {

        $initials = $this->getClientInitials($name);
        $totalCompanies = $count ?? Client::count() + 1;

        $numberPart = str_pad($totalCompanies, 4, '0', STR_PAD_LEFT);
        $suffix = $this->getClientSuffix($totalCompanies);

        return "{$initials}-{$numberPart}-{$suffix}";
    }

    // Retourne les initials du nom de Client
    protected function getClientInitials(string $name): string
    {
        $words = preg_split('/\s+/', $name);
        $wordCount = count($words);

        // If two words: take first two characters of each word
        if ($wordCount === 2) {
            $initials = strtoupper(substr($words[0], 0, 2)) . strtoupper(substr($words[1], 0, 2));
            return $initials;
        }

        // If one word
        if ($wordCount === 1) {
            $word = $words[0];
            $length = strlen($word);

            // If exactly 4 characters: keep it as is
            if ($length === 4) {
                return strtoupper($word);
            }

            // If more than 4 characters: extract beginning, middle, and end
            if ($length > 4) {
                $first = strtoupper(substr($word, 0, 1));
                $middle = strtoupper(substr($word, intval($length / 2), 1));
                $last = strtoupper(substr($word, -1));
                return $first . $middle . $last;
            }

            // If less than 4 characters: return as is (uppercase)
            return strtoupper($word);
        }

        // If more than 2 words: extract only the initials
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return $initials;
    }

    // Retourne le suffix de l'ID d'une école le suffix est entre A et Z / A1 et Z1 etc.
    protected function getClientSuffix(int $index): string
    {
        $letterIndex = ($index - 1) % 26;
        $round = intval(($index - 1) / 26);
        $letter = chr(65 + $letterIndex);

        return $round > 0 ? $letter . $round : $letter;
    }
}
