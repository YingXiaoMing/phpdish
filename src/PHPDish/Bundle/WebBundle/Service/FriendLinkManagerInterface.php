<?php

declare(strict_types=1);

namespace PHPDish\Bundle\WebBundle\Service;

use PHPDish\Bundle\WebBundle\Entity\FriendLink;

interface FriendLinkManagerInterface
{
    /**
     * 获取所有启用的友情链接
     *
     * @param int $limit
     * @return FriendLink[]
     */
    public function findEnabledFriendLinks($limit);
}