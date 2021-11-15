<?php

namespace App\Entity;

use App\Repository\TweetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TweetRepository::class)
 */
class Tweet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Play::class, inversedBy="tweets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $play_id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $twitter_id;

    /**
     * @ORM\ManyToOne(targetEntity=Tweet::class, inversedBy="replies")
     */
    private $in_reply_to;

    /**
     * @ORM\OneToMany(targetEntity=Tweet::class, mappedBy="in_reply_to")
     */
    private $replies;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    public function __construct()
    {
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayId(): ?Play
    {
        return $this->play_id;
    }

    public function setPlayId(?Play $play_id): self
    {
        $this->play_id = $play_id;

        return $this;
    }

    public function getTwitterId(): ?string
    {
        return $this->twitter_id;
    }

    public function setTwitterId(string $twitter_id): self
    {
        $this->twitter_id = $twitter_id;

        return $this;
    }

    public function getInReplyTo(): ?self
    {
        return $this->in_reply_to;
    }

    public function setInReplyTo(?self $in_reply_to): self
    {
        $this->in_reply_to = $in_reply_to;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setInReplyTo($this);
        }

        return $this;
    }

    public function removeReply(self $reply): self
    {
        if ($this->replies->removeElement($reply)) {
            // set the owning side to null (unless already changed)
            if ($reply->getInReplyTo() === $this) {
                $reply->setInReplyTo(null);
            }
        }

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }
}
