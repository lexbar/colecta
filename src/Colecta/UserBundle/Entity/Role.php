<?php

namespace Colecta\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Colecta\UserBundle\Entity\Role
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Role
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name = 'ROLE_CUSTOM';

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var boolean $site_access
     *
     * @ORM\Column(name="site_access", type="boolean")
     */
    protected $site_access;
    // access granted to the site in general

    /**
     * @var boolean $item_post_create
     *
     * @ORM\Column(name="item_post_create", type="boolean")
     */
    protected $item_post_create;
    // creation of a text-based item

    /**
     * @var boolean $item_event_create
     *
     * @ORM\Column(name="item_event_create", type="boolean")
     */
    protected $item_event_create;
    // creation of a time-based item

    /**
     * @var boolean $item_route_create
     *
     * @ORM\Column(name="item_route_create", type="boolean")
     */
    protected $item_route_create;
    // creation of a track-based item

    /**
     * @var boolean $item_place_create
     *
     * @ORM\Column(name="item_place_create", type="boolean")
     */
    protected $item_place_create;
    // creation of a location-based item

    /**
     * @var boolean $item_file_create
     *
     * @ORM\Column(name="item_file_create", type="boolean")
     */
    protected $item_file_create;
    // creation of a file-based item

    /**
     * @var boolean $item_contest_create
     *
     * @ORM\Column(name="item_contest_create", type="boolean")
     */
    protected $item_contest_create;
    // creation of a competition-based item

    /**
     * @var boolean $item_poll_create
     *
     * @ORM\Column(name="item_poll_create", type="boolean")
     */
    protected $item_poll_create;
    // creation of a vote-based item
    
    /**
     * @var boolean $item_post_view
     *
     * @ORM\Column(name="item_post_view", type="boolean")
     */
    protected $item_post_view;
    // access to text-based items

    /**
     * @var boolean $item_event_view
     *
     * @ORM\Column(name="item_event_view", type="boolean")
     */
    protected $item_event_view;
    // access to time-based items

    /**
     * @var boolean $item_route_view
     *
     * @ORM\Column(name="item_route_view", type="boolean")
     */
    protected $item_route_view;
    // access to track-based items

    /**
     * @var boolean $item_place_view
     *
     * @ORM\Column(name="item_place_view", type="boolean")
     */
    protected $item_place_view;
    // access to location-based items

    /**
     * @var boolean $item_file_view
     *
     * @ORM\Column(name="item_file_view", type="boolean")
     */
    protected $item_file_view;
    // access to file-based items

    /**
     * @var boolean $item_contest_view
     *
     * @ORM\Column(name="item_contest_view", type="boolean")
     */
    protected $item_contest_view;
    // access to competition-based items

    /**
     * @var boolean $item_poll_view
     *
     * @ORM\Column(name="item_poll_view", type="boolean")
     */
    protected $item_poll_view;
    // access to vote-based items

    /**
     * @var boolean $item_post_edit
     *
     * @ORM\Column(name="item_post_edit", type="boolean")
     */
    protected $item_post_edit;
    // update my own text-based items

    /**
     * @var boolean $item_event_edit
     *
     * @ORM\Column(name="item_event_edit", type="boolean")
     */
    protected $item_event_edit;
    // update my own time-based items

    /**
     * @var boolean $item_route_edit
     *
     * @ORM\Column(name="item_route_edit", type="boolean")
     */
    protected $item_route_edit;
    // update my own track-based items

    /**
     * @var boolean $item_place_edit
     *
     * @ORM\Column(name="item_place_edit", type="boolean")
     */
    protected $item_place_edit;
    // update my own location-based items

    /**
     * @var boolean $item_file_edit
     *
     * @ORM\Column(name="item_file_edit", type="boolean")
     */
    protected $item_file_edit;
    // update my own file-based items

    /**
     * @var boolean $item_contest_edit
     *
     * @ORM\Column(name="item_contest_edit", type="boolean")
     */
    protected $item_contest_edit;
    // update my own competition-based items

    /**
     * @var boolean $item_poll_edit
     *
     * @ORM\Column(name="item_poll_edit", type="boolean")
     */
    protected $item_poll_edit;
    // update my own vote-based items

    /**
     * @var boolean $item_post_edit_any
     *
     * @ORM\Column(name="item_post_edit_any", type="boolean")
     */
    protected $item_post_edit_any;
    // update any text-based items

    /**
     * @var boolean $item_event_edit_any
     *
     * @ORM\Column(name="item_event_edit_any", type="boolean")
     */
    protected $item_event_edit_any;
    // update any time-based items

    /**
     * @var boolean $item_route_edit_any
     *
     * @ORM\Column(name="item_route_edit_any", type="boolean")
     */
    protected $item_route_edit_any;
    // update any track-based items

    /**
     * @var boolean $item_place_edit_any
     *
     * @ORM\Column(name="item_place_edit_any", type="boolean")
     */
    protected $item_place_edit_any;
    // update any location-based items

    /**
     * @var boolean $item_file_edit_any
     *
     * @ORM\Column(name="item_file_edit_any", type="boolean")
     */
    protected $item_file_edit_any;
    // update any file-based items

    /**
     * @var boolean $item_contest_edit_any
     *
     * @ORM\Column(name="item_contest_edit_any", type="boolean")
     */
    protected $item_contest_edit_any;
    // update any competition-based items

    /**
     * @var boolean $item_poll_edit_any
     *
     * @ORM\Column(name="item_poll_edit_any", type="boolean")
     */
    protected $item_poll_edit_any;
    // update any vote-based items

    /**
     * @var boolean $item_relate_own
     *
     * @ORM\Column(name="item_relate_own", type="boolean")
     */
    protected $item_relate_own;
    // relate my own items with others

    /**
     * @var boolean $item_relate_any
     *
     * @ORM\Column(name="item_relate_any", type="boolean")
     */
    protected $item_relate_any;
    // relate any item with any other

    /**
     * @var boolean $item_post_comment
     *
     * @ORM\Column(name="item_post_comment", type="boolean")
     */
    protected $item_post_comment;
    // submit comments on text-based items

    /**
     * @var boolean $item_event_comment
     *
     * @ORM\Column(name="item_event_comment", type="boolean")
     */
    protected $item_event_comment;
    // submit comments on time-based items

    /**
     * @var boolean $item_route_comment
     *
     * @ORM\Column(name="item_route_comment", type="boolean")
     */
    protected $item_route_comment;
    // submit comments on track-based items

    /**
     * @var boolean $item_place_comment
     *
     * @ORM\Column(name="item_place_comment", type="boolean")
     */
    protected $item_place_comment;
    // submit comments on location-based items

    /**
     * @var boolean $item_file_comment
     *
     * @ORM\Column(name="item_file_comment", type="boolean")
     */
    protected $item_file_comment;
    // submit comments on file-based items

    /**
     * @var boolean $item_contest_comment
     *
     * @ORM\Column(name="item_contest_comment", type="boolean")
     */
    protected $item_contest_comment;
    // submit comments on competition-based items

    /**
     * @var boolean $item_poll_comment
     *
     * @ORM\Column(name="item_poll_comment", type="boolean")
     */
    protected $item_poll_comment;
    // submit comments on vote-based items

    /**
     * @var boolean $item_poll_vote
     *
     * @ORM\Column(name="item_poll_vote", type="boolean")
     */
    protected $item_poll_vote;
    // submit vote on polls

    /**
     * @var boolean $category_create
     *
     * @ORM\Column(name="category_create", type="boolean")
     */
    protected $category_create;
    // create a new category and edit the ones I have created

    /**
     * @var boolean $category_edit
     *
     * @ORM\Column(name="category_edit", type="boolean")
     */
    protected $category_edit;
    // edit any category

    /**
     * @var boolean $activity_create
     *
     * @ORM\Column(name="activity_create", type="boolean")
     */
    protected $activity_create;
    // create a new activity and edit the ones I have created

    /**
     * @var boolean $activity_edit
     *
     * @ORM\Column(name="activity_edit", type="boolean")
     */
    protected $activity_edit;
    // edit any activity

    /**
     * @var boolean $user_create
     *
     * @ORM\Column(name="user_create", type="boolean")
     */
    protected $user_create;
    // create new users to the system

    /**
     * @var boolean $user_edit
     *
     * @ORM\Column(name="user_edit", type="boolean")
     */
    protected $user_edit;
    // edit any user of the system

    /**
     * @var boolean $user_view
     *
     * @ORM\Column(name="user_view", type="boolean")
     */
    protected $user_view;
    // access to users information

    /**
     * @var boolean $message_send
     *
     * @ORM\Column(name="message_send", type="boolean")
     */
    protected $message_send;
    // send and reply to private messages
    
    /**
     * @var boolean $site_config_settings
     *
     * @ORM\Column(name="site_config_settings", type="boolean")
     */
    protected $site_config_settings;
    // change site settings
    
    /**
     * @var boolean $site_config_users
     *
     * @ORM\Column(name="site_config_users", type="boolean")
     */
    protected $site_config_users;
    // access to users backend. user_create must be active to create new users. user_edit must be active to edit accounts
    
    /**
     * @var boolean $site_config_pages
     *
     * @ORM\Column(name="site_config_pages", type="boolean")
     */
    protected $site_config_pages;
    // create, edit and delete site static pages
    
    /**
     * @var boolean $site_config_lottery
     *
     * @ORM\Column(name="site_config_lottery", type="boolean")
     */
    protected $site_config_lottery;
    // manage lottery shreds
    
    /**
     * @var boolean $site_config_stats
     *
     * @ORM\Column(name="site_config_stats", type="boolean")
     */
    protected $site_config_stats;
    // access and control over page stats
    
    /**
     * @var boolean $site_config_plan
     *
     * @ORM\Column(name="site_config_plan", type="boolean")
     */
    protected $site_config_plan;
    // access and control over the plan purchased

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="role")
     */
    protected $users;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * Set site_access
     *
     * @param boolean $siteAccess
     */
    public function setSiteAccess($siteAccess)
    {
        $this->site_access = $siteAccess;
    }

    /**
     * Get site_access
     *
     * @return boolean 
     */
    public function getSiteAccess()
    {
        return $this->site_access;
    }

    /**
     * Set item_post_create
     *
     * @param boolean $itemPostCreate
     */
    public function setItemPostCreate($itemPostCreate)
    {
        $this->item_post_create = $itemPostCreate;
    }

    /**
     * Get item_post_create
     *
     * @return boolean 
     */
    public function getItemPostCreate()
    {
        return $this->item_post_create;
    }

    /**
     * Set item_event_create
     *
     * @param boolean $itemEventCreate
     */
    public function setItemEventCreate($itemEventCreate)
    {
        $this->item_event_create = $itemEventCreate;
    }

    /**
     * Get item_event_create
     *
     * @return boolean 
     */
    public function getItemEventCreate()
    {
        return $this->item_event_create;
    }

    /**
     * Set item_route_create
     *
     * @param boolean $itemRouteCreate
     */
    public function setItemRouteCreate($itemRouteCreate)
    {
        $this->item_route_create = $itemRouteCreate;
    }

    /**
     * Get item_route_create
     *
     * @return boolean 
     */
    public function getItemRouteCreate()
    {
        return $this->item_route_create;
    }

    /**
     * Set item_place_create
     *
     * @param boolean $itemPlaceCreate
     */
    public function setItemPlaceCreate($itemPlaceCreate)
    {
        $this->item_place_create = $itemPlaceCreate;
    }

    /**
     * Get item_place_create
     *
     * @return boolean 
     */
    public function getItemPlaceCreate()
    {
        return $this->item_place_create;
    }

    /**
     * Set item_file_create
     *
     * @param boolean $itemFileCreate
     */
    public function setItemFileCreate($itemFileCreate)
    {
        $this->item_file_create = $itemFileCreate;
    }

    /**
     * Get item_file_create
     *
     * @return boolean 
     */
    public function getItemFileCreate()
    {
        return $this->item_file_create;
    }

    /**
     * Set item_contest_create
     *
     * @param boolean $itemContestCreate
     */
    public function setItemContestCreate($itemContestCreate)
    {
        $this->item_contest_create = $itemContestCreate;
    }

    /**
     * Get item_contest_create
     *
     * @return boolean 
     */
    public function getItemContestCreate()
    {
        return $this->item_contest_create;
    }

    /**
     * Set item_poll_create
     *
     * @param boolean $itemPollCreate
     */
    public function setItemPollCreate($itemPollCreate)
    {
        $this->item_poll_create = $itemPollCreate;
    }

    /**
     * Get item_poll_create
     *
     * @return boolean 
     */
    public function getItemPollCreate()
    {
        return $this->item_poll_create;
    }

    /**
     * Set item_post_view
     *
     * @param boolean $itemPostView
     */
    public function setItemPostView($itemPostView)
    {
        $this->item_post_view = $itemPostView;
    }

    /**
     * Get item_post_view
     *
     * @return boolean 
     */
    public function getItemPostView()
    {
        return $this->item_post_view;
    }

    /**
     * Set item_event_view
     *
     * @param boolean $itemEventView
     */
    public function setItemEventView($itemEventView)
    {
        $this->item_event_view = $itemEventView;
    }

    /**
     * Get item_event_view
     *
     * @return boolean 
     */
    public function getItemEventView()
    {
        return $this->item_event_view;
    }

    /**
     * Set item_route_view
     *
     * @param boolean $itemRouteView
     */
    public function setItemRouteView($itemRouteView)
    {
        $this->item_route_view = $itemRouteView;
    }

    /**
     * Get item_route_view
     *
     * @return boolean 
     */
    public function getItemRouteView()
    {
        return $this->item_route_view;
    }

    /**
     * Set item_place_view
     *
     * @param boolean $itemPlaceView
     */
    public function setItemPlaceView($itemPlaceView)
    {
        $this->item_place_view = $itemPlaceView;
    }

    /**
     * Get item_place_view
     *
     * @return boolean 
     */
    public function getItemPlaceView()
    {
        return $this->item_place_view;
    }

    /**
     * Set item_file_view
     *
     * @param boolean $itemFileView
     */
    public function setItemFileView($itemFileView)
    {
        $this->item_file_view = $itemFileView;
    }

    /**
     * Get item_file_view
     *
     * @return boolean 
     */
    public function getItemFileView()
    {
        return $this->item_file_view;
    }

    /**
     * Set item_contest_view
     *
     * @param boolean $itemContestView
     */
    public function setItemContestView($itemContestView)
    {
        $this->item_contest_view = $itemContestView;
    }

    /**
     * Get item_contest_view
     *
     * @return boolean 
     */
    public function getItemContestView()
    {
        return $this->item_contest_view;
    }

    /**
     * Set item_poll_view
     *
     * @param boolean $itemPollView
     */
    public function setItemPollView($itemPollView)
    {
        $this->item_poll_view = $itemPollView;
    }

    /**
     * Get item_poll_view
     *
     * @return boolean 
     */
    public function getItemPollView()
    {
        return $this->item_poll_view;
    }

    /**
     * Set item_post_edit
     *
     * @param boolean $itemPostEdit
     */
    public function setItemPostEdit($itemPostEdit)
    {
        $this->item_post_edit = $itemPostEdit;
    }

    /**
     * Get item_post_edit
     *
     * @return boolean 
     */
    public function getItemPostEdit()
    {
        return $this->item_post_edit;
    }

    /**
     * Set item_event_edit
     *
     * @param boolean $itemEventEdit
     */
    public function setItemEventEdit($itemEventEdit)
    {
        $this->item_event_edit = $itemEventEdit;
    }

    /**
     * Get item_event_edit
     *
     * @return boolean 
     */
    public function getItemEventEdit()
    {
        return $this->item_event_edit;
    }

    /**
     * Set item_route_edit
     *
     * @param boolean $itemRouteEdit
     */
    public function setItemRouteEdit($itemRouteEdit)
    {
        $this->item_route_edit = $itemRouteEdit;
    }

    /**
     * Get item_route_edit
     *
     * @return boolean 
     */
    public function getItemRouteEdit()
    {
        return $this->item_route_edit;
    }

    /**
     * Set item_place_edit
     *
     * @param boolean $itemPlaceEdit
     */
    public function setItemPlaceEdit($itemPlaceEdit)
    {
        $this->item_place_edit = $itemPlaceEdit;
    }

    /**
     * Get item_place_edit
     *
     * @return boolean 
     */
    public function getItemPlaceEdit()
    {
        return $this->item_place_edit;
    }

    /**
     * Set item_file_edit
     *
     * @param boolean $itemFileEdit
     */
    public function setItemFileEdit($itemFileEdit)
    {
        $this->item_file_edit = $itemFileEdit;
    }

    /**
     * Get item_file_edit
     *
     * @return boolean 
     */
    public function getItemFileEdit()
    {
        return $this->item_file_edit;
    }

    /**
     * Set item_contest_edit
     *
     * @param boolean $itemContestEdit
     */
    public function setItemContestEdit($itemContestEdit)
    {
        $this->item_contest_edit = $itemContestEdit;
    }

    /**
     * Get item_contest_edit
     *
     * @return boolean 
     */
    public function getItemContestEdit()
    {
        return $this->item_contest_edit;
    }

    /**
     * Set item_poll_edit
     *
     * @param boolean $itemPollEdit
     */
    public function setItemPollEdit($itemPollEdit)
    {
        $this->item_poll_edit = $itemPollEdit;
    }

    /**
     * Get item_poll_edit
     *
     * @return boolean 
     */
    public function getItemPollEdit()
    {
        return $this->item_poll_edit;
    }

    /**
     * Set item_post_edit_any
     *
     * @param boolean $itemPostEditAny
     */
    public function setItemPostEditAny($itemPostEditAny)
    {
        $this->item_post_edit_any = $itemPostEditAny;
    }

    /**
     * Get item_post_edit_any
     *
     * @return boolean 
     */
    public function getItemPostEditAny()
    {
        return $this->item_post_edit_any;
    }

    /**
     * Set item_event_edit_any
     *
     * @param boolean $itemEventEditAny
     */
    public function setItemEventEditAny($itemEventEditAny)
    {
        $this->item_event_edit_any = $itemEventEditAny;
    }

    /**
     * Get item_event_edit_any
     *
     * @return boolean 
     */
    public function getItemEventEditAny()
    {
        return $this->item_event_edit_any;
    }

    /**
     * Set item_route_edit_any
     *
     * @param boolean $itemRouteEditAny
     */
    public function setItemRouteEditAny($itemRouteEditAny)
    {
        $this->item_route_edit_any = $itemRouteEditAny;
    }

    /**
     * Get item_route_edit_any
     *
     * @return boolean 
     */
    public function getItemRouteEditAny()
    {
        return $this->item_route_edit_any;
    }

    /**
     * Set item_place_edit_any
     *
     * @param boolean $itemPlaceEditAny
     */
    public function setItemPlaceEditAny($itemPlaceEditAny)
    {
        $this->item_place_edit_any = $itemPlaceEditAny;
    }

    /**
     * Get item_place_edit_any
     *
     * @return boolean 
     */
    public function getItemPlaceEditAny()
    {
        return $this->item_place_edit_any;
    }

    /**
     * Set item_file_edit_any
     *
     * @param boolean $itemFileEditAny
     */
    public function setItemFileEditAny($itemFileEditAny)
    {
        $this->item_file_edit_any = $itemFileEditAny;
    }

    /**
     * Get item_file_edit_any
     *
     * @return boolean 
     */
    public function getItemFileEditAny()
    {
        return $this->item_file_edit_any;
    }

    /**
     * Set item_contest_edit_any
     *
     * @param boolean $itemContestEditAny
     */
    public function setItemContestEditAny($itemContestEditAny)
    {
        $this->item_contest_edit_any = $itemContestEditAny;
    }

    /**
     * Get item_contest_edit_any
     *
     * @return boolean 
     */
    public function getItemContestEditAny()
    {
        return $this->item_contest_edit_any;
    }

    /**
     * Set item_poll_edit_any
     *
     * @param boolean $itemPollEditAny
     */
    public function setItemPollEditAny($itemPollEditAny)
    {
        $this->item_poll_edit_any = $itemPollEditAny;
    }

    /**
     * Get item_poll_edit_any
     *
     * @return boolean 
     */
    public function getItemPollEditAny()
    {
        return $this->item_poll_edit_any;
    }

    /**
     * Set item_relate_own
     *
     * @param boolean $itemRelateOwn
     */
    public function setItemRelateOwn($itemRelateOwn)
    {
        $this->item_relate_own = $itemRelateOwn;
    }

    /**
     * Get item_relate_own
     *
     * @return boolean 
     */
    public function getItemRelateOwn()
    {
        return $this->item_relate_own;
    }

    /**
     * Set item_relate_any
     *
     * @param boolean $itemRelateAny
     */
    public function setItemRelateAny($itemRelateAny)
    {
        $this->item_relate_any = $itemRelateAny;
    }

    /**
     * Get item_relate_any
     *
     * @return boolean 
     */
    public function getItemRelateAny()
    {
        return $this->item_relate_any;
    }

    /**
     * Set item_post_comment
     *
     * @param boolean $itemPostComment
     */
    public function setItemPostComment($itemPostComment)
    {
        $this->item_post_comment = $itemPostComment;
    }

    /**
     * Get item_post_comment
     *
     * @return boolean 
     */
    public function getItemPostComment()
    {
        return $this->item_post_comment;
    }

    /**
     * Set item_event_comment
     *
     * @param boolean $itemEventComment
     */
    public function setItemEventComment($itemEventComment)
    {
        $this->item_event_comment = $itemEventComment;
    }

    /**
     * Get item_event_comment
     *
     * @return boolean 
     */
    public function getItemEventComment()
    {
        return $this->item_event_comment;
    }

    /**
     * Set item_route_comment
     *
     * @param boolean $itemRouteComment
     */
    public function setItemRouteComment($itemRouteComment)
    {
        $this->item_route_comment = $itemRouteComment;
    }

    /**
     * Get item_route_comment
     *
     * @return boolean 
     */
    public function getItemRouteComment()
    {
        return $this->item_route_comment;
    }

    /**
     * Set item_place_comment
     *
     * @param boolean $itemPlaceComment
     */
    public function setItemPlaceComment($itemPlaceComment)
    {
        $this->item_place_comment = $itemPlaceComment;
    }

    /**
     * Get item_place_comment
     *
     * @return boolean 
     */
    public function getItemPlaceComment()
    {
        return $this->item_place_comment;
    }

    /**
     * Set item_file_comment
     *
     * @param boolean $itemFileComment
     */
    public function setItemFileComment($itemFileComment)
    {
        $this->item_file_comment = $itemFileComment;
    }

    /**
     * Get item_file_comment
     *
     * @return boolean 
     */
    public function getItemFileComment()
    {
        return $this->item_file_comment;
    }

    /**
     * Set item_contest_comment
     *
     * @param boolean $itemContestComment
     */
    public function setItemContestComment($itemContestComment)
    {
        $this->item_contest_comment = $itemContestComment;
    }

    /**
     * Get item_contest_comment
     *
     * @return boolean 
     */
    public function getItemContestComment()
    {
        return $this->item_contest_comment;
    }

    /**
     * Set item_poll_comment
     *
     * @param boolean $itemPollComment
     */
    public function setItemPollComment($itemPollComment)
    {
        $this->item_poll_comment = $itemPollComment;
    }

    /**
     * Get item_poll_comment
     *
     * @return boolean 
     */
    public function getItemPollComment()
    {
        return $this->item_poll_comment;
    }

    /**
     * Set item_poll_vote
     *
     * @param boolean $itemPollVote
     */
    public function setItemPollVote($itemPollVote)
    {
        $this->item_poll_vote = $itemPollVote;
    }

    /**
     * Get item_poll_vote
     *
     * @return boolean 
     */
    public function getItemPollVote()
    {
        return $this->item_poll_vote;
    }

    /**
     * Set category_create
     *
     * @param boolean $categoryCreate
     */
    public function setCategoryCreate($categoryCreate)
    {
        $this->category_create = $categoryCreate;
    }

    /**
     * Get category_create
     *
     * @return boolean 
     */
    public function getCategoryCreate()
    {
        return $this->category_create;
    }

    /**
     * Set category_edit
     *
     * @param boolean $categoryEdit
     */
    public function setCategoryEdit($categoryEdit)
    {
        $this->category_edit = $categoryEdit;
    }

    /**
     * Get category_edit
     *
     * @return boolean 
     */
    public function getCategoryEdit()
    {
        return $this->category_edit;
    }

    /**
     * Set activity_create
     *
     * @param boolean $activityCreate
     */
    public function setActivityCreate($activityCreate)
    {
        $this->activity_create = $activityCreate;
    }

    /**
     * Get activity_create
     *
     * @return boolean 
     */
    public function getActivityCreate()
    {
        return $this->activity_create;
    }

    /**
     * Set activity_edit
     *
     * @param boolean $activityEdit
     */
    public function setActivityEdit($activityEdit)
    {
        $this->activity_edit = $activityEdit;
    }

    /**
     * Get activity_edit
     *
     * @return boolean 
     */
    public function getActivityEdit()
    {
        return $this->activity_edit;
    }

    /**
     * Set user_create
     *
     * @param boolean $userCreate
     */
    public function setUserCreate($userCreate)
    {
        $this->user_create = $userCreate;
    }

    /**
     * Get user_create
     *
     * @return boolean 
     */
    public function getUserCreate()
    {
        return $this->user_create;
    }

    /**
     * Set user_edit
     *
     * @param boolean $userEdit
     */
    public function setUserEdit($userEdit)
    {
        $this->user_edit = $userEdit;
    }

    /**
     * Get user_edit
     *
     * @return boolean 
     */
    public function getUserEdit()
    {
        return $this->user_edit;
    }

    /**
     * Set user_view
     *
     * @param boolean $userView
     */
    public function setUserView($userView)
    {
        $this->user_view = $userView;
    }

    /**
     * Get user_view
     *
     * @return boolean 
     */
    public function getUserView()
    {
        return $this->user_view;
    }

    /**
     * Set message_send
     *
     * @param boolean $messageSend
     */
    public function setMessageSend($messageSend)
    {
        $this->message_send = $messageSend;
    }

    /**
     * Get message_send
     *
     * @return boolean 
     */
    public function getMessageSend()
    {
        return $this->message_send;
    }

    /**
     * Set users
     *
     * @param string $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * Get users
     *
     * @return string 
     */
    public function getUsers()
    {
        return $this->users;
    }
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param Colecta\UserBundle\Entity\User $users
     */
    public function addUser(\Colecta\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;
    }

    /**
     * Remove users
     *
     * @param \Colecta\UserBundle\Entity\User $users
     */
    public function removeUser(\Colecta\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Set site_config_settings
     *
     * @param boolean $siteConfigSettings
     * @return Role
     */
    public function setSiteConfigSettings($siteConfigSettings)
    {
        $this->site_config_settings = $siteConfigSettings;
    
        return $this;
    }

    /**
     * Get site_config_settings
     *
     * @return boolean 
     */
    public function getSiteConfigSettings()
    {
        return $this->site_config_settings;
    }

    /**
     * Set site_config_users
     *
     * @param boolean $siteConfigUsers
     * @return Role
     */
    public function setSiteConfigUsers($siteConfigUsers)
    {
        $this->site_config_users = $siteConfigUsers;
    
        return $this;
    }

    /**
     * Get site_config_users
     *
     * @return boolean 
     */
    public function getSiteConfigUsers()
    {
        return $this->site_config_users;
    }

    /**
     * Set site_config_pages
     *
     * @param boolean $siteConfigPages
     * @return Role
     */
    public function setSiteConfigPages($siteConfigPages)
    {
        $this->site_config_pages = $siteConfigPages;
    
        return $this;
    }

    /**
     * Get site_config_pages
     *
     * @return boolean 
     */
    public function getSiteConfigPages()
    {
        return $this->site_config_pages;
    }

    /**
     * Set site_config_lottery
     *
     * @param boolean $siteConfigLottery
     * @return Role
     */
    public function setSiteConfigLottery($siteConfigLottery)
    {
        $this->site_config_lottery = $siteConfigLottery;
    
        return $this;
    }

    /**
     * Get site_config_lottery
     *
     * @return boolean 
     */
    public function getSiteConfigLottery()
    {
        return $this->site_config_lottery;
    }

    /**
     * Set site_config_stats
     *
     * @param boolean $siteConfigStats
     * @return Role
     */
    public function setSiteConfigStats($siteConfigStats)
    {
        $this->site_config_stats = $siteConfigStats;
    
        return $this;
    }

    /**
     * Get site_config_stats
     *
     * @return boolean 
     */
    public function getSiteConfigStats()
    {
        return $this->site_config_stats;
    }

    /**
     * Set site_config_plan
     *
     * @param boolean $siteConfigPlan
     * @return Role
     */
    public function setSiteConfigPlan($siteConfigPlan)
    {
        $this->site_config_plan = $siteConfigPlan;
    
        return $this;
    }

    /**
     * Get site_config_plan
     *
     * @return boolean 
     */
    public function getSiteConfigPlan()
    {
        return $this->site_config_plan;
    }
    
    /**
     * Get site_config
     *
     * @return boolean 
     */
    public function getSiteConfig()
    {
        return $this->site_config_settings || $this->site_config_users || $this->site_config_pages || $this->site_config_lottery || $this->site_config_stats || $this->site_config_plan; 
    }
    
    /**
     * Get contribute
     *
     * @return boolean 
     */
    public function getContribute()
    {
        return $this->item_post_create || $this->item_event_create || $this->item_route_create || $this->item_place_create || $this->item_file_create; 
    }
}