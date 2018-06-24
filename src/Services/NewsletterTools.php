<?php

namespace App\Service;

use App\Entity\Album;
use App\Entity\Newsletter;
use Doctrine\ORM\EntityManager;

class NewsletterTools
{

    private $em;

    private $mailer;

    public function __construct(EntityManager $em )
    {
        $this->em = $em;
        $this->mailer = new \Swift_Mailer('gmail');
    }

    public function sendNewAlbum(Album $album)
    {
        $newsletters = $this->em->getRepository(Newsletter::class)->findAll();
        /** @var Newsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $email = $newsletter->getEmail();
            $message = (new \Swift_Message('Nouvelle Album ' . $album->getName()))
                ->setFrom(Newsletter::FROM)
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'emails/registration.html.twig',
                        array('token' => "LOL")
                    ),
                    'text/html'
                );

            $this->mailer->send($message);


        }


    }
}