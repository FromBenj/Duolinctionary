<?php

namespace App\Controller;

use App\Service\DropboxService;
use App\Service\fileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Attribute\Route;

final class DictionaryController extends AbstractController
{
    #[Route('/dictionary', name: 'app_dictionary')]
    public function index(DropboxService $dropboxService): Response
    {
        $rawHtml = $this->getParameter('kernel.project_dir') . '/assets/' . $_ENV['DUOLINGO_FILE'];
        $htmlContent = file_get_contents($rawHtml);

        $dictionaryArray = [];
        $crawler = new Crawler($htmlContent);
        $wordsCrawler = $crawler->filter('ul._4JTMa h3');
        $wordsCount = $wordsCrawler->count();
        for ($i = 0; $i < $wordsCount; $i++) {
            $word = $wordsCrawler->eq($i)->text();
            $translation = $wordsCrawler->eq($i)->nextAll()->first()->text();
            $dictionaryArray[] = [
                'word' => $word,
                'translation' => $translation,
            ];
        }

        $document = fileService::duolingoWordFile($dictionaryArray);

        // Save to temporary file
        $tempDocFile = tempnam(sys_get_temp_dir(), 'doc');
        $writer = IOFactory::createWriter($document, 'Word2007');
        $writer->save($tempDocFile);

        // Create response
        $response = new Response(file_get_contents($tempDocFile));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->headers->set('Content-Disposition', 'attachment;filename="My_Duolinctionnary_' . date('d-m-Y') . '.docx"');

        $file = new UploadedFile(
            $tempDocFile,
            'My_Duolinctionnary_' . date('d-m-Y') . '.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true // Set to true to move the file instead of copying it
        );
        $dropboxService->uploadFile($file);

        unlink($tempDocFile);

        return $response;
    }
}
