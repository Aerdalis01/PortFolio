<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DownloadController extends AbstractController
{
    #[Route('/download-cv', name: 'download_cv')]
    public function downloadCv(): Response
    {
        $filePath = $this->getParameter('kernel.project_dir').'/public/assets/uploads/cv-audric-caillon.pdf';

        return $this->file($filePath, 'cv-audric-caillon.pdf');
    }
}
