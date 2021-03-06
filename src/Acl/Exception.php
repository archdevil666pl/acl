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

namespace Vegas\Security\Acl;

use \Vegas\Security\Exception as SecurityException;

/**
 *
 * @package Vegas\Security\Acl
 */
class Exception extends SecurityException
{
    protected $message = "ACL error";
}
 