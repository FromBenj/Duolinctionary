<?php

namespace App\Service;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class fileService
{
    static function duolingoWordFile(Array $dictionaryArray): PhpWord
    {
        $document = new PhpWord();

        $section = $document->addSection();
        $section->addText('My DuoLingo Dictionary',
            [
                'size' => 20,
                'bold' => true,
                'underline' => 'thick',
                'color' => '08946D',
            ],
            [
                'alignment' => Jc::CENTER,
            ],
        );
        $section->addTextBreak();
        $section->addText(date('d/m/Y'),
            [
                'size' => 14,
                'italic' => true,
                'bold' => true,
                'color' => '0A6883',
            ],
            [
                'alignment' => Jc::CENTER,
            ],
        );
        $section->addTextBreak(4);

        foreach ($dictionaryArray as $dictionary) {
            $textrun = $section->addTextRun();
            $textrun->addText($dictionary['word'] . ': ',
                [
                    'bold' => true,
                    'size' => 12,
                ]
            );
            $textrun->addText($dictionary['translation'],
                [
                    'italic' => true,
                    'size' => 12,
                ]
            );
            $section->addTextBreak();
        }
        return $document;
    }
}
