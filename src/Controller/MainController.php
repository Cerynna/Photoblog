<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Background;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    /**
     * @Route("/", name="home")
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


    public function parallax()
    {
        $backgrounds = $this->getDoctrine()->getRepository(Background::class)->findAll();
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
