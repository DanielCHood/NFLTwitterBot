<?php

namespace App\Entity;

use App\Repository\PlayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlayRepository::class)
 */
class Play
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
    private $espnId;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity=Game::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\Column(type="integer")
     */
    private $yards;

    /**
     * @ORM\Column(type="integer")
     */
    private $down;

    /**
     * @ORM\Column(type="boolean")
     */
    private $turnover;

    /**
     * @ORM\Column(type="boolean")
     */
    private $scoringPlay;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $playType;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\OneToMany(targetEntity=Tweet::class, mappedBy="play_id", orphanRemoval=true)
     */
    private $tweets;






    public function __construct()
    {
        $this->tweets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEspnId(): ?int
    {
        return $this->espnId;
    }

    public function setEspnId(int $espnId): self
    {
        $this->espnId = $espnId;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getYards(): ?int
    {
        return $this->yards;
    }

    public function setYards(int $yards): self
    {
        $this->yards = $yards;

        return $this;
    }

    public function getDown(): ?int
    {
        return $this->down;
    }

    public function setDown(int $down): self
    {
        $this->down = $down;

        return $this;
    }

    public function getTurnover(): ?bool
    {
        return $this->turnover;
    }

    public function setTurnover(bool $turnover): self
    {
        $this->turnover = $turnover;

        return $this;
    }

    public function getScoringPlay(): ?bool
    {
        return $this->scoringPlay;
    }

    public function setScoringPlay(bool $scoringPlay): self
    {
        $this->scoringPlay = $scoringPlay;

        return $this;
    }

    public function getPlayType(): ?string
    {
        return $this->playType;
    }

    public function setPlayType(string $playType): self
    {
        $this->playType = $playType;

        return $this;
    }

    /**
     * @return Collection|Tweet[]
     */
    public function getTweets(): Collection
    {
        return $this->tweets;
    }

    public function addTweet(Tweet $tweet): self
    {
        if (!$this->tweets->contains($tweet)) {
            $this->tweets[] = $tweet;
            $tweet->setPlayId($this);
        }

        return $this;
    }

    public function removeTweet(Tweet $tweet): self
    {
        if ($this->tweets->removeElement($tweet)) {
            // set the owning side to null (unless already changed)
            if ($tweet->getPlayId() === $this) {
                $tweet->setPlayId(null);
            }
        }

        return $this;
    }
}
