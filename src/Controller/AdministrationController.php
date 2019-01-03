<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\Config;
use App\Entity\Newsletter;
use App\Entity\Photo;
use App\Entity\SousAlbum;
use App\Entity\UploadFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Unsplash\OAuth2\Client\Provider\Unsplash;
use ZipArchive;

/**
 * @Route("/administration", name="administration")
 */
class AdministrationController extends Controller
{

    /**
     * @Route("/test", name="test")
     */
    public function testMail()
    {

        $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => 31]);


        return $this->render('emails/newAlbum.html.twig', [
            'album' => $album
        ]);
    }

    /**
     * @Route("/", name="homeAdmin")
     */
    public function index()
    {

        $lastComment = $this->getDoctrine()->getRepository(Comment::class)->lastComment(5);

        $statPhotos = $this->getDoctrine()->getRepository(Photo::class)->statPhotos();
        $statAlbums = $this->getDoctrine()->getRepository(Album::class)->statAlbums();

        dump($statPhotos);
        dump($statAlbums);
        $url = "https://api.unsplash.com/photos/random";

        $provider = new \Unsplash\OAuth2\Client\Provider\Unsplash([
            'clientId' => 'c75bae61c0b54165c93a067d0067c2640906a792452ff1616b7ed2d5f9c5c934',
            'clientSecret' => '82f79bb3d01f8f093806c10d25a1222027895dcf2bc7536afed08c82e05ac34f',
            'redirectUri' => '127.0.0.1:8000',
        ]);
        /*if (!isset($_GET['code'])) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: ' . $authUrl);
            exit;
        }

        if (isset($_GET['code']) && !isset($_SESSION['token'])) {
            try {
                // Try to get an access token (using the authorization code grant)
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);

            } catch (Exception $e) {
                print($e->getMessage());
                exit;
            }

            // Use this to interact with an API on the users behalf
            $_SESSION['token'] = $token->getToken();
        }

        if (isset($_SESSION['token'])) {
            $user = $provider->getResourceOwner($_SESSION['token']);
            printf('Hello %s!', $user->getName());
            return;
        }*/

        return $this->render('administration/index.html.twig', [
            'title' => 'Index Admin',
            'lastComment' => $lastComment,
            'statPhotos' => $statPhotos,
            'statAlbums' => $statAlbums,
        ]);
    }

    /**
     * @Route("/config", name="config")
     */
    public function config()
    {
        $backgrounds = $this->getDoctrine()->getRepository(Config::class)->getParallax();
        $config = $this->getDoctrine()->getRepository(Config::class)->findAll();
        $albums = $this->getDoctrine()->getRepository(Album::class)->findAll();


        return $this->render('administration/config.html.twig', [
            'title' => 'Config Admin',
            'config' => $config,
            'backgrounds' => $backgrounds,
            'albums' => $albums,
        ]);
    }

    /**
     * @Route("/editAlbum/{id}", name="editAlbum")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAlbum($id, Request $request)
    {
        /** @var Album $album */
        $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => $id]);
        if ($request->getMethod() === "POST") {
            $album->setName($_POST['title']);
            $album->setMessage($_POST['messageAlbum']);
            $album->setDate(new \DateTime($_POST['dateTime']));
            $album->setStatus($_POST['status']);
            if (isset($_POST["sousAlbumId"]) and !empty($_POST["sousAlbumId"])) {
                foreach ($_POST['sousAlbumId'] as $position => $id) {
                    if (!is_null($id) AND !empty($id)) {
                        /** @var SousAlbum $sousAlbum */
                        $sousAlbum = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $id]);
                        if (!is_null($sousAlbum)) {
                            $sousAlbum->setPosition($position);
                            $sousAlbum->setName($_POST['sousAlbumName'][$position]);
                            $sousAlbum->setMessage($_POST['sousAlbumMessage'][$position]);
                            $this->getDoctrine()->getManager()->flush();
                        }
                    } else {
                        $sousAlbum = new SousAlbum();
                        $sousAlbum->setPosition($position);
                        $sousAlbum->setName($_POST['sousAlbumName'][$position]);
                        $sousAlbum->setMessage($_POST['sousAlbumMessage'][$position]);
                        $album->addSousAlbum($sousAlbum);
                        $this->getDoctrine()->getManager()->persist($sousAlbum);
                        $this->getDoctrine()->getManager()->flush();
                    }


                }
            }
            if (isset($_POST["photoPosition"]) and !empty($_POST["photoPosition"])) {
                foreach ($_POST['photoPosition'] as $idPhoto => $position) {
                    $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);
                    $photo->setPosition($position);
                    $this->getDoctrine()->getManager()->flush();
                }
            }
            $this->getDoctrine()->getManager()->flush();
            if (!is_null($this->get('session')->get('actionAdmin'))) {
                $this->get('session')->set('actionAdmin', null);
            }
            return $this->redirectToRoute('administrationeditAlbum', ['id' => $album->getId()]);

        }
        return $this->render('administration/editAlbum.html.twig', [
            'title' => 'Index Admin',
            'album' => $album
        ]);
    }

    /**
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verifNewletters(\Swift_Mailer $mailer)
    {
        $newletters = $this->getDoctrine()->getRepository(Newsletter::class)->findAll();
        $albums = $this->getDoctrine()->getRepository(Album::class)->findBy(['sendNewletter' => 1]);
        /** @var Album $album */
        foreach ($albums as $album) {
            /** @var Newsletter $newsletter */
            foreach ($newletters as $newsletter) {

                $message = (new \Swift_Message('Nouvelle Album **' . $album->getName() . '**'))
                    ->setFrom(Newsletter::FROM)
                    ->setTo($newsletter->getEmail())
                    ->setBody(
                        $this->renderView(
                        // templates/emails/registration.html.twig
                            'emails/newAlbum.html.twig',
                            array('album' => $album)
                        ),
                        'text/html'
                    );

                $mailer->send($message);
            }


            $album->setSendNewletter(2);
            $this->getDoctrine()->getManager()->persist($album);
            $this->getDoctrine()->getManager()->flush();

        }


        return $this->render('void.html.twig');


    }


    /**
     * @Route("/addAlbum", name="addAlbum")
     */
    public function addAlbum(Request $request)
    {
        $albumPosition = $this->getDoctrine()->getRepository(Album::class)->getCurrentPosition();

        if ($request->getMethod() === "POST") {

            $arrFile = [];
            if (isset($_POST["arrFiles"]) and !empty($_POST["arrFiles"])) {
                $arrFilesJSON = json_decode($_POST["arrFiles"]);
                foreach ($arrFilesJSON as $file) {
                    $explodeFile = explode('/', $file);
                    if (count($explodeFile) === 2) {
                        $arrFile[array_pop($explodeFile)] = null;
                    }
                    if (count($explodeFile) === 3) {
                        $arrFile[array_pop($explodeFile)] = $explodeFile[1];
                    }
                }
            }

            $albumDir = sha1(uniqid(mt_rand(), true));


            $album = new Album();

            $album->setName($_POST["title"]);
            if ($_POST["status"] !== 3) {
                $album->setPosition($albumPosition);
            }
            $album->setDate(new \DateTime($_POST["dateTime"]));
            $album->setMessage($_POST["messageAlbum"]);
            $album->setStatus($_POST["status"]);
            $album->setDir($albumDir);
            $this->getDoctrine()->getManager()->persist($album);
            $this->getDoctrine()->getManager()->flush();
            $this->getDoctrine()->getManager()->refresh($album);

            $arrSousAlbums = [];


            if (isset($_POST['sousAlbumDir']) and !empty($_POST['sousAlbumDir'])) {
                foreach ($_POST['sousAlbumDir'] as $key => $dirSousAlbum) {
                    $newSousAlbum = new SousAlbum();
                    $newSousAlbum->setName($_POST['sousAlbumName'][$key]);
                    $newSousAlbum->setPosition($key);
                    $newSousAlbum->setAlbum($album);
                    $newSousAlbum->setMessage($_POST['sousAlbumMessage'][$key]);
                    $album->addSousAlbum($newSousAlbum);
                    $this->getDoctrine()->getManager()->persist($newSousAlbum);
                    $arrSousAlbums[$dirSousAlbum] = $newSousAlbum;
                }
            }


            foreach ($_FILES['file']['name'] as $key => $name) {
                if (strpos($_FILES['file']['type'][$key], 'image') !== false) {

                    $fileObject = new UploadedFile($_FILES['file']['tmp_name'][$key], $_FILES['file']['name'][$key], $_FILES['file']['type'][$key], $_FILES['file']['size'][$key], $_FILES['file']['error'][$key]);
                    $fileObject->move($this->getParameter('kernel.project_dir') . "/public/albums/" . $albumDir, $fileObject->getClientOriginalName());
                    $photo = new Photo();
                    $photo->setImage($albumDir . "/" . $fileObject->getClientOriginalName());
                    $photo->setUpdatedAt(new \DateTime('now'));
                    $photo->setMessages($album->getName());
                    $photo->setPosition($key);

                    if ($key == $_POST['miniature'][0]) {
                        $album->setImageMiniature($photo);
                    }

                    if (is_null($arrFile[$fileObject->getClientOriginalName()])) {
                        $album->addPhoto($photo);
                        $photo->setAlbum($album);
                    } else {
                        $arrSousAlbums[$arrFile[$fileObject->getClientOriginalName()]]->addPhoto($photo);
                        $photo->setSousAlbum($arrSousAlbums[$arrFile[$fileObject->getClientOriginalName()]]);
                    }
                    $this->getDoctrine()->getManager()->persist($photo);
                }
            }
            $this->getDoctrine()->getManager()->persist($album);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirect('album');
        }


        return $this->render('administration/newAlbum.html.twig', [
            'title' => 'NewAlbum Admin',
        ]);
    }

    /**
     * @Route("/album", name="Admin_Albums")
     */
    public function AdminAlbums()
    {
        /** @var array $albums */
        $albums = $this->getDoctrine()->getRepository(Album::class)->adminAlbumIndex();

        return $this->render('administration/listAlbum.html.twig', [
            'title' => 'Admin Album',
            'albumsOnLine' => $albums[0],
            'albumsWaiting' => $albums[1],
            'albumsGhost' => $albums[2],
            'countAlbums' => count($albums),
        ]);
    }

    /**
     * @Route("/addAlbumZip", name="addAlbumZip")
     */
    public function addAlbumZip(Request $request)
    {
        $uploadFile = new UploadFile();
        $form = $this->createFormBuilder($uploadFile)
            ->add('file', FileType::class, [
                'multiple' => true,
                'attr' => [
                    'multiple' => 'multiple'
                ]
            ])
            ->getForm();

        $dirParser = 'parser/';
        $waitingAlbum = scandir($dirParser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            if (is_array($file)) {
                foreach ($file as $oneFile) {
                    $oneFile->move($this->getParameter('kernel.project_dir') . "/public/" . $dirParser, $oneFile->getClientOriginalName());
                }
            }


            return $this->redirect('parser');
        }

        return $this->render('administration/addAlbumZip.html.twig', [
            'controller_name' => 'AdministrationController',
            'form' => $form->createView(),
            'waitingAlbum' => $waitingAlbum
        ]);
    }

    /**
     * @Route("/parser", name="parser")
     */
    public function parser(Request $request)
    {
        $dirParser = 'parser/';
        $files = scandir($dirParser);
        $listDir = [];
        $fileSystem = new Filesystem();
        $albumPosition = $this->getDoctrine()->getRepository(Album::class)->getCurrentPosition();
        foreach ($files as $file) {
            if (!is_dir($file)) {
                $fileName = $dirParser . $file;
                $zip = new ZipArchive;
                $res = $zip->open($fileName);
                $albumDir = sha1(uniqid(mt_rand(), true)) . "/";
                if ($res === TRUE) {
                    $zip->extractTo('albums/');
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if (is_dir("albums/" . $zip->getNameIndex($i))) {

                            $explodeDir = array_filter(explode('/', $zip->getNameIndex($i)));
                            if (count($explodeDir) == 1) {
                                $listDir[] = $albumDir;
                            } else {
                                $newName = sha1(uniqid(mt_rand(), true));
                                /*$fileSystem->rename('albums/' . $albumDir . '/' . $explodeDir[1], 'albums/' . $albumDir . "/" . $newName, true);*/
                                $listDir[] = $albumDir . $explodeDir[1];
                            }


                        }
                    }
                    $fileSystem->rename('albums/' . $zip->getNameIndex(0), 'albums/' . $albumDir, true);
                    $zip->close();
                    $fileSystem->remove($fileName);
                }

            }
        }

        $InfoAlbum = [];
        $keySousAlbum = 0;
        foreach ($listDir as $dir) {


            $explodeDir = array_filter(explode('/', $dir));

            if (count($explodeDir) === 1) {
                $InfoAlbum = [];
                if (is_file('albums/' . $dir . 'info.txt')) {
                    $infos = file('albums/' . $dir . 'info.txt');
                    $keySousAlbum = 0;
                    foreach ($infos as $info) {
                        $info = trim(preg_replace('/\s\s+/', ' ', $info));
                        if (preg_match("/##/i", $info) AND strpos($info, "###") === false) {
                            $InfoAlbum['album']['name'] = utf8_encode(str_replace('##', '', $info));
                        } elseif (preg_match("/--/i", $info) AND strpos($info, "---") === false) {
                            $InfoAlbum['album']['message'] = utf8_encode(str_replace('--', '', $info));
                        } elseif (preg_match("/###/i", $info)) {
                            $keySousAlbum++;
                            $InfoAlbum['sousAlbum'][$keySousAlbum]['name'] = utf8_encode(str_replace('###', '', $info));

                        } elseif (preg_match("/---/i", $info)) {
                            $InfoAlbum['sousAlbum'][$keySousAlbum]['message'] = utf8_encode(str_replace('---', '', $info));
                        } else {
                            if ($keySousAlbum === 0) {
                                $InfoAlbum['album']['message'] = $InfoAlbum['album']['message'] . utf8_encode($info);
                            } else {
                                if (isset ($InfoAlbum['sousAlbum'][$keySousAlbum]['message'])) {
                                    $InfoAlbum['sousAlbum'][$keySousAlbum]['message'] = $InfoAlbum['sousAlbum'][$keySousAlbum]['message'] . utf8_encode($info);
                                }
                            }
                        }
                    }
                }
                $album = new Album();
                $album->setDate(new \DateTime("now"));
                $album->setPosition($albumPosition);
                if (isset($InfoAlbum['album'])) {
                    $album->setName($InfoAlbum['album']['name']);
                } else {
                    $album->setName("Erreur Name Album");
                }

                if (isset ($InfoAlbum['album']['message'])) {
                    $album->setMessage($InfoAlbum['album']['message']);
                    $album->setMessage(str_replace('', "''", $album->getMessage()));
                }


                $album->setStatus(1);
                $album->setDir(str_replace('/', '', $dir));

                $this->AddPhotos('albums/' . $dir . "/", $album);

                $this->getDoctrine()->getManager()->persist($album);
            } else {

                $explodeDir = array_filter(explode('/', $dir));
                unset($explodeDir[0]);
                $dirSousAlbum = implode("", $explodeDir);
                explode('-', $dirSousAlbum);
                list($position) = explode("-", $dirSousAlbum);
                if (!is_numeric($position)) {
                    $position = 0;
                }
                $position = intval($position);

                $sousAlbum = new SousAlbum();
                $sousAlbum->setName($InfoAlbum['sousAlbum'][$position]['name']);
                if (isset ($InfoAlbum['sousAlbum'][$position]['message'])) {
                    $sousAlbum->setMessage($InfoAlbum['sousAlbum'][$position]['message']);
                    $sousAlbum->setMessage(str_replace('', "''", $sousAlbum->getMessage()));
                }
                $sousAlbum->setPosition($position);
                $sousAlbum->setAlbum($album);
                $album->addSousAlbum($sousAlbum);

                $this->AddPhotos('albums/' . $dir . "/", null, $sousAlbum);

                $this->getDoctrine()->getManager()->persist($album);
                $this->getDoctrine()->getManager()->persist($sousAlbum);

            }

        }
        $this->getDoctrine()->getManager()->flush();

        return $this->render('administration/parser.html.twig', [
            'listDir' => $listDir,
        ]);
    }

    public function AddPhotos($dir, Album $album = null, SousAlbum $sousAlbum = null)
    {
        $files = scandir($dir);
        $photoNumber = 0;
        $fileSystem = new Filesystem();
        foreach ($files as $file) {

            if ($file !== "." AND $file !== "..") {
                if (!is_dir($dir . $file) AND preg_match("/image/i", mime_content_type($dir . $file))) {

                    list($position) = explode('-', $file);

                    if (!is_numeric($position)) {
                        $position = 0;
                    }
                    $position = intval($position);


                    $dirPhoto = str_replace('albums/', '', $dir);

                    $photo = new Photo();
                    $photoNumber++;

                    $photo->setPosition($position);
                    $photo->setUpdatedAt(new \DateTime('now'));

                    list($name, $ext) = explode('.', $file);
                    $newName = sha1(uniqid(mt_rand(), true)) . '.' . $ext;
                    $fileSystem->rename($dir . $file, $dir . $newName, true);
                    $photo->setImage($dirPhoto . $newName);
                    if (!is_null($album)) {
                        $photo->setAlbum($album);
                        $photo->setMessages($album->getName());
                        if ($photoNumber === 1) {
                            $album->setImageMiniature($photo);
                        }
                    }
                    if (!is_null($sousAlbum)) {
                        $photo->setSousAlbum($sousAlbum);
                        $photo->setMessages($sousAlbum->getName());
                    }
                    $this->getDoctrine()->getManager()->persist($photo);
                }
            }


        }


    }


}
