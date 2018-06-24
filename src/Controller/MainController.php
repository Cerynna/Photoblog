<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Config;
use App\Entity\Newsletter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    /**
     * @Route("/", name="home")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $albums = $this->getDoctrine()->getRepository(Album::class)->albumIndex(16);

        $albumUne = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['status' => 2]);
        return $this->render('default/index.html.twig', [
            'albums' => $albums,
            'albumUne' => $albumUne,
        ]);
    }

    /**
     * @Route("/register/{token}", name="validToken")
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register($token)
    {
        $verif = $this->getDoctrine()->getRepository(Newsletter::class)->findOneBy(['token' => $token]);
        $dateNow = new \DateTime('now');

        if ($verif !== null) {
            if ($verif->getDate()->diff($dateNow)->d <= 3) {
                $verif->setStatus(Newsletter::STATUS['CONFIRM']);
                $verif->setToken(null);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('valid', 'Ton inscription a la newsletter a bien été prise en compte.');
            } else {
                $this->getDoctrine()->getManager()->remove($verif);
                $this->addFlash('error', 'Cet Email de validation a expiré recommence la procédure d\'inscription a la Newsletter.');
            }
            $this->getDoctrine()->getManager()->refresh($verif);
        } else {
            $this->addFlash('error', 'Cet Email de confirmation n\'est pas valid recommence la procédure d\'inscription a la Newsletter si l\'erreur persiste contact moi.');
        }


        return $this->redirectToRoute('home');
        /*return $this->render('/default/clean.html.twig');*/
    }

    public function parallax()
    {
        $backgrounds = $this->getDoctrine()->getRepository(Config::class)->getParallax();

        return $this->render('/default/components/style.html.twig', [
            'backgrounds' => $backgrounds
        ]);
    }




    /**
     * @Route("/album/{dir}", name="album")
     * @param $dir
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function album($dir)
    {

        $album = $this->getDoctrine()->getRepository(Album::class)->getPrevAndNext($dir);
        return $this->render('/default/album.html.twig', [
            "album" => $album,
        ]);
    }

    /**
     * @Route("/albums", name="albums")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function albums()
    {

        $albums = $this->getDoctrine()->getRepository(Album::class)->albumIndex();
        return $this->render('/default/albums.html.twig', [
            "albums" => $albums,
        ]);
    }


}
