<?php
/**
 * PHP version 5.4 and 7
 *
 * @package   Payever\Plugins
 * @author    Hennadii.Shymanskyi <gendosua@gmail.com>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 */

namespace Payever\ExternalIntegration\Plugins\Http\MessageEntity;

use Payever\ExternalIntegration\Core\Base\MessageEntity;
use Payever\ExternalIntegration\Plugins\Enum\PluginCommandNameEnum;

/**
 * @method string getId()
 * @method string getName()
 * @method string getValue()
 */
class PluginCommandEntity extends MessageEntity
{
    /** @var string */
    protected $id;

    /**
     * @var string
     * @see PluginCommandNameEnum
     */
    protected $name;

    /** @var string */
    protected $value;
}