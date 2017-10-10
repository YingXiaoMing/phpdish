<?php

namespace PHPDish\Bundle\NotificationBundle\Service;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPDish\Bundle\CoreBundle\Model\CommentInterface;
use PHPDish\Bundle\CoreBundle\Service\PaginatorTrait;
use PHPDish\Bundle\ForumBundle\Model\ReplyInterface;
use PHPDish\Bundle\ForumBundle\Model\TopicInterface;
use PHPDish\Bundle\NotificationBundle\Entity\Notification;
use PHPDish\Bundle\NotificationBundle\Model\NotificationInterface;
use PHPDish\Bundle\PostBundle\Model\CategoryInterface;
use PHPDish\Bundle\PostBundle\Model\PostInterface;
use PHPDish\Bundle\UserBundle\Model\UserInterface;

class NotificationManager implements NotificationManagerInterface
{
    use PaginatorTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $entityManager->getRepository('PHPDishNotificationBundle:Notification');
    }

    /**
     * {@inheritdoc}
     */
    public function createFollowUserNotification(UserInterface $user, UserInterface $follower)
    {
        $notification = $this->createNotification();
        $notification->setUser($user)->setFromUser($follower)->setSubject(Notification::SUBJECT_FOLLOW_USER);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createReplyTopicNotification(TopicInterface $topic, ReplyInterface $reply)
    {
        $notification = $this->createNotification();
        $notification->setUser($topic->getUser())
            ->setTopic($topic)
            ->setReply($reply)
            ->setMessage($reply->getBody())
            ->setFromUser($reply->getUser())
            ->setSubject(Notification::SUBJECT_REPLY_TOPIC);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createCommentPostNotification(PostInterface $post, CommentInterface $comment)
    {
        $notification = $this->createNotification();
        $notification->setUser($post->getUser())
            ->setPost($post)
            ->setComment($comment)
            ->setMessage($comment->getBody())
            ->setFromUser($comment->getUser())
            ->setSubject(Notification::SUBJECT_COMMENT_POST);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createAtUserInPostNotification(UserInterface $user, PostInterface $post, CommentInterface $comment)
    {
        $notification = $this->createNotification();
        $notification->setUser($user)
            ->setPost($post)
            ->setComment($comment)
            ->setMessage($comment->getBody())
            ->setFromUser($comment->getUser())
            ->setSubject(Notification::SUBJECT_AT_USER_IN_POST);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createAtUserInTopicNotification(UserInterface $user, TopicInterface $topic, ReplyInterface $reply)
    {
        $notification = $this->createNotification();
        $notification->setUser($user)
            ->setTopic($topic)
            ->setReply($reply)
            ->setMessage($reply->getBody())
            ->setFromUser($reply->getUser())
            ->setSubject(Notification::SUBJECT_AT_USER_IN_TOPIC);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createFollowCategoryNotification(CategoryInterface $category, UserInterface $user)
    {
        $notification = $this->createNotification();
        $notification->setUser($category->getCreator())
            ->setCategory($category)
            ->setFromUser($user)
            ->setSubject(Notification::SUBJECT_FOLLOW_CATEGORY);
        $this->saveNotification($notification);
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function createNotification()
    {
        $notification = new Notification();
        $notification->setCreatedAt(Carbon::now());
        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function saveNotification(NotificationInterface $notification)
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUnSeenNotificationCount(UserInterface $user)
    {
        $qb = $this->notificationRepository->createQueryBuilder('n');
        return $qb->select($qb->expr()->count('n'))
            ->where('n.user = :userId')->setParameter('userId', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserNotifications(UserInterface $user, $page, $limit = null)
    {
        $query = $this->notificationRepository->createQueryBuilder('n')
            ->where('n.user = :userId')->setParameter('userId', $user)
            ->getQuery();
        return $this->createPaginator($query, $page, $limit);
    }
}