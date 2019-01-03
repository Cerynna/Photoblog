<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Comment;
use App\Entity\Config;
use App\Entity\Newsletter;
use App\Entity\Photo;
use App\Entity\SousAlbum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends Controller
{

    /**
     * @param Request $request
     * @Route("/ajax/changePositionPhoto",name="changePositionPhoto")
     * @return JsonResponse
     */
    public function changePositionPhoto(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $idPhoto = $idAlbum = $request->get('photo');
            $action = $idAlbum = $request->get('action');
            $type = $idAlbum = $request->get('type');

            $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);
            $photoChange = null;

            $operator = +1;
            if (strtolower($action) === "down") {
                $operator = -1;
            }
            if (strtolower($type) === "album") {
                $photoChange = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['album' => $photo->getAlbum(), 'position' => $photo->getPosition() + $operator]);
            }
            if (strtolower($type) === "sousalbum") {
                $photoChange = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['sousAlbum' => $photo->getSousAlbum(), 'position' => $photo->getPosition() + $operator]);
            }

            if (!is_null($photoChange)) {

                $newPositionPhotoChange = $photo->getPosition();
                $newPositionPhoto = $photoChange->getPosition();

                $photoChange->setPosition($newPositionPhotoChange);
                $photo->setPosition($newPositionPhoto);

                $this->getDoctrine()->getManager()->flush();


            }
            $this->get('session')->set('actionAdmin', "listPhotos");

            return new JsonResponse(['from' => $photo->getId(), 'to' => $photoChange->getId(), 'action' => $action]);
        }
    }


    /**
     * @param Request $request
     * @Route("/ajax/getAlbumPhoto",name="getAlbumPhoto")
     * @return JsonResponse
     */
    public function getAlbumPhoto(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $idAlbum = $request->get('idAlbum');

            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => $idAlbum]);


            $result = [];

            if ($album->getPhotos() !== null) {
                /** @var Photo $photo */
                foreach ($album->getPhotos() as $photo) {
                    $result[] = [
                        'name' => $photo->getImage(),
                        'id' => $photo->getId()
                    ];
                }
            }
            if ($album->getSousAlbums() !== null) {
                /** @var SousAlbum $sousAlbum */
                foreach ($album->getSousAlbums() as $sousAlbum) {
                    foreach ($sousAlbum->getPhotos() as $photo) {
                        $result[] = [
                            'name' => $photo->getImage(),
                            'id' => $photo->getId()
                        ];
                    }
                }
            }


            return new JsonResponse($result);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/transferePhoto",name="transferePhoto")
     * @return JsonResponse
     */
    public function transferePhoto(Request $request)
    {
        if ($request->isXmlHttpRequest()) {


            $select = $request->get('select');
            $idPhoto = $request->get('photo');

            list($type, $id) = explode('_', $select);
            /** @var SousAlbum $sousAlbumFrom */
            $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);
            $photo->setAlbum(null);
            $photo->setSousAlbum(null);


            if (strtoupper($type) === "A") {
                /** @var Album $album */
                $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["id" => $id]);
                $album->addPhoto($photo);
                $photo->setAlbum($album);
            }
            if (strtoupper($type) === "SA") {
                /** @var SousAlbum $sousAlbum */
                $sousAlbum = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $id]);
                $sousAlbum->addPhoto($photo);
                $photo->setSousAlbum($sousAlbum);
            }
            $this->getDoctrine()->getManager()->flush();
            $this->get('session')->set('actionAdmin', "listPhotos");
            return new JsonResponse('');
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/transferePhotos",name="transferePhotos")
     * @return JsonResponse
     */
    public function transferePhotos(Request $request)
    {
        if ($request->isXmlHttpRequest()) {


            $select = $request->get('select');
            $idFrom = $request->get('idFrom');

            list($type, $idTo) = explode('_', $select);
            /** @var SousAlbum $sousAlbumFrom */
            $sousAlbumFrom = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $idFrom]);

            if (strtoupper($type) === "A") {
                /** @var Album $album */
                $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["id" => $idTo]);
                /** @var Photo $photo */
                foreach ($sousAlbumFrom->getPhotos() as $photo) {
                    $sousAlbumFrom->removePhoto($photo);
                    $album->addPhoto($photo);
                }
            }
            if (strtoupper($type) === "SA") {
                /** @var SousAlbum $sousAlbumTo */
                $sousAlbumTo = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $idTo]);
                foreach ($sousAlbumFrom->getPhotos() as $photo) {
                    $sousAlbumFrom->removePhoto($photo);
                    $sousAlbumTo->addPhoto($photo);
                }
            }
            $album = $sousAlbumFrom->getAlbum();
            $album->removeSousAlbum($sousAlbumFrom);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse('');
        }
    }


    /**
     * @param Request $request
     * @Route("/ajax/supprSousAlbum",name="supprSousAlbum")
     * @return JsonResponse
     */
    public function supprSousAlbum(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $idSousAlbum = $request->get('idSousAlbum');
            /** @var SousAlbum $sousAlbum */
            $sousAlbum = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $idSousAlbum]);
            if (!is_null($sousAlbum)) {
                if (!is_null($sousAlbum->getPhotos())) {
                    foreach ($sousAlbum->getPhotos() as $photo) {
                        $sousAlbum->removePhoto($photo);
                    }
                }
                $album = $sousAlbum->getAlbum();
                $album->removeSousAlbum($sousAlbum);
                $this->getDoctrine()->getManager()->remove($sousAlbum);
                $this->getDoctrine()->getManager()->flush();
            }


            return new JsonResponse('');
        }
    }

    /**
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return JsonResponse
     * @Route("/ajax/addNewsletter",name="addNewsletter")
     */
    public function addNewsletter(Request $request, \Swift_Mailer $mailer)
    {
        if ($request->isXmlHttpRequest()) {

            $email = $request->get('email');

            $verif = $this->getDoctrine()->getRepository(Newsletter::class)->findOneBy(['email' => $email]);

            $result = [];

            if ($verif === null) {

                $newsletter = new Newsletter();
                $newsletter->setEmail($email);
                $newsletter->setDate(new \DateTime('now'));
                $token = sha1(uniqid(mt_rand(), true));
                $newsletter->setToken($token);
                $newsletter->setStatus(Newsletter::STATUS['WAITING']);
                $this->getDoctrine()->getManager()->persist($newsletter);
                $this->getDoctrine()->getManager()->flush();


                $message = (new \Swift_Message('Confirme ton inscription'))
                    ->setFrom(Newsletter::FROM)
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                        // templates/emails/registration.html.twig
                            'emails/registration.html.twig',
                            array('token' => $token)
                        ),
                        'text/html'
                    )/*
                     * If you also want to include a plaintext version of the message
                    ->addPart(
                        $this->renderView(
                            'emails/registration.txt.twig',
                            array('name' => $name)
                        ),
                        'text/plain'
                    )
                    */
                ;

                $mailer->send($message);
                $result = [
                    "type" => "valid",
                    "message" => "Un Email de confirmation vous a été envoyer."
                ];

            } else {

                $verif->setDate(new \DateTime('now'));
                $token = sha1(uniqid(mt_rand(), true));
                $verif->setToken($token);
                $this->getDoctrine()->getManager()->flush();

                $message = (new \Swift_Message('Confirme ton inscription'))
                    ->setFrom(Newsletter::FROM)
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                        // templates/emails/registration.html.twig
                            'emails/registration.html.twig',
                            array('token' => $token)
                        ),
                        'text/html'
                    )/*
                     * If you also want to include a plaintext version of the message
                    ->addPart(
                        $this->renderView(
                            'emails/registration.txt.twig',
                            array('name' => $name)
                        ),
                        'text/plain'
                    )
                    */
                ;

                $mailer->send($message);

                $result = [
                    "type" => "valid",
                    "message" => "Un Email de confirmation vous a été envoyer."
                ];
            }


            return new JsonResponse($result);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/changeBackground",name="changeBackground")
     * @return JsonResponse
     */
    public function changeBackground(Request $request)
    {
        if ($request->isXmlHttpRequest()) {


            $name = $request->get('name');
            /** @var Config $config */
            $config = $this->getDoctrine()->getRepository(Config::class)->findOneBy(['name' => $name]);

            $idPhoto = $request->get('idPhoto');
            /** @var Photo $photo */
            $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);

            $config->setValue($photo->getId());
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([$photo->getImage(), $config->getName()]);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/changeStatus",name="changeStatus")
     * @return JsonResponse
     */
    public function changeStatus(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $status = intval($request->get('status'));
            $idAlbum = $request->get('idAlbum');

            if ($status === 2) {
                $oldOne = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['status' => 2]);
                if (!is_null($oldOne)) {
                    $oldOne->setStatus(1);
                }
            }
            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => $idAlbum]);
            $album->setStatus($status);

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([$status, $idAlbum]);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/deletePhoto",name="deletePhoto")
     * @return JsonResponse
     */
    public function deletePhoto(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $idPhoto = intval($request->get('idPhoto'));

            $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);

            if (!is_null($photo->getAlbum())) {
                $album = $photo->getAlbum();
                $album->removePhoto($photo);
            }
            if (!is_null($photo->getSousAlbum())) {
                $sousAlbum = $photo->getSousAlbum();
                $sousAlbum->removePhoto($photo);
            }

            $this->getDoctrine()->getManager()->remove($photo);
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse($idPhoto);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/uploadPhoto",name="uploadPhoto")
     * @return JsonResponse
     */
    public function uploadPhoto(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $idSousAlbum = $request->get('idSousAlbum');
            $idAlbum = $request->get('idAlbum');

            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => $idAlbum]);
            $photo = new Photo();
            $newname = sha1(uniqid(mt_rand(), true));

            $fileObject = new UploadedFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], $_FILES['file']['type'], $_FILES['file']['size'], $_FILES['file']['error']);
            $fileObject->move($this->getParameter('kernel.project_dir') . "/public/albums/" . $album->getDir(), $newname . "." . $fileObject->getExtension());


            $photo->setImage($album->getDir() . "/" . $newname . "." . $fileObject->getExtension());
            $photo->setMessages($album->getName());
            $photo->setUpdatedAt(new \DateTime('now'));


            $sousAlbum = $this->getDoctrine()->getRepository(SousAlbum::class)->findOneBy(['id' => $idSousAlbum]);
            $photo->setSousAlbum($sousAlbum);
            if (!is_null($sousAlbum)) {
                $sousAlbum->addPhoto($photo);
                $this->getDoctrine()->getManager()->flush();

            } else {
                $photo->setAlbum($album);
                $album->addPhoto($photo);
                $this->getDoctrine()->getManager()->flush();
            }


            $this->getDoctrine()->getManager()->flush();


            return new JsonResponse([$photo->getImage(), $photo->getId(), $idAlbum, $idSousAlbum]);
        }
    }

    /**
     * @param Request $request
     * @Route("/ajax/changeMiniature",name="changeMiniature")
     * @return JsonResponse
     */
    public function changeMiniature(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $idAlbum = intval($request->get('idAlbum'));
            $idPhoto = intval($request->get('idPhoto'));

            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(['id' => $idAlbum]);
            $photo = $this->getDoctrine()->getRepository(Photo::class)->findOneBy(['id' => $idPhoto]);
            $album->setImageMiniature($photo);

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([$album, $photo]);
        }
    }


    /**
     * @Route("/ajax/comment", name="add_comment")
     */
    public function addComment(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $form = $request->get('data');
            $data = [];
            foreach ($form as $item) {
                $data[$item["name"]] = $item["value"];
            }

            $comment = new Comment();
            $comment->setMessage($data["message"]);
            $comment->setName($data["name"]);
            $comment->setEmail($data["email"]);
            $comment->setDate(new \DateTime("now"));

            /** @var Album $album */
            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["id" => $data["album"]]);
            $comment->setAlbum($album);
            $album->addComment($comment);

            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();


            return new JsonResponse($comment->toArray());
        }
    }

    /**
     * @Route("/ajax/deleteAlbum", name="delete_Album")
     */
    public function deleteAlbum(Request $request)
    {
        /** Verif de Role a faire  */
        if ($request->isXmlHttpRequest()) {

            $idAlbum = $request->get('data');
            /** @var Album $album */
            $album = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["id" => $idAlbum]);

            $dir = "/album/" . $album->getDir();

            $album->setImageMiniature(null);
            /** @var Photo $photo */
            foreach ($album->getPhotos() as $photo) {
                $album->removePhoto($photo);
                $photo->setAlbum(null);
                $this->getDoctrine()->getManager()->remove($photo);
                $this->getDoctrine()->getManager()->flush();
            }
            /** @var SousAlbum $sousAlbum */
            foreach ($album->getSousAlbums() as $sousAlbum) {
                /** @var Photo $photo */
                foreach ($sousAlbum->getPhotos() as $photo) {
                    $sousAlbum->removePhoto($photo);
                    $photo->setSousAlbum(null);
                    $this->getDoctrine()->getManager()->remove($photo);
                    $this->getDoctrine()->getManager()->flush();
                }
                $album->removeSousAlbum($sousAlbum);
                $this->getDoctrine()->getManager()->remove($sousAlbum);
                $this->getDoctrine()->getManager()->flush();
            }
            $album->setStatus(0);
            $this->getDoctrine()->getManager()->remove($album);
            $this->getDoctrine()->getManager()->flush();


            $fileSysteme = new Filesystem();

            $fileSysteme->remove($dir);

            return new JsonResponse($idAlbum);
        }
    }

    /**
     * @Route("/ajax/positionAlbum", name="position_Album")
     */
    public function positionAlbum(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $position = $request->get('position');
            $operator = $request->get('operator');

            if (intval($operator) === 1 or intval($operator) === -1) {

                /** @var Album $albumFrom */
                $albumFrom = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["position" => $position]);
                /** @var Album $albumTo */
                $albumTo = $this->getDoctrine()->getRepository(Album::class)->findOneBy(["position" => $position + $operator]);

                $newPosFrom = $albumTo->getPosition();
                $newPosTo = $albumFrom->getPosition();

                $albumFrom->setPosition($newPosFrom);
                $albumTo->setPosition($newPosTo);
                $this->getDoctrine()->getManager()->flush();

                $retour = [
                    $albumFrom->getId() => $albumFrom->getPosition(),
                    $albumTo->getId() => $albumTo->getPosition(),

                ];

                return new JsonResponse($retour);
            }

            if (intval($operator) > 1 or intval($operator) < 1) {
                $retour = [];
                if ($operator > 0) {
                    $albums = $this->getDoctrine()->getRepository(Album::class)->findInterval($position, abs($position + $operator), ">=", "<=");
                } else {
                    $albums = $this->getDoctrine()->getRepository(Album::class)->findInterval($position, abs($position + $operator), "<=", ">=");
                }
                /** @var Album $albumTo */
                $albumTo = array_pop($albums);
                /**
                 * @var int $key
                 * @var Album $album
                 */
                foreach ($albums as $key => $album) {
                    if ($operator > 0) {
                        $album->setPosition($position + $key);
                    } else {
                        $album->setPosition($position - $key);
                    }
                    $this->getDoctrine()->getManager()->flush();
                    $retour[$album->getId()] = $album->getPosition();
                }
                $albumTo->setPosition($position + $operator);
                $retour[$albumTo->getId()] = $albumTo->getPosition();
                $this->getDoctrine()->getManager()->flush();
                return new JsonResponse($retour);
            }

        }

    }
}


