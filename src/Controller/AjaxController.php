<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Comment;
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

            $fileObject = new UploadedFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], $_FILES['file']['type'], $_FILES['file']['size'], $_FILES['file']['error']);
            $fileObject->move($this->getParameter('kernel.project_dir') . "/public/albums/" . $album->getDir(), $fileObject->getClientOriginalName());


            $photo->setImage($album->getDir() . "/" . $fileObject->getClientOriginalName());
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


