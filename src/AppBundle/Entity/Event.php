<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @Assert\Regex("/[A-Za-z0-9-]+/")
     * @ORM\Column(name="slug", type="string", length=255, unique=true, nullable=false)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Image")
     */
    private $carousel;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Image")
     */
    private $image;

    /**
     * @var array
     *
     * @ORM\Column(name="attachments", type="array")
     */
    private $attachments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time", type="datetime")
     * @Assert\Range(min="31 January 2017 09:00", max="2 February 2017 08:00")
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(name="venue", type="string", length=255, nullable=false)
     */
    private $venue;

    /**
     * @var boolean
     *
     * @ORM\Column(name="team_event", type="boolean", nullable=false)
     */
    private $teamEvent;

    /**
     * @var int
     * @Assert\Currency()
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\User")
     */
    private $coordinators;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Event
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Event
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set coverImage
     *
     * @param Image $image
     *
     * @return Event
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get coverImage
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set attachments
     *
     * @param array $attachments
     *
     * @return Event
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set time
     *
     * @param \DateTime $time
     *
     * @return Event
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set venue
     *
     * @param string $venue
     *
     * @return Event
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get venue
     *
     * @return string
     */
    public function getVenue()
    {
        return $this->venue;
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->carousel = new ArrayCollection();
    }

    /**
     * Add carousel
     *
     * @param Image $carousel
     *
     * @return Event
     */
    public function addCarousel(Image $carousel)
    {
        $this->carousel[] = $carousel;

        return $this;
    }

    /**
     * Remove carousel
     *
     * @param Image $carousel
     */
    public function removeCarousel(Image $carousel)
    {
        $this->carousel->removeElement($carousel);
    }

    /**
     * Get carousel
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCarousel()
    {
        return $this->carousel;
    }

    /**
     * Set teamEvent
     *
     * @param boolean $teamEvent
     *
     * @return Event
     */
    public function setTeamEvent($teamEvent)
    {
        $this->teamEvent = $teamEvent;

        return $this;
    }

    /**
     * Get teamEvent
     *
     * @return boolean
     */
    public function isTeamEvent()
    {
        return $this->teamEvent;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Event
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Add coordinator
     *
     * @param User $coordinator
     *
     * @return Event
     */
    public function addCoordinator(User $coordinator)
    {
        $this->coordinators[] = $coordinator;

        return $this;
    }

    /**
     * Remove coordinator
     *
     * @param User $coordinator
     */
    public function removeCoordinator(User $coordinator)
    {
        $this->coordinators->removeElement($coordinator);
    }

    /**
     * Get coordinators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCoordinators()
    {
        return $this->coordinators;
    }
}
