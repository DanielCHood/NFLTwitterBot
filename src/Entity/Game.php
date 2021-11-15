<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $espn_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Venue::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $venue;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $homeTeam;

    /**
     * @ORM\ManyToOne(targetEntity=Team::class, inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $awayTeam;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_analyzed;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $lastPlayId;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $twitter_thread_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $season_year;

    /**
     * @ORM\Column(type="integer")
     */
    private $season_type;

    /**
     * @ORM\Column(type="integer")
     */
    private $season_week;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $last_video_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEspnId(): ?int
    {
        return $this->espn_id;
    }

    public function setEspnId(int $espn_id): self
    {
        $this->espn_id = $espn_id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
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

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    public function getHomeTeam(): ?Team
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?Team $homeTeam): self
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayTeam(): ?Team
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?Team $awayTeam): self
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastAnalyzed(): ?\DateTimeInterface
    {
        return $this->last_analyzed;
    }

    public function setLastAnalyzed(?\DateTimeInterface $last_analyzed): self
    {
        $this->last_analyzed = $last_analyzed;

        return $this;
    }

    public function getLastPlayId(): ?int
    {
        return $this->lastPlayId;
    }

    public function setLastPlayId(?int $lastPlayId): self
    {
        $this->lastPlayId = $lastPlayId;

        return $this;
    }

    public function getTwitterThreadId(): ?string
    {
        return $this->twitter_thread_id;
    }

    public function setTwitterThreadId(?string $twitter_thread_id): self
    {
        $this->twitter_thread_id = $twitter_thread_id;

        return $this;
    }

    public function getSeasonYear(): ?int
    {
        return $this->season_year;
    }

    public function setSeasonYear(int $season_year): self
    {
        $this->season_year = $season_year;

        return $this;
    }

    public function getSeasonType(): ?int
    {
        return $this->season_type;
    }

    public function setSeasonType(int $season_type): self
    {
        $this->season_type = $season_type;

        return $this;
    }

    public function getSeasonWeek(): ?int
    {
        return $this->season_week;
    }

    public function setSeasonWeek(int $season_week): self
    {
        $this->season_week = $season_week;

        return $this;
    }

    public function getLastVideoId(): ?string
    {
        return $this->last_video_id;
    }

    public function setLastVideoId(?string $last_video_id): self
    {
        $this->last_video_id = $last_video_id;

        return $this;
    }

    public function getGameHashTags(): array {
        return [
            '#' . $this->getAwayTeam()->getAbbreviation() . 'vs' . $this->getHomeTeam()->getAbbreviation(),
            '#' . $this->getAwayTeam()->getName(),
            '#' . $this->getHomeTeam()->getName()
        ];
    }
}
