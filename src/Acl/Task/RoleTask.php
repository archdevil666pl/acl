<?php
/**
 * This file is part of Vegas package
 *
 * @author Slawomir Zytko <slawomir.zytko@gmail.com>
 * @copyright Amsterdam Standard Sp. Z o.o.
 * @homepage http://vegas-cmf.github.io
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vegas\Security\Acl\Task;

use Vegas\Security\Acl\Adapter\Exception\ResourceNotExistsException,
    Vegas\Security\Acl\Builder,
    Vegas\Security\Acl\Resource,
    Vegas\Cli\Task\Action,
    Vegas\Cli\Task\Option;
use \Vegas\Security\Acl\Role as UserRole;


class RoleTask extends \Vegas\Cli\TaskAbstract
{

    /**
     * Default list of predefined ACL resources
     *
     * @var array
     */
    private $predefinedResources  = [
        'all'   =>  [
            'description'   =>  'All privileges (for super admin)',
            'accessList'    =>  [
                [
                    'name'  =>  Resource::ACCESS_WILDCARD,
                    'description' => 'All',
                    'inherit' => ''
                ]
            ]
        ]
    ];

    /**
     * Shorthand method to retrieve valid ACL instance from DI container.
     *
     * @return \Vegas\Security\Acl
     */
    private function getAcl()
    {
        return $this->getDI()->get('acl');
    }

    /**
     * Task must implement this method to set available options
     *
     * @return mixed
     */
    public function setupOptions()
    {
        $setupAction = new \Vegas\Cli\Task\Action('setup', 'Setup ACL basic roles');
        $this->addTaskAction($setupAction);

        // add action
        $addAction = new Action('add', 'Add a new role');
        $option = new Option('name', 'n', 'Name of role');
        $option->setRequired(true);
        $addAction->addOption($option);
        $option = new Option('description', 'd', 'Description of role');
        $addAction->addOption($option);

        $this->addTaskAction($addAction);

        // remove action
        $removeAction = new Action('remove', 'Remove a role');
        $option = new Option('name', 'n', 'Name of role to remove');
        $option->setRequired(true);
        $removeAction->addOption($option);

        $this->addTaskAction($removeAction);

        // allow action
        $allowAction = new Action('allow', 'Allow resource for role');
        $option = new Option('name', 'n', 'Name of role');
        $option->setRequired(true);
        $allowAction->addOption($option);

        $option = new Option('resource', 'r', 'Resource to allow');
        $option->setRequired(true);
        $allowAction->addOption($option);

        $option = new Option('access', 'a', 'Access in resource to allow');
        $allowAction->addOption($option);

        $this->addTaskAction($allowAction);

        // deny action
        $denyAction = new Action('deny', 'Deny resource for role');
        $option = new Option('name', 'n', 'Name of role');
        $option->setRequired(true);
        $denyAction->addOption($option);

        $option = new Option('resource', 'r', 'Resource to deny');
        $option->setRequired(true);
        $denyAction->addOption($option);

        $option = new Option('access', 'a', 'Access in resource to deny');
        $denyAction->addOption($option);

        $this->addTaskAction($denyAction);

        // build action
        $buildAction = new \Vegas\Cli\Task\Action('build', 'Build list of resources');
        $removeAction->addOption($option);

        $this->addTaskAction($buildAction);
    }

    /**
     * Sets up default builtin roles:
     * - creates guest role for non-authenticated users
     * - creates super-admin role with all privileges
     * Usage:
     *   vegas:security_acl:role setup
     *
     */
    public function setupAction()
    {
        $acl = $this->getAcl();
        $roleManager = $acl->getRoleManager();

        $roleManager->add(UserRole::DEFAULT_ROLE_GUEST, 'Not authenticated user', true);
        $acl->getResourceManager()->add(
            Resource::WILDCARD,
            ucfirst(Resource::WILDCARD),
            Resource::ACCESS_WILDCARD
        );
                
        $roleManager->add(UserRole::SUPER_ADMIN, 'Super administrator with all privileges', true);
        $acl->allow(UserRole::SUPER_ADMIN, Resource::WILDCARD, Resource::ACCESS_WILDCARD);

        $this->putText('Success.');
    }

    /**
     * Creates new role.
     * Usage:
     *   vegas:security_acl:role add [options]
     * Options:
     *   --name           -n      Name of role
     *   --description    -d      Description of role (optional)
     *
     * Example:
     * <code>
     *  vegas:security_acl:role add -n Manager -d "Manages regular users"
     * </code>
     */
    public function addAction()
    {
        $name = $this->getOption('n');
        $description = $this->getOption('d');
        
        //creates role
        $this->getAcl()->getRoleManager()->add($name, $description);

        $this->putText('Success.');
    }

    /**
     * Removes existing role.
     * Usage:
     *   vegas:security_acl:role remove [options]
     * Options:
     *   --name           -n      Name of role
     *
     * Example:
     * <code>
     *  vegas:security_acl:role remove -n Manager
     * </code>
     */
    public function removeAction()
    {
        $name = $this->getOption('n');

        //removes role
        $this->getAcl()->getRoleManager()->dropRole($name);

        $this->putText('Success.');
    }

    /**
     * Grants specified access (or all accesses) to a resource for specified role.
     * Both role and resource must exist before running this command.
     *
     * Usage:
     *   vegas:security_acl:role allow [options]
     * Options:
     *   --name           -n      Name of role
     *   --resource       -r      Resource to allow
     *   --access         -a      Access in resource to allow (optional)
     *
     * Example:
     * <code>
     *  vegas:security_acl:role allow -n Editor -r mvc:wiki:Frontend-Handbook -a index
     * </code>
     * @throws \Vegas\Security\Acl\Adapter\Exception\ResourceNotExistsException
     */
    public function allowAction()
    {
        $roleName = $this->getOption('n');
        $resourceName = $this->getOption('r');

        $acl = $this->getAcl();

        $role = $acl->getRole($roleName);
        $resource = $acl->getResource($resourceName);

        $access = $this->getOption('a');
        if ($access && !$resource->hasAccess($access)) {
            throw new ResourceNotExistsException($access);
        }
        $accessList = $access ? [$access] : array_keys($resource->getAccesses());

        foreach ($accessList as $access) {
            $acl->allow($role->getName(), $resource->getName(), $access);
        }

        $this->putText('Success.');
    }

    /**
     * Removes specified access (or all accesses) to a resource for specified role.
     * Both role and resource must exist before running this command.
     *
     * Usage:
     *   vegas:security_acl:role deny [options]
     * Options:
     *   --name           -n      Name of role
     *   --resource       -r      Resource to deny
     *   --access         -a      Access in resource to deny (optional)
     *
     * Example:
     * <code>
     *  vegas:security_acl:role deny -n Editor -r mvc:wiki:Frontend-Handbook -a delete
     * </code>
     * @throws \Vegas\Security\Acl\Adapter\Exception\ResourceNotExistsException
     */
    public function denyAction()
    {
        $roleName = $this->getOption('n');
        $resourceName = $this->getOption('r');

        $acl = $this->getAcl();

        $role = $acl->getRole($roleName);
        $resource = $acl->getResource($resourceName);

        $access = $this->getOption('a');
        if ($access && !$resource->hasAccess($access)) {
            throw new ResourceNotExistsException($access);
        }
        $accessList = $access ? [$access] : array_keys($resource->getAccesses());

        foreach ($accessList as $access) {
            $acl->deny($role->getName(), $resource->getName(), $access);
        }

        $this->putText('Success.');
    }

    /**
     * Clears stored list of resources with their accesses and recreates it based on ACL annotations in controllers.
     *
     * Usage:
     *   vegas:security_acl:role build
     */
    public function buildAction()
    {
        //clears collection before build
        $acl = $this->getAcl();
        $acl->removeResources();
        $acl->removeResourceAccesses();

        $config = $this->getDI()->get('config');
        $predefinedResources = isset($config['acl']) ? $config['acl']->toArray() : $this->predefinedResources;
        $resourceBuilder = new Builder($this->getDI()->get('modules'), $predefinedResources);
        $aclResources = $resourceBuilder->build();

        $resourceManager = $this->getAcl()->getResourceManager();
        foreach ($aclResources as $aclResource) {
            if (empty($aclResource)) continue;
            $resourceManager->add(
                $aclResource['name'],
                $aclResource['description'],
                $aclResource['accessList'],
                isset($aclResource['scope']) ? $aclResource['scope'] : ''
            );
        }

        $this->putText('Success.');
    }
}
 