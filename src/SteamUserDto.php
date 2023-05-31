<?php

declare(strict_types=1);

namespace Ilzrv\LaravelSteamAuth;

final class SteamUserDto
{
    private function __construct(
        private readonly string $steamId,
        private readonly ?int $communityVisibilityState,
        private readonly ?int $profileState,
        private readonly ?string $personaName,
        private readonly ?int $commentPermission,
        private readonly ?string $profileUrl,
        private readonly ?string $avatar,
        private readonly ?string $avatarMedium,
        private readonly ?string $avatarFull,
        private readonly ?string $avatarHash,
        private readonly ?int $lastLogoff,
        private readonly ?int $personaState,
        private readonly ?string $primaryClanId,
        private readonly ?int $timeCreated,
        private readonly ?int $personaStateFlags,
        private readonly ?string $locCountryCode,
        private readonly ?int $playerLevel,
    ) {
    }

    public static function create(array $data): self
    {
        return new self(
            steamId: $data['steamid'],
            communityVisibilityState: $data['communityvisibilitystate'] ?? null,
            profileState: $data['profilestate'] ?? null,
            personaName: $data['personaname'] ?? null,
            commentPermission: $data['commentpermission'] ?? null,
            profileUrl: $data['profileurl'] ?? null,
            avatar: $data['avatar'] ?? null,
            avatarMedium: $data['avatarmedium ?? null'],
            avatarFull: $data['avatarfull'] ?? null,
            avatarHash: $data['avatarhash'] ?? null,
            lastLogoff: $data['lastlogoff'] ?? null,
            personaState: $data['personastate'] ?? null,
            primaryClanId: $data['primaryclanid'] ?? null,
            timeCreated: $data['timecreated'] ?? null,
            personaStateFlags: $data['personastateflags'] ?? null,
            locCountryCode: $data['loccountrycode'] ?? null,
            playerLevel: $data['player_level'] ?? null,
        );
    }

    public function getSteamId(): string
    {
        return $this->steamId;
    }

    public function getCommunityVisibilityState(): ?int
    {
        return $this->communityVisibilityState;
    }

    public function getProfileState(): ?int
    {
        return $this->profileState;
    }

    public function getPersonaName(): ?string
    {
        return $this->personaName;
    }

    public function getCommentPermission(): ?int
    {
        return $this->commentPermission;
    }

    public function getProfileUrl(): ?string
    {
        return $this->profileUrl;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getAvatarMedium(): ?string
    {
        return $this->avatarMedium;
    }

    public function getAvatarFull(): ?string
    {
        return $this->avatarFull;
    }

    public function getAvatarHash(): ?string
    {
        return $this->avatarHash;
    }

    public function getLastLogoff(): ?int
    {
        return $this->lastLogoff;
    }

    public function getPersonaState(): ?int
    {
        return $this->personaState;
    }

    public function getPrimaryClanId(): ?string
    {
        return $this->primaryClanId;
    }

    public function getTimeCreated(): ?int
    {
        return $this->timeCreated;
    }

    public function getPersonaStateFlags(): ?int
    {
        return $this->personaStateFlags;
    }

    public function getLocCountryCode(): ?string
    {
        return $this->locCountryCode;
    }

    public function getPlayerLevel(): ?int
    {
        return $this->playerLevel;
    }
}
