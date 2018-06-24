<?php
/**
 * Created by PhpStorm.
 * User: cerynna
 * Date: 24/06/18
 * Time: 01:17
 */

namespace App\EventListener;


use App\Entity\Album;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class AlbumListener
{


    public function preUpdate(Album $album, PreUpdateEventArgs $event)
    {
        $this->checkForNewletter($event);
    }

    public function prePersist(Album $album,LifecycleEventArgs $event)
    {
        $this->checkForNewletter($event);
    }

    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $event
     */
    public function checkForNewletter($event)
    {

        $entity = $event->getObject();

        $entityManager = $event->getEntityManager();


        if ($entity instanceof Album) {
            /** @var Album $album */
            $album = $event->getObject();

            if ($album->getStatus() === Album::STATUS['PUBLISH'] AND $album->getSendNewletter() === 0) {
                $album->setSendNewletter(1);
                $entityManager->flush();

            }
            return;
        }
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }
}