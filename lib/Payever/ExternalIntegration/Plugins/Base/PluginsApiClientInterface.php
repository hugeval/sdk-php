<?php
/**
 * PHP version 5.4 and 7
 *
 * @package   Payever\Plugins
 * @author    Hennadii.Shymanskyi <gendosua@gmail.com>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 */

namespace Payever\ExternalIntegration\Plugins\Base;

use Payever\ExternalIntegration\Core\Http\Response;

interface PluginsApiClientInterface
{
    /**
     * @return PluginRegistryInfoProviderInterface
     */
    public function getRegistryInfoProvider();

    /**
     * @return Response
     */
    public function registerPlugin();

    /**
     * @return Response
     */
    public function unregisterPlugin();

    /**
     * @param string $commandId
     * @return Response
     */
    public function acknowledgePluginCommand($commandId);

    /**
     * @param int|null $fromTimestamp
     * @return Response
     */
    public function getCommands($fromTimestamp = null);
}