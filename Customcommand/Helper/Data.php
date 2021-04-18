<?php
/**
 * Copyright © shadab All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Shadab\Customcommand\Helper;
use Magento\Framework\Exception\NoSuchEntityException;

class Data
{
    /**
     * construct function
     *
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepo
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepo
    )
    {
        $this->userCollectionFactory = $userCollectionFactory;
        $this->customerRepo = $customerRepo;
    }
    /**
     * checkIfadminUserExist function
     *
     * @param string $email
     * @return boolean
     */
    public function checkIfadminUserExist($email){
        $admins = $this->getAdminUsers();
        if(isset($admins[$email]) && $admins[$email]!=''){
            return true;
        }
        return false;
    }
/**
 * checkIfCustomerExist function
 *
 * @param string $email
 * @return boolean
 */
    public function checkIfCustomerExist($email){
        try{
            $customerRepo = $this->customerRepo->get($email);  
            if($customerRepo->getId()){
                return true;
            }
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
    /**
     * getAdminUsers function
     *
     * @return array
     */
    public function getAdminUsers() {
        $adminUsers = [];
        foreach ($this->userCollectionFactory->create() as $user) {
            $adminUsers[$user->getEmail()] = $user->getId();
        }
        return $adminUsers;
    }
}
