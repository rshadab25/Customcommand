<?php 
/**
 * Copyright © shadab All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Shadab\Customcommand\Plugin\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement as MagentoAccountManagement;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AccountManagement
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var Data */
    public $helper;
    /**
     * AccountManagement constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Shadab\Customcommand\Helper\Data $helper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Shadab\Customcommand\Helper\Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->helper = $helper;
    }

    /**
     * @param MagentoAccountManagement $subject
     * @param string $username
     * @param string $password
     * @return array
     * @throws InvalidEmailOrPasswordException|LocalizedException
     */
    public function beforeAuthenticate(MagentoAccountManagement $subject, string $username, string $password): array
    {

        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($this->helper->checkIfadminUserExist($username)) {
            throw new LocalizedException(__('Please contact customer service.'));
        }

        return [$username, $password];
    }
}
