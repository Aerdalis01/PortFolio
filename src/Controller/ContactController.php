<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    private TransportInterface $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    #[Route('/contact', name: 'contact', methods: ['POST'])]
    public function contact(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $message = $data['message'] ?? null;

        // Validation basique
        if (!$email || !$message) {
            return $this->json(['error' => 'Tous les champs sont obligatoires.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Préparer et envoyer l'e-mail
            $emailMessage = (new Email())
                ->from('caillonaudric@hotmail.fr') // Adresse vérifiée dans Azure
                ->to('caillonaudric@hotmail.fr')
                ->replyTo($email)
                ->subject('Nouveau message de contact')
                ->text($message);

            // Utilisation du transport pour envoyer le mail
            $mailer = new Mailer($this->transport);
            $mailer->send($emailMessage);

            return $this->json(['success' => 'Votre message a été envoyé avec succès !'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Une erreur est survenue lors de l\'envoi : '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
