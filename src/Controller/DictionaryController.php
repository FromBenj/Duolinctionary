<?php

namespace App\Controller;

use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Attribute\Route;

final class DictionaryController extends AbstractController
{
    #[Route('/dictionary', name: 'app_dictionary')]
    public function index(): Response
    {
        $rawHtml = $this->getParameter('kernel.project_dir') . '/assets/ben_dictionary.html';
        $htmlContent = file_get_contents($rawHtml);
        $htmlCleaned = preg_replace('/<svg(.*?)\/svg>/s', '', $htmlContent);

        $crawler = new Crawler($htmlCleaned);
        $wordsCrawler = $crawler
            ->filter('h3');
        $wordsCount = $wordsCrawler->count();
        $dictionaryArray = [];
        for ($i = 0; $i < $wordsCount; $i++) {
            $word = $wordsCrawler->eq($i)->text();
            $translation = $wordsCrawler->eq($i)->nextAll()->first()->text();
            $dictionaryArray[] = [
                'word' => $word,
                'translation' => $translation,
            ];
        }

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

        // Save to temporary file
        $tempDocFile = tempnam(sys_get_temp_dir(), 'doc');
        $writer = IOFactory::createWriter($document, 'Word2007');
        $writer->save($tempDocFile);

        // Create response
        $response = new Response(file_get_contents($tempDocFile));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->headers->set('Content-Disposition', 'attachment;filename="My_Duolinctionnary.docx"');

        unlink($tempDocFile);

        return $response;
    }
}
