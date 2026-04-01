<?php
/**
 * Author: 风哀伤
 */
namespace Cje\Wechat\traits;

trait RequesterTrait
{
    /**
     * @var \Cje\Wechat\bases\Requester
     */
    protected $requester;

    public function getRequester()
    {
        if (!$this->requester) {
            $this->requester = new \Cje\Wechat\bases\Requester();
        }
        return $this->requester;
    }

    public function setRequester($requester)
    {
        $this->requester = $requester;
    }
}