<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event
{
    const TYPE_EVENT = 0;
    const TYPE_CELEBRITY_APPEARANCE = 1;
    const TYPE_GUEST_LECTURE = 2;
    const TYPE_WORKSHOP = 3;
    const TYPE_FLAGSHIP = 4;

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
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Image")
     */
    private $carousel;

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
     * @var User
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var Image
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Image")
     */
    private $image;


    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var  int
     * @ORM\Column(name="type", type="smallint", nullable=false)
     */
    private $type = Event::TYPE_EVENT;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category")
     */
    private $category;

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
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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
     * @param float $price
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

    /**
     * Get createdBy
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param string $createdBy
     *
     * @return $this
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;

    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType(int $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /** 
     * Get event type description
     */
    public function getEventType() {
        $ret = '';
        switch($this->type) {
            case Event::TYPE_EVENT:
                $ret = 'Event';
                break;
            case Event::TYPE_FLAGSHIP:
                $ret = 'Flagship';
                break;
            case Event::TYPE_WORKSHOP:
                $ret = 'Workshop';
                break;
            case Event::TYPE_CELEBRITY_APPEARANCE:
                $ret = 'Celebrity Appearance';
                break;
            case Event::TYPE_GUEST_LECTURE:
                $ret = 'Guest Lecture';
                break;
        }

        return $ret;
    }
}