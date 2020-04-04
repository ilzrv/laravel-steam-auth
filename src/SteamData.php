<?php

namespace Ilzrv\LaravelSteamAuth;

class SteamData
{
    /**
     * @var string|null
     */
    protected $steamId;

    /**
     * @var int|null
     */
    protected $communityVisibilityState;

    /**
     * @var int|null
     */
    protected $profileState;

    /**
     * @var string|null
     */
    protected $personaName;

    /**
     * @var int|null
     */
    protected $commentPermission;

    /**
     * @var string|null
     */
    protected $profileUrl;

    /**
     * @var string|null
     */
    protected $avatar;

    /**
     * @var string|null
     */
    protected $avatarMedium;

    /**
     * @var string|null
     */
    protected $avatarFull;

    /**
     * @var int|null
     */
    protected $lastLogoff;

    /**
     * @var int|null
     */
    protected $personaState;

    /**
     * @var string|null
     */
    protected $primaryClanId;

    /**
     * @var int|null
     */
    protected $timeCreated;

    /**
     * @var int|null
     */
    protected $personaStateFlags;

    /**
     * @var string|null
     */
    protected $locCountryCode;

    /**
     * @var int|null
     */
    protected $playerLevel;

    public function __construct(array $data)
    {
        $this->steamId = isset($data['steamid']) ? $data['steamid'] : null;
        $this->communityVisibilityState = isset($data['communityvisibilitystate']) ? $data['communityvisibilitystate'] : null;
        $this->profileState = isset($data['profilestate']) ? $data['profilestate'] : null;
        $this->personaName = isset($data['personaname']) ? $data['personaname'] : null;
        $this->commentPermission = isset($data['commentpermission']) ? $data['commentpermission'] : null;
        $this->profileUrl = isset($data['profileurl']) ? $data['profileurl'] : null;
        $this->avatar = isset($data['avatar']) ? $data['avatar'] : null;
        $this->avatarMedium = isset($data['avatarmedium']) ? $data['avatarmedium'] : null;
        $this->avatarFull = isset($data['avatarfull']) ? $data['avatarfull'] : null;
        $this->lastLogoff = isset($data['lastlogoff']) ? $data['lastlogoff'] : null;
        $this->personaState = isset($data['personastate']) ? $data['personastate'] : null;
        $this->primaryClanId = isset($data['primaryclanid']) ? $data['primaryclanid'] : null;
        $this->timeCreated = isset($data['timecreated']) ? $data['timecreated'] : null;
        $this->personaStateFlags = isset($data['personastateflags']) ? $data['personastateflags'] : null;
        $this->locCountryCode = isset($data['loccountrycode']) ? $data['loccountrycode'] : null;
        $this->playerLevel = isset($data['player_level']) ? $data['player_level'] : null;
    }

    /**
     * @return string|null
     */
    public function getSteamId()
    {
        return $this->steamId;
    }

    /**
     * @return int|null
     */
    public function getCommunityVisibilityState()
    {
        return $this->communityVisibilityState;
    }

    /**
     * @return int|null
     */
    public function getProfileState()
    {
        return $this->profileState;
    }

    /**
     * @return string|null
     */
    public function getPersonaName()
    {
        return $this->personaName;
    }

    /**
     * @return int|null
     */
    public function getCommentPermission()
    {
        return $this->commentPermission;
    }

    /**
     * @return string|null
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @return string|null
     */
    public function getAvatarMedium()
    {
        return $this->avatarMedium;
    }

    /**
     * @return string|null
     */
    public function getAvatarFull()
    {
        return $this->avatarFull;
    }

    /**
     * @return int|null
     */
    public function getLastLogoff()
    {
        return $this->lastLogoff;
    }

    /**
     * @return int|null
     */
    public function getPersonaState()
    {
        return $this->personaState;
    }

    /**
     * @return string|null
     */
    public function getPrimaryClanId()
    {
        return $this->primaryClanId;
    }

    /**
     * @return int|null
     */
    public function getTimeCreated()
    {
        return $this->timeCreated;
    }

    /**
     * @return int|null
     */
    public function getPersonaStateFlags()
    {
        return $this->personaStateFlags;
    }

    /**
     * @return string|null
     */
    public function getLocCountryCode()
    {
        return $this->locCountryCode;
    }

    /**
     * @return int|null
     */
    public function getPlayerLevel()
    {
        return $this->playerLevel;
    }
}
