<?php
namespace jones\wschat\components;

use Yii;

/**
 * Class ChatManager
 * @package \jones\wschat\components
 */
class ChatManager
{
    /** @var \jones\wschat\components\User[] */
    private $users = [];
    /** @var string a namespace of class to get user instance */
    public $userClassName = null;

    /**
     * Check if user exists in list
     * return resource id if user in current list - else null
     *
     * @access private
     * @param $id
     * @return null|int
     */
    public function isUserExists($id)
    {
        foreach ($this->users as $rid => $user) {
            if ($user->id == $id) {
                return $rid;
            }
        }
        return null;
    }

    /**
     * Add new user to manager
     *
     * @access public
     * @param $rid
     * @param $id
     * @return void
     */
    public function addUser($rid, $id)
    {
        $user = new User($id, $this->userClassName);
        $user->setRid($rid);
        $this->users[$rid] = $user;
    }

    /**
     * Return if exists user chat room
     *
     * @access public
     * @param $rid
     * @return \jones\wschat\components\ChatRoom|null
     */
    public function getUserChat($rid)
    {
        $user = $this->getUserByRid($rid);
        return $user ? $user->getChat() : null;
    }

    /**
     * Find chat room by id, if not exists create new chat room
     * and assign to user by resource id
     *
     * @access public
     * @param $chatId
     * @param $rid
     * @return \jones\wschat\components\ChatRoom|null
     */
    public function findChat($chatId, $rid)
    {
        $chat = null;
        $storedUser = $this->getUserByRid($rid);
        foreach ($this->users as $user) {
            $userChat = $user->getChat();
            if (!$userChat) {
                continue;
            }
            if ($userChat->getUid() == $chatId) {
                $chat = $userChat;
                Yii::info('User('.$user->id.') will be joined to: '.$chatId, 'chat');
                break;
            }
        }
        if (!$chat) {
            Yii::info('New chat room: '.$chatId.' for user: '.$storedUser->id, 'chat');
            $chat = new ChatRoom();
            $chat->setUid($chatId);
        }
        $storedUser->setChat($chat);
        return $chat;
    }

    /**
     * Get user by resource id
     *
     * @access public
     * @param $rid
     * @return User
     */
    public function getUserByRid($rid)
    {
        return !empty($this->users[$rid]) ? $this->users[$rid] : null;
    }

    /**
     * Find user by resource id and remove it from chat
     *
     * @access public
     * @param $rid
     * @return void
     */
    public function removeUserFromChat($rid)
    {
        $user = $this->getUserByRid($rid);
        if (!$user) {
            return;
        }
        $chat = $user->getChat();
        if ($chat) {
            $chat->removeUser($user);
        }
        unset($this->users[$rid]);
    }
}
 