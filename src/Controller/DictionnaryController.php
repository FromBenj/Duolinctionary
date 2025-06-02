<?php

namespace App\Controller;

use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Attribute\Route;

final class DictionnaryController extends AbstractController
{
    #[Route('/dictionnary', name: 'app_dictionnary')]
    public function index(): Response
    {
        $rawHtml = $this->getParameter('kernel.project_dir') . '/assets/ben_dictionnary.html';
        $htmlContent = file_get_contents($rawHtml);
        $htmlCleaned = preg_replace('/<svg(.*?)\/svg>/s', '', $htmlContent);

        $crawler = new Crawler($htmlCleaned);
        $wordsCrawler = $crawler
            ->filter('h3');
        $wordsCount = $wordsCrawler->count();
        $dictionnaryArray = [];
        for ($i = 0; $i < $wordsCount; $i++) {
            $word = $wordsCrawler->eq($i)->text();
            $translation = $wordsCrawler->eq($i)->nextAll()->first()->text();
            $dictionnaryArray[] = [
                'word' => $word,
                'translation' => $translation,
            ];
        }

        $document = new PhpWord();

        $section = $document->addSection();
        $section->addText('My DuoLingo Dictionnary',
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

        foreach ($dictionnaryArray as $dictionnary) {
            $textrun = $section->addTextRun();
            $textrun->addText($dictionnary['word'] . ': ',
                [
                    'bold' => true,
                    'size' => 12,
                ]
            );
            $textrun->addText($dictionnary['translation'],
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
