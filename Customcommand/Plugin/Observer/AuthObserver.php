<?php
/**
 * Copyright © shadab All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Shadab\Customcommand\Plugin\Observer;
use Magento\Framework\Exception\State\UserLockedException;

class AuthObserver
{

    /**
     * construct function
     *
     * @param \Shadab\Customcommand\Helper\Data $helper
     */
    public function __construct(
        \Shadab\Customcommand\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * beforeExecute function
     *
     * @param \Magento\User\Observer\Backend\AuthObserver $subject
     * @param  $observer
     * @return array|UserLockedException
     */
    public function beforeExecute(
        \Magento\User\Observer\Backend\AuthObserver $subject,
        $observer
    ) {
         /** @var User $user */
         $user = $observer->getEvent()->getUser();
         $email = $user->getEmail();
         if($this->helper->checkIfCustomerExist($email)){
            throw new UserLockedException(
                __(
                    'Please contact customer service.'
                )
            );
            return $subject;
         }
        return [$observer];
    }
}
