<?php
/**
 * Copyright © shadab All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Shadab\Customcommand\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Setup\Model\AdminAccount;
use Magento\User\Model\UserValidationRules;
use Magento\Framework\Encryption\EncryptorInterface;

class Admincreate extends Command
{

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var UserValidationRules
     */
    private $validationRules;
    /**
     * @var ResourceConnection
     */
    protected $resource;

   /**
    *
    * @param UserValidationRules $validationRules
    * @param EncryptorInterface $encryptor
    * @param \Magento\Framework\App\ResourceConnection $resource
    */
    public function __construct(
        UserValidationRules $validationRules,
        EncryptorInterface $encryptor,
        \Magento\Framework\App\ResourceConnection $resource
      )
    {
        $this->_resource = $resource;
        $this->connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $this->validationRules = $validationRules;
        $this->encryptor = $encryptor;
        parent::__construct();
    }
    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
       
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln('<error>' . implode('</error>' . PHP_EOL . '<error>', $errors) . '</error>');
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        
        $this->data = $input->getOptions();
       
        $passwordHash = $this->generatePassword();
        $adminData = [
            'firstname' => $this->data[AdminAccount::KEY_FIRST_NAME],
            'lastname'  => $this->data[AdminAccount::KEY_LAST_NAME],
            'password'  => $passwordHash,
            'is_active' => 1,
        ];
     
        $result = $this->connection->fetchRow(
            "SELECT user_id, username, email FROM admin_user "
            . "WHERE username = :username OR email = :email",
            ['username' => $this->data[AdminAccount::KEY_USER], 'email' => $this->data[AdminAccount::KEY_EMAIL]]
        );
        if (!empty($result)) {
            $output->writeln("Allready user is existed");
            return;
        } else {
            try{
            // User does not exist, create it
            $adminData['username'] = $this->data[AdminAccount::KEY_USER];
            $adminData['email'] = $this->data[AdminAccount::KEY_EMAIL];
            $this->connection->insert(
                'admin_user',
                $adminData
            );
            $adminId = $this->connection->lastInsertId();
            }catch(\Exception $e){
                $output->writeln($e->getMessage());
            }
        }
        $this->trackPassword($adminId, $passwordHash);
        $adminRoleData = [
            'parent_id'  => 1,
            'tree_level' => 2,
            'role_type'  => 'U',
            'user_id'    => $adminId,
            'user_type'  => 2,
            'role_name'  => $this->data[AdminAccount::KEY_USER],
        ];
        $this->connection->insert('authorization_role', $adminRoleData);
        $output->writeln($this->data[AdminAccount::KEY_USER]." user successfully created!!!!");
    }
    /**
     * Generate Password function
     *
     * @return string
     */
    public function generatePassword(){
        return $this->encryptor->getHash($this->data[AdminAccount::KEY_PASSWORD], true);
    }
     /**
     * Remember a password hash for further usage.
     *
     * @param int $adminId
     * @param string $passwordHash
     * @return void
     */
    private function trackPassword($adminId, $passwordHash)
    {
        $this->connection->insert(
            'admin_passwords',
            [
                'user_id' => $adminId,
                'password_hash' => $passwordHash,
                'last_updated' => time()
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("customcommand:admincreate");
        $this->setDescription("custom admin creation");
        $this->setDefinition($this->getOptionsList());
        parent::configure();
    }
    /**
     * Get list of arguments for the command
     *
     * @param int $mode The mode of options.
     * @return InputOption[]
     */
    public function getOptionsList($mode = InputOption::VALUE_REQUIRED)
    {
        $requiredStr = ($mode === InputOption::VALUE_REQUIRED ? '(Required) ' : '');

        return [
            new InputOption(
                AdminAccount::KEY_USER,
                null,
                $mode,
                $requiredStr . 'Admin user'
            ),
            new InputOption(
                AdminAccount::KEY_PASSWORD,
                null,
                $mode,
                $requiredStr . 'Admin password'
            ),
            new InputOption(
                AdminAccount::KEY_EMAIL,
                null,
                $mode,
                $requiredStr . 'Admin email'
            ),
            new InputOption(
                AdminAccount::KEY_FIRST_NAME,
                null,
                $mode,
                $requiredStr . 'Admin first name'
            ),
            new InputOption(
                AdminAccount::KEY_LAST_NAME,
                null,
                $mode,
                $requiredStr . 'Admin last name'
            ),
        ];
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $user = new \Magento\Framework\DataObject();
        $user->setFirstname($input->getOption(AdminAccount::KEY_FIRST_NAME))
            ->setLastname($input->getOption(AdminAccount::KEY_LAST_NAME))
            ->setUsername($input->getOption(AdminAccount::KEY_USER))
            ->setEmail($input->getOption(AdminAccount::KEY_EMAIL))
            ->setPassword(
                $input->getOption(AdminAccount::KEY_PASSWORD) === null
                ? '' : $input->getOption(AdminAccount::KEY_PASSWORD)
            );
        $validator = new \Magento\Framework\Validator\DataObject();
        $this->validationRules->addUserInfoRules($validator);
        $this->validationRules->addPasswordRules($validator);

        if (!$validator->isValid($user)) {
            $errors = array_merge($errors, $validator->getMessages());
        }

        return $errors;
    }
}

