<?php

namespace App\Entity;

use App\Repository\FollowQueueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass=FollowQueueRepository::class)
 */
class FollowQueue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $twitterId;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $dateQueued;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $followedBack;

    /**
     * @ORM\Column(type="array")
     */
    private $criteria = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DateFollowed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTwitterId(): ?string
    {
        return $this->twitterId;
    }

    public function setTwitterId(string $twitterId): self
    {
        $this->twitterId = $twitterId;

        return $this;
    }

    public function getDateQueued(): ?\DateTimeInterface
    {
        return $this->dateQueued;
    }

    public function setDateQueued(\DateTimeInterface $dateQueued): self
    {
        $this->dateQueued = $dateQueued;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersistSetDateQueued()
    {
        $this->dateQueued = new \DateTime();
    }

    public function getFollowedBack(): ?bool
    {
        return $this->followedBack;
    }

    public function setFollowedBack(?bool $followedBack): self
    {
        $this->followedBack = $followedBack;

        return $this;
    }

    public function getCriteria(): ?array
    {
        return $this->criteria;
    }

    public function setCriteria(array $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getDateFollowed(): ?\DateTimeInterface
    {
        return $this->DateFollowed;
    }

    public function setDateFollowed(?\DateTimeInterface $DateFollowed): self
    {
        $this->DateFollowed = $DateFollowed;

        return $this;
    }
}
