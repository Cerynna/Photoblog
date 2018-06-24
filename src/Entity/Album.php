<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AlbumRepository")
 * @EntityListeners({"App\EventListener\AlbumListener"})
 *
 */
class Album
{

    const STATUS = [
        'WAITING' => 0,
        'PUBLISH' => 1,
        'ATONE' => 2,
        'GHOST' => 3
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Photo", mappedBy="album", cascade={"all"})
     */
    private $photos;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SousAlbum", mappedBy="album", orphanRemoval=true, cascade={"all"})
     * @OrderBy({"position" = "ASC"})
     */
    private $sousAlbums;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Photo", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $imageMiniature;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dir;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="album", orphanRemoval=true)
     * @OrderBy({"date" = "DESC"})
     */
    private $comments;

    /**
     * @ORM\Column(type="integer")
     */
    private $sendNewletter;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->sousAlbums = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->sendNewletter = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Photo[]
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setAlbum($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->contains($photo)) {
            $this->photos->removeElement($photo);
            // set the owning side to null (unless already changed)
            if ($photo->getAlbum() === $this) {
                $photo->setAlbum(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SousAlbum[]
     */
    public function getSousAlbums(): Collection
    {
        return $this->sousAlbums;
    }

    public function addSousAlbum(SousAlbum $sousAlbum): self
    {
        if (!$this->sousAlbums->contains($sousAlbum)) {
            $this->sousAlbums[] = $sousAlbum;
            $sousAlbum->setAlbum($this);
        }

        return $this;
    }

    public function removeSousAlbum(SousAlbum $sousAlbum): self
    {
        if ($this->sousAlbums->contains($sousAlbum)) {
            $this->sousAlbums->removeElement($sousAlbum);
            // set the owning side to null (unless already changed)
            if ($sousAlbum->getAlbum() === $this) {
                $sousAlbum->setAlbum(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getImageMiniature(): ?Photo
    {
        return $this->imageMiniature;
    }

    public function setImageMiniature(?Photo $imageMiniature): self
    {
        $this->imageMiniature = $imageMiniature;

        return $this;
    }

    public function getDir(): ?string
    {
        return $this->dir;
    }

    public function setDir(?string $dir): self
    {
        $this->dir = $dir;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAlbum($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAlbum() === $this) {
                $comment->setAlbum(null);
            }
        }

        return $this;
    }

    public function getSendNewletter(): ?int
    {
        return $this->sendNewletter;
    }

    public function setSendNewletter(int $sendNewletter = 0): self
    {
        $this->sendNewletter = $sendNewletter;

        return $this;
    }


}
